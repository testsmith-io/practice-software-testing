<?php

namespace App\Http\Controllers;

use App\Services\UserService;
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

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
        $users = $this->userService->getAllUsers();
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
        $user = $this->userService->registerUser($request->all());
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
        $credentials = $request->only(['email', 'password', 'access_token', 'totp']);
        $response = $this->userService->login($credentials);

        if (isset($response['error'])) {
            $statusCode = match ($response['error']) {
                'Account locked, too many failed attempts. Please contact the administrator.' => ResponseAlias::HTTP_LOCKED,
                'Account disabled' => ResponseAlias::HTTP_FORBIDDEN,
                'Invalid or expired token' => ResponseAlias::HTTP_BAD_REQUEST,
                'Unauthorized token usage' => ResponseAlias::HTTP_UNAUTHORIZED,
                'Invalid TOTP' => ResponseAlias::HTTP_UNAUTHORIZED,
                default => ResponseAlias::HTTP_UNAUTHORIZED,
            };
            return response()->json(['error' => $response['error']], $statusCode);
        }

        // TOTP response
        if (isset($response['requires_totp']) && $response['requires_totp']) {
            return response()->json([
                'message' => $response['message'],
                'requires_totp' => $response['requires_totp'],
                'access_token' => $response['access_token'],
            ], ResponseAlias::HTTP_OK);
        }

        // Successful login
        return $this->respondWithToken($response['token']);
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
        $request->validate(['email' => 'exists:users,email']);
        $response = $this->userService->resetPassword($request->email);
        return $this->preferredFormat($response, ResponseAlias::HTTP_OK);
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

        if (!Hash::check($current, Auth::user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Your current password does not matches with the password.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($current === $new) {
            return response()->json([
                'success' => false,
                'message' => 'New Password cannot be same as your current password.',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($new);
        $user->save();

        return response()->json(['success' => true], ResponseAlias::HTTP_OK);
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
        $user = $this->userService->getAuthenticatedUser();
        return response()->json($user);
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
        $response = $this->userService->logout();

        if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['message' => $response['message']], ResponseAlias::HTTP_OK);
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
        $token = $this->userService->refreshToken();
        return $this->respondWithToken($token);
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
        try {
            $currentUserId = Auth::id();
            $currentUserRole = Auth::user()->role;
            $user = $this->userService->getUserById($id, $currentUserId, $currentUserRole);

            return response()->json($user, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_NOT_FOUND);
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
        $users = $this->userService->searchUsers($request->get('q'));
        return $this->preferredFormat($users);
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
        try {
            $currentUserId = app('auth')->id();
            $currentUserRole = app('auth')->parseToken()->getPayload()->get('role');

            // Use the service to handle the update logic
            $response = $this->userService->updateUser($id, $request->all(), $currentUserId, $currentUserRole);

            return $this->preferredFormat($response, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_FORBIDDEN);
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
        try {
            $currentUserId = Auth::id();
            $currentUserRole = Auth::user()->role;
            $data = $request->validated();

            $this->userService->patchUser($id, $data, $currentUserId, $currentUserRole);

            return response()->json(['success' => true], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_FORBIDDEN); // Ensure 403 is returned
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
            // Ensure the authenticated user is an admin
            if (app('auth')->parseToken()->getPayload()->get('role') !== "admin") {
                return $this->preferredFormat(['error' => 'Forbidden'], ResponseAlias::HTTP_FORBIDDEN);
            }

            // Use the service to delete the user
            $this->userService->deleteUser($id);

            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            // Handle specific exceptions for integrity constraint violations
            if ($e->getCode() === '23000') { // SQLSTATE 23000 indicates a foreign key constraint failure
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this customer is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            // Handle other exceptions
            return $this->preferredFormat([
                'error' => $e->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
