<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\DestroyCustomer;
use App\Http\Requests\Customer\StoreCustomer;
use App\Http\Requests\Customer\UpdateCustomer;
use App\Mail\ForgetPassword;
use App\Mail\Register;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:users', ['except' => ['login', 'store', 'forgotPassword', 'refresh']]);
        $this->middleware('assign.guard:users');
        $this->middleware('role:admin', ['only' => ['index', 'destroy']]);

        Log::debug('UserController constructed');
    }

    /**
     * @OA\Get(
     *      path="/users",
     *      operationId="getUsers",
     *      tags={"User"},
     *      summary="Retrieve all users",
     *      description="Retrieve all users",
     *      @OA\Response(
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(
     *               title="PaginatedUserResponse",
     *               @OA\Property(property="current_page", type="integer", example=1),
     *               @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/UserResponse")
     *               ),
     *               @OA\Property(property="from", type="integer", example=1),
     *               @OA\Property(property="last_page", type="integer", example=1),
     *               @OA\Property(property="per_page", type="integer", example=1),
     *               @OA\Property(property="to", type="integer", example=1),
     *               @OA\Property(property="total", type="integer", example=1),
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        Log::info('Index method called to retrieve all users with role user');
        $users = User::where('role', '=', 'user')->paginate();
        Log::debug('Users retrieved', ['count' => count($users->items())]);
        return $this->preferredFormat($users);
    }

    /**
     * @OA\Post(
     *      path="/users/register",
     *      operationId="storeUser",
     *      tags={"User"},
     *      summary="Store new user",
     *      description="Store new user",
     *      @OA\RequestBody(
     *          required=true,
     *          description="User request object",
     *          @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/UserResponse")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *  )
     */
    public function store(StoreCustomer $request)
    {
        Log::info('Store method called for new user registration');
        $input = $request->all();
        $input['role'] = $input['role'] ?? 'user';

        // Avoid logging the password or any sensitive data.
        Log::debug('User registration input received', ['email' => $input['email'], 'first_name' => $input['first_name'], 'last_name' => $input['last_name']]);

        if (App::environment('local')) {
            Log::debug('Running in local environment. Sending registration email.');
            Mail::to([$input['email']])->send(new Register($input['first_name'] . ' ' . $input['last_name'], $input['email'], $input['password']));
        }
        // Hash the password
        $input['password'] = hash('sha256', $input['password']);

        $user = User::create($input);
        Log::info('User created successfully', ['user_id' => $user->id]);

        return $this->preferredFormat($user, ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *     path="/users/login",
     *     summary="Login customer",
     *     operationId="login-customer",
     *     tags={"User"},
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *                mediaType="application/json",
     *           @OA\Schema(
     *               title="AccountRequest",
     *               @OA\Property(property="email",
     *                        type="string",
     *                        example="customer@practicesoftwaretesting.com"
     *                    ),
     *               @OA\Property(property="password",
     *                        type="string",
     *                        example="welcome01"
     *                    )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A token",
     *        @OA\MediaType(
     *                mediaType="application/json",
     *           @OA\Schema(
     *               title="TokenResponse",
     *               @OA\Property(property="access_token",
     *                        type="string",
     *                        example="super-secret-token",
     *                        description=""
     *                    ),
     *               @OA\Property(property="token_type",
     *                        type="string",
     *                        example="Bearer",
     *                        description=""
     *                    ),
     *               @OA\Property(property="expires_in",
     *                        type="number",
     *                        example=120,
     *                        description=""
     *                    )
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        Log::info('Login method called');
        // Get raw user input (no validation)
        $email = $request->input('email');
        $password = hash('sha256', $request->input('password'));
        Log::debug('Login attempt', ['email' => $email]);

        // ⚠️ VULNERABLE TO SQL INJECTION!
        $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";
        Log::debug('Executing raw query', ['query' => $query]);
        $user = DB::selectOne($query);

        if (!$user) {
            Log::warning('Login failed: user not found', ['email' => $email]);
            return $this->failedLoginResponse();
        }

        // Get full Eloquent model so we can generate a JWT
        $eloquentUser = User::find($user->id);
        Log::debug('User found and retrieved from Eloquent', ['user_id' => $user->id]);

        if ($user && $user->role != "admin") {
            // Check if account is locked
            if ($user->failed_login_attempts >= 1) {
                Log::notice('Login attempt for locked account', ['user_id' => $user->id]);
                return $this->lockedAccountResponse();
            }
        }

        // Create token manually using Laravel Auth
        $token = app('auth')->login($eloquentUser); // generates JWT

        // Check if login was successful
        if (!$token) {
            Log::warning('JWT generation failed', ['user_id' => $user->id]);
            // Login failed - increment failed login count for non-admins
            if ($user && $user->role != "admin") {
                $this->incrementLoginAttempts($user);
            }
            return $this->failedLoginResponse();
        }

        // Check if the user is enabled
        if (!$user->enabled) {
            Log::notice('Login attempted on disabled account', ['user_id' => $user->id]);
            return response()->json([
                'error' => 'Account disabled.'
            ], ResponseAlias::HTTP_FORBIDDEN);
        }

        // Reset failed login attempts on successful login
        $this->resetLoginAttempts($user);
        Log::info('User logged in successfully', ['user_id' => $user->id]);

        // Return the successful login response
        return $this->successfulLoginResponse($token);
    }

    protected function validateLogin(Request $request)
    {
        Log::debug('Validating login request');
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    }

    protected function incrementLoginAttempts($user)
    {
        Log::debug('Incrementing login attempts', ['user_id' => $user->id, 'failed_login_attempts' => $user->failed_login_attempts]);
        if ($user->failed_login_attempts < 1) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['failed_login_attempts' => $user->failed_login_attempts + 1]);
        }
    }

    protected function resetLoginAttempts($user)
    {
        Log::debug('Resetting login attempts', ['user_id' => $user->id, 'failed_login_attempts' => $user->failed_login_attempts]);
        if ($user->failed_login_attempts < 1) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['failed_login_attempts' => 0]);
        }
    }

    protected function lockedAccountResponse()
    {
        Log::info('Locked account response generated');
        return response()->json(['error' => 'Account locked.'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    protected function failedLoginResponse()
    {
        Log::info('Failed login response generated');
        return response()->json(['error' => 'Unauthorized'], ResponseAlias::HTTP_UNAUTHORIZED);
    }

    protected function successfulLoginResponse($token)
    {
        Log::info('Successful login response generated');
        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *      path="/users/forgot-password",
     *      operationId="forgotPassword",
     *      tags={"User"},
     *      summary="Request a new password",
     *      description="Request a new password, it actually sets the password to `welcome01`",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                @OA\Property(property="email",
     *                         type="string",
     *                         example="customer@practicesoftwaretesting.com"
     *                )
     *             )
     *         )
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *  )
     */
    public function forgotPassword(Request $request)
    {
        Log::info('Forgot password request received', ['email' => $request->get('email')]);
        $request['password'] = app('hash')->make('welcome02');

        if (App::environment('local')) {
            $user = User::where('email', $request['email'])->first();
            Log::debug('Sending forget password email', ['user_id' => $user->id]);
            Mail::to([$request['email']])->send(new ForgetPassword($user->first_name . ' ' . $user->last_name, "welcome02"));
        }
        $updated = (bool)User::where('email', $request['email'])->update(['password' => $request['password']]);
        Log::debug('Forgot password update', ['email' => $request->get('email'), 'updated' => $updated]);

        return $this->preferredFormat(['success' => $updated], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/users/change-password",
     *      operationId="changePassword",
     *      tags={"User"},
     *      summary="Change password",
     *      description="Change the existing password to a new one",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                @OA\Property(property="current_password",
     *                         type="string",
     *                         example="welcome01"
     *                ),
     *                @OA\Property(property="new_password",
     *                         type="string",
     *                         example="welcome02"
     *                ),
     *                @OA\Property(property="new_password_confirmation",
     *                         type="string",
     *                         example="welcome02"
     *                )
     *             )
     *         )
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function changePassword(Request $request)
    {
        Log::info('Change password request initiated', ['user_id' => auth()->user()->id]);
        $current = $request->get('current_password');
        $new = $request->get('new_password');
        $confirm = $request->get('new_password_confirmation');

        if (hash('sha256', $current) !== auth()->user()->password) {
            Log::warning('Password change failed: current password does not match', ['user_id' => auth()->user()->id]);
            return $this->preferredFormat([
                'success' => false,
                'message' => 'Your current password does not matches with the password.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (strcmp($current, $new) == 0) {
            Log::warning('Password change failed: new password is the same as current', ['user_id' => auth()->user()->id]);
            return $this->preferredFormat([
                'success' => false,
                'message' => 'New Password cannot be same as your current password.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = $request->get('new_password');
        $saved = $user->save();
        Log::info('Password changed successfully', ['user_id' => $user->id]);

        return $this->preferredFormat(['success' => $saved]);
    }

    /**
     * @OA\Get(
     *     path="/users/me",
     *     summary="Retrieve current customer info",
     *     operationId="get-current-customer-info",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="A customer",
     *         @OA\JsonContent(ref="#/components/schemas/UserResponse"),
     *     ),
     *     @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function me()
    {
        Log::info('Retrieving current authenticated user info', ['user_id' => auth()->id()]);
        return response()->json(app('auth')->user());
    }

    /**
     * @OA\Get(
     *      path="/users/logout",
     *      operationId="logOut",
     *      tags={"User"},
     *      summary="Logout - invalidate the token",
     *      description="Logout - invalidate the token",
     *      @OA\Response(
     *           response=200,
     *           description="Result of logout",
     *           @OA\MediaType(
     *               mediaType="application/json",
     *               @OA\Schema(
     *                   title="LogoutResponse",
     *                   @OA\Property(property="message",
     *                        type="string",
     *                        example="Successfully logged out",
     *                        description=""
     *                   ),
     *               )
     *           )
     *       ),
     *       @OA\Response(
     *           response=400,
     *           description="Bad Request"
     *       ),
     *       @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *       security={{ "apiAuth": {} }}
     *  )
     */
    public function logout()
    {
        Log::info('Logout requested', ['user_id' => auth()->id()]);
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            Log::info('User successfully logged out', ['user_id' => auth()->id()]);
            return response()->json(['message' => 'Successfully logged out'], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to logout, please try again.'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/users/refresh",
     *      operationId="refreshToken",
     *      tags={"User"},
     *      summary="Retrieve a refreshed token",
     *      description="Retrieve a refreshed token",
     *      @OA\Response(
     *         response=200,
     *         description="A token",
     *        @OA\MediaType(
     *                mediaType="application/json",
     *           @OA\Schema(
     *               title="TokenResponse",
     *               @OA\Property(property="access_token",
     *                        type="string",
     *                        example="super-secret-token",
     *                        description=""
     *                    ),
     *               @OA\Property(property="token_type",
     *                        type="string",
     *                        example="Bearer",
     *                        description=""
     *                    ),
     *               @OA\Property(property="expires_in",
     *                        type="number",
     *                        example=120,
     *                        description=""
     *                    )
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function refresh()
    {
        Log::info('Token refresh requested', ['user_id' => auth()->id()]);
        $newToken = app('auth')->refresh(true, false);
        Log::info('Token refreshed successfully', ['user_id' => auth()->id()]);
        return $this->respondWithToken($newToken);
    }

    /**
     * @OA\Get(
     *      path="/users/{userId}",
     *      operationId="getUser",
     *      tags={"User"},
     *      summary="Retrieve specific user",
     *      description="Retrieve specific user",
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          example=1,
     *          description="The userId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/UserResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function show($id)
    {
        Log::info('Show method called to retrieve user', ['user_id' => $id]);
        $user = User::findOrFail($id);
        return $this->preferredFormat($user);
    }

    /**
     * @OA\Get(
     *      path="/users/search",
     *      operationId="searchUser",
     *      tags={"User"},
     *      summary="Retrieve specific users matching the search query",
     *      description="Search is performed on the `first_name`, `last_name`, or `city` column",
     *      @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="A query phrase",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserResponse")
     *          )
     *       ),
     *       @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       security={{ "apiAuth": {} }}
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        Log::info('Search method called', ['query' => $q]);
        $results = User::where('role', '=', 'user')->where(function ($query) use ($q) {
            $query->where('first_name', 'like', "%$q%")
                ->orWhere('last_name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('city', 'like', "%$q%");
        })->paginate();
        Log::debug('Search results retrieved', ['count' => count($results->items())]);
        return $this->preferredFormat($results);
    }

    /**
     * @OA\Put(
     *      path="/users/{userId}",
     *      operationId="updateUser",
     *      tags={"User"},
     *      summary="Update specific user",
     *      description="Update specific user",
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          description="The userId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="User request object",
     *          @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function update(UpdateCustomer $request, $id)
    {
        Log::info('Update method called for user', ['user_id' => $id]);
        if ((app('auth')->id() == $id) || (app('auth')->parseToken()->getPayload()->get('role') == "admin")) {
            unset($request['enabled']);
            $updated = (bool)User::where('id', $id)->update($request->all());
            Log::debug('User update attempted', ['user_id' => $id, 'success' => $updated]);
            return $this->preferredFormat(['success' => $updated], ResponseAlias::HTTP_OK);
        } else {
            Log::warning('Unauthorized update attempt', ['user_id' => $id, 'current_user' => app('auth')->id()]);
            return response()->json(['error' => 'You can only update your own data.'], ResponseAlias::HTTP_FORBIDDEN);
        }
    }

    /**
     * @OA\Delete(
     *      path="/users/{userId}",
     *      operationId="deleteUser",
     *      tags={"User"},
     *      summary="Delete specific user",
     *      description="Admin role is required to delete a specific user",
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          description="The userId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function destroy(DestroyCustomer $request, $id)
    {
        Log::info('Destroy method called for user deletion', ['user_id' => $id]);
        try {
            if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
                $user = User::find($id);
                if ($user) {
                    $user->delete();
                    Log::info('User deleted successfully', ['user_id' => $id]);
                    return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
                } else {
                    Log::warning('User not found for deletion', ['user_id' => $id]);
                    return response()->json(['error' => 'User not found.'], ResponseAlias::HTTP_NOT_FOUND);
                }
            } else {
                Log::warning('Unauthorized deletion attempt', ['attempting_user' => app('auth')->parseToken()->getPayload()->get('role')]);
                return response()->json(['error' => 'Only admins can delete accounts.'], ResponseAlias::HTTP_FORBIDDEN);
            }
        } catch (QueryException $e) {
            Log::error('QueryException during user deletion', ['user_id' => $id, 'error' => $e->getMessage()]);
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this customer is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
