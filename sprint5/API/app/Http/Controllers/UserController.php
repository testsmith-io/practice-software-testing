<?php

namespace App\Http\Controllers;

use PragmaRX\Google2FA\Google2FA;
use App\Http\Requests\Customer\DestroyCustomer;
use App\Http\Requests\Customer\PatchCustomer;
use App\Http\Requests\Customer\StoreCustomer;
use App\Http\Requests\Customer\UpdateCustomer;
use App\Mail\ForgetPassword;
use App\Mail\Register;
use App\Models\User;
use App\Rules\SubscriptSuperscriptRule;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 3;

    public function __construct()
    {
        $this->middleware('auth:users', ['except' => ['login', 'store', 'forgotPassword', 'refresh']]);
        $this->middleware('assign.guard:users');
        $this->middleware('role:admin', ['only' => ['index', 'destroy']]);
    }

    /**
     * @OA\Get(
     *      path="/users",
     *      operationId="getUsers",
     *      tags={"User"},
     *      summary="Retrieve all users",
     *      description="Retrieve all users",
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="pagenumber",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserResponse")
     *          )
     *      ),
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
        return $this->preferredFormat(User::where('role', '=', 'user')->paginate());
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
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(StoreCustomer $request)
    {
        $input = $request->all();
        $input['role'] = 'user';

        if (App::environment('local')) {
            Mail::to([$input['email']])->send(new Register("{$input['first_name']} {$input['last_name']}", $input['email'], $input['password']));
        }
        // Hash the password
        $input['password'] = app('hash')->make($input['password']);
        return $this->preferredFormat(User::create($input), ResponseAlias::HTTP_CREATED);
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
        // Case 1: Login with email and password
        if ($request->has(['email', 'password'])) {
            $credentials = $request->only(['email', 'password']);

            // Find the user
            $user = User::where('email', $credentials['email'])->first();

            // Handle login failures and account locking
            if ($user && $user->role != "admin") {
                if ($user->failed_login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
                    return $this->lockedAccountResponse();
                }
            }

            // Attempt to authenticate
            $token = app('auth')->attempt($credentials);

            if (!$token) {
                if ($user && $user->role != "admin") {
                    $this->incrementLoginAttempts($user);
                }
                return $this->failedLoginResponse();
            }

            // Check if user is enabled
            if (!$user->enabled) {
                return response()->json([
                    'error' => 'Account disabled.',
                ], ResponseAlias::HTTP_FORBIDDEN);
            }

            // Handle TOTP-enabled users
            if ($user->totp_enabled) {
                // Invalidate the token and issue a temporary one for TOTP verification
                app('auth')->invalidate($token);

                $tempToken = app('auth')->attempt($credentials);
                return response()->json([
                    'message' => 'TOTP required. Please provide your TOTP code.',
                    'requires_totp' => true,
                    'access_token' => $tempToken,
                ], ResponseAlias::HTTP_OK);
            }

            // Reset failed login attempts
            $this->resetLoginAttempts($user);

            // Return successful login response
            return $this->successfulLoginResponse($token);
        }

        // Case 2: Login with access_token and TOTP
        if ($request->has(['access_token', 'totp'])) {
            $accessToken = $request->input('access_token');
            $totpCode = $request->input('totp');

            $user = User::find(JWTAuth::setToken($accessToken)->toUser()->id);

            // Validate user and TOTP
            if (!$user || !$user->totp_enabled) {
                return response()->json(['error' => 'Unauthorized'], ResponseAlias::HTTP_UNAUTHORIZED);
            }

            $google2fa = new Google2FA();
            if (!$google2fa->verifyKey($user->totp_secret, $totpCode)) {
                return response()->json(['error' => 'Invalid TOTP'], ResponseAlias::HTTP_UNAUTHORIZED);
            }

            // Generate a new token after TOTP verification
            $finalToken = app('auth')->login($user);

            return $this->successfulLoginResponse($finalToken);
        }

        // Invalid request payload
        return response()->json([
            'error' => 'Invalid login request. Provide either email/password or access_token/TOTP.',
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    }

    protected function incrementLoginAttempts($user)
    {
        if ($user->failed_login_attempts < self::MAX_LOGIN_ATTEMPTS) {
            $user->increment('failed_login_attempts');
        }
    }

    protected function resetLoginAttempts($user)
    {
        $user->update(['failed_login_attempts' => 0]);
    }

    protected function lockedAccountResponse()
    {
        return response()->json(['error' => 'Account locked, too many failed attempts. Please contact the administrator.'], ResponseAlias::HTTP_LOCKED);
    }

    protected function failedLoginResponse()
    {
        return response()->json(['error' => 'Unauthorized'], ResponseAlias::HTTP_UNAUTHORIZED);
    }

    protected function successfulLoginResponse($token)
    {
        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *      path="/users/forgot-password",
     *      operationId="forgotPassword",
     *      tags={"User"},
     *      summary="Request a new password",
     *      description="Request a new password, it actually sets the password to `welcome02`",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                @OA\Property(property="email",
     *                         type="string",
     *                         example="customer@practicesoftwaretesting.com"
     *                )
     *            )
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
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'exists:users,email'
        ]);

        $request['password'] = app('hash')->make('welcome02');

        if (App::environment('local')) {
            $user = User::where('email', $request['email'])->first();
            Mail::to([$request['email']])->send(new ForgetPassword("{$user->first_name} {$user->last_name}", "welcome02"));
        }
        return $this->preferredFormat(['success' => (bool)User::where('email', $request['email'])->update(['password' => $request['password']])], ResponseAlias::HTTP_OK);
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
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function changePassword(Request $request)
    {
        $current = $request->get('current_password');
        $new = $request->get('new_password');
        $confirm = $request->get('new_password_confirmation');

        if (!(Hash::check($current, Auth::user()->password))) {
            return $this->preferredFormat([
                'success' => false,
                'message' => 'Your current password does not matches with the password.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (strcmp($current, $new) == 0) {
            return $this->preferredFormat([
                'success' => false,
                'message' => 'New Password cannot be same as your current password.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed', new SubscriptSuperscriptRule()],
        ]);

        $user = Auth::user();
        $user->password = app('hash')->make($request->get('new_password'));

        return $this->preferredFormat(['success' => $user->save()]);
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
        return response()->json(Auth::user());
    }

    /**
     * @OA\Get(
     *      path="/users/logout",
     *      operationId="logOut",
     *      tags={"User"},
     *      summary="Logout - invalidate the token",
     *      description="Logout - invalidate the token",
     *      @OA\Response(
     *          response=200,
     *          description="Result of logout",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  title="LogoutResponse",
     *                  @OA\Property(property="message",
     *                       type="string",
     *                       example="Successfully logged out",
     *                       description=""
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function logout()
    {
        app('auth')->logout();

        return response()->json(['message' => 'Successfully logged out']);
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
        return $this->respondWithToken(app('auth')->refresh(true, false));
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
     *          @OA\Schema(type="string")
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
        if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
            return $this->preferredFormat(User::findOrFail($id));
        } else {
            return $this->preferredFormat(User::where('id', Auth::user()->id)->findOrFail($id));
        }
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
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="pagenumber",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserResponse")
     *          )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        return $this->preferredFormat(User::where('role', '=', 'user')->where(function ($query) use ($q) {
            $query->where('first_name', 'like', "%$q%")
                ->orWhere('last_name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('city', 'like', "%$q%");
        })->paginate());
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
     *          @OA\Schema(type="string")
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
        $user = User::findOrFail($id);

        // Check if the current user is the same as the one being updated or is an admin
        if ((app('auth')->id() == $id) || (app('auth')->parseToken()->getPayload()->get('role') == "admin")) {
            // If the 'role' field is present in the request, ensure the authenticated user is an admin
            if ($request->has('role')) {
                if (app('auth')->parseToken()->getPayload()->get('role') !== "admin") {
                    return response()->json(['error' => 'Only admins can update the role.'], ResponseAlias::HTTP_FORBIDDEN);
                }
            }

            // Update the user with the request data
            $updateData = $request->except('password');

            // For non-admin users, remove the 'role' field from the update data if present
            if (app('auth')->parseToken()->getPayload()->get('role') !== "admin") {
                unset($updateData['role']);
            }

            $success = $user->update($updateData);
            return $this->preferredFormat(['success' => (bool)$success], ResponseAlias::HTTP_OK);
        } else {
            return response()->json(['error' => 'You can only update your own data.'], ResponseAlias::HTTP_FORBIDDEN);
        }
    }

    /**
     * @OA\Patch(
     *      path="/users/{userId}",
     *      operationId="patchUser",
     *      tags={"User"},
     *      summary="Partially update specific user",
     *      description="Partially update specific user",
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          description="The userId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Partial user request object. Only fields to be updated should be included.",
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
    public function patch(PatchCustomer $request, $id)
    {
        $user = User::findOrFail($id);

        // Check if the current user is the same as the one being updated or is an admin
        if ((app('auth')->id() == $id) || (app('auth')->parseToken()->getPayload()->get('role') == "admin")) {
            // If the 'role' field is present in the request, ensure the authenticated user is an admin
            if ($request->has('role')) {
                if (app('auth')->parseToken()->getPayload()->get('role') !== "admin") {
                    return response()->json(['error' => 'Only admins can update the role.'], ResponseAlias::HTTP_FORBIDDEN);
                }
            }

            // Update the user with the request data
            $updateData = $request->except('password');

            // For non-admin users, remove the 'role' field from the update data if present
            if (app('auth')->parseToken()->getPayload()->get('role') !== "admin") {
                unset($updateData['role']);
            }

            $success = $user->update($updateData);
            return $this->preferredFormat(['success' => (bool)$success], ResponseAlias::HTTP_OK);
        } else {
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
     *          @OA\Schema(type="string")
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
     *     security={{ "apiAuth": {} }}
     * ),
     */
    public function destroy(DestroyCustomer $request, $id)
    {
        try {
            if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
                User::find($id)->delete();
                return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
            }
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this customer is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }

}
