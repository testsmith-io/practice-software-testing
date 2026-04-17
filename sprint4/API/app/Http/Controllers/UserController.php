<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:users', ['except' => ['login', 'store', 'forgotPassword', 'refresh']]);
        $this->middleware('assign.guard:users');
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
     *               @OA\Property(property="from", type="integer", nullable=true, example=1),
     *               @OA\Property(property="last_page", type="integer", example=1),
     *               @OA\Property(property="per_page", type="integer", example=1),
     *               @OA\Property(property="to", type="integer", nullable=true, example=1),
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
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(response="409", ref="#/components/responses/DuplicateConflictResponse")
     * )
     */
    public function store(StoreCustomer $request)
    {
        $input = $request->all();
        $input['role'] = 'user';

        if (App::environment('local')) {
            Mail::to([$input['email']])->queue(new Register($input['first_name'] . ' ' . $input['last_name'], $input['email'], $input['password']));
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
        $credentials = $request->all(['email', 'password']);

        if (!$token = app('auth')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], ResponseAlias::HTTP_UNAUTHORIZED);
        }
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
     *                title="ForgotPasswordRequest",
     *                @OA\Property(property="email",
     *                         type="string",
     *                         example="customer@practicesoftwaretesting.com"
     *                )
     *              )
     *          )
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
        $request->validate([
            'email' => 'exists:users,email'
        ]);

        $request['password'] = app('hash')->make('welcome02');

        if (App::environment('local')) {
            $user = User::where('email', $request['email'])->first();
            Mail::to([$request['email']])->queue(new ForgetPassword($user->first_name . ' ' . $user->last_name, "welcome02"));
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
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *                mediaType="application/json",
     *           @OA\Schema(
     *               title="ChangePasswordRequest",
     *               @OA\Property(property="current_password",
     *                        type="string",
     *                        example="welcome01"
     *               ),
     *               @OA\Property(property="new_password",
     *                        type="string",
     *                        example="welcome02"
     *               ),
     *               @OA\Property(property="new_password_confirmation",
     *                        type="string",
     *                        example="welcome02"
     *               )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *     @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *     security={{ "apiAuth": {} }}
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
            'new_password' => 'required|string|min:8|confirmed',
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
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out'], ResponseAlias::HTTP_OK);
        } catch (JWTException $e) {
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
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                title="TokenResponse",
     *                @OA\Property(property="access_token",
     *                         type="string",
     *                         example="super-secret-token",
     *                         description=""
     *                     ),
     *                @OA\Property(property="token_type",
     *                         type="string",
     *                         example="Bearer",
     *                         description=""
     *                     ),
     *                @OA\Property(property="expires_in",
     *                         type="number",
     *                         example=120,
     *                         description=""
     *                     )
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
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/UserResponse")
     *       ),
     *       @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *       security={{ "apiAuth": {} }}
     * )
     */
    public function show($id)
    {
        return $this->preferredFormat(User::findOrFail($id));
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
     *               @OA\Property(property="from", type="integer", nullable=true, example=1),
     *               @OA\Property(property="last_page", type="integer", example=1),
     *               @OA\Property(property="per_page", type="integer", example=1),
     *               @OA\Property(property="to", type="integer", nullable=true, example=1),
     *               @OA\Property(property="total", type="integer", example=1),
     *           )
     *       ),
     *       @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       security={{ "apiAuth": {} }}
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        $builder = User::where('role', '=', 'user');
        // FULLTEXT requires terms of at least ft_min_word_len (default 4).
        if (strlen($q) >= 4 && in_array(\DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $builder->whereRaw(
                'MATCH(first_name, last_name, email, city) AGAINST(? IN BOOLEAN MODE)',
                [$q . '*']
            );
        } else {
            $builder->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%$q%")
                    ->orWhere('last_name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('city', 'like', "%$q%");
            });
        }

        return $this->preferredFormat($builder->paginate());
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
     *      @OA\Response(response="409", ref="#/components/responses/DuplicateConflictResponse"),
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
        if (app('auth')->id() == $id) {
            User::findOrFail($id)->update($request->all());
            return $this->preferredFormat(['success' => true], ResponseAlias::HTTP_OK);
        } else {
            return response()->json(['error' => 'You can only update your own data.'], ResponseAlias::HTTP_FORBIDDEN);
        }
    }


}
