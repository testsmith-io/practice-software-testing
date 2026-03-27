<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContact;
use App\Http\Requests\Contact\StoreContactReply;
use App\Mail\Contact;
use App\Models\ContactRequestReply;
use App\Models\ContactRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:users', ['except' => ['send', 'attachFile']]);
        $this->middleware('assign.guard:users');
    }

    /**
     * @OA\Post(
     *      path="/messages",
     *      operationId="sendMessage",
     *      tags={"Contact"},
     *      summary="Send new contact message",
     *      description="Send new contact message by mail",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Contact request object",
     *          @OA\JsonContent(ref="#/components/schemas/ContactRequest")
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="A token",
     *        @OA\MediaType(
     *                mediaType="application/json",
     *           @OA\Schema(
     *               title="AddContactMessageResponse",
     *               @OA\Property(property="access_token",
     *                        type="boolean",
     *                        example="super-secret-token",
     *                        description=""
     *                    )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *     @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function send(StoreContact $request)
    {
        Log::info('[ContactController@send] Request received', $request->all());

        if (app('auth')->user()) {
            $input = $request->all();
            $input['user_id'] = app('auth')->user()->id;
            Log::debug('[ContactController@send] Authenticated user ID: ' . $input['user_id']);
            $result = ContactRequests::create($input);
        } else {
            $input = $request->all();
            Log::debug('[ContactController@send] Guest submission');
            $result = ContactRequests::create($input);
        }

        Log::info('[ContactController@send] Contact request created', ['id' => $result->id]);

        if (App::environment('local')) {
            $email = $request->input('email') ?? app('auth')->user()->email;
            $name = $request->input('name') ?? app('auth')->user()->first_name . ' ' . app('auth')->user()->last_name;
            Log::debug('[ContactController@send] Sending email to: ' . $email);
            Mail::to([$email])->send(new Contact($name, $request->input('subject'), $request->input('message')));
        }

        return $this->preferredFormat($result, ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/messages/{messageId}/attach-file",
     *      operationId="attachFile",
     *      tags={"Contact"},
     *      summary="Attach file to contact message",
     *      description="Attach file to contact message",
     *      @OA\Parameter(
     *          name="messageId",
     *          in="path",
     *          example=1,
     *          description="The messageId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                title="AttachFileRequest",
     *                @OA\Property(
     *                    description="File",
     *                    property="file",
     *                    type="string", format="binary"
     *                )
     *             )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Result of the file upload",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  title="FileUploadResponse",
     *                  @OA\Property(property="success",
     *                       type="boolean",
     *                       example=true,
     *                       description=""
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function attachFile($id, Request $request)
    {
        Log::info('[ContactController@attachFile] Request to attach file to message ID: ' . $id);

        $result = ['errors' => []];

        if ($request->hasFile('file')) {
            Log::debug('[ContactController@attachFile] File attached: ' . $request->file('file')->getClientOriginalName());

            if (empty($id)) {
                $result['errors'][] = "No messageId given.";
                Log::warning('[ContactController@attachFile] No messageId provided.');
            }

            if ($request->file('file')->getClientOriginalExtension() != 'txt') {
                $result['errors'][] = "The file extension is incorrect, we only accept txt files.";
                Log::warning('[ContactController@attachFile] Invalid file type: ' . $request->file('file')->getClientOriginalExtension());
            }
        } else {
            $result['errors'][] = "No file attached.";
            Log::warning('[ContactController@attachFile] No file found in request.');
        }

        if (!empty($result['errors'])) {
            return $this->preferredFormat($result, ResponseAlias::HTTP_BAD_REQUEST);
        }

        Log::info('[ContactController@attachFile] File successfully validated and accepted.');
        return $this->preferredFormat(['success' => 'true'], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/messages",
     *      operationId="getMessages",
     *      tags={"Contact"},
     *      summary="Retrieve messages",
     *      description="`admin` retrieves all messages, `user` retrieves only related messages",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              title="PaginatedContactMessageResponse",
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/ContactResponse")
     *              ),
     *              @OA\Property(property="next_page_url", type="integer", example=1),
     *              @OA\Property(property="path", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="prev_page_url", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        $role = app('auth')->parseToken()->getPayload()->get('role');
        Log::info('[ContactController@index] Fetching messages for role: ' . $role);

        if ($role == "admin") {
            return $this->preferredFormat(ContactRequests::with('user')->orderBy('created_at', 'DESC')->paginate());
        }

        $userId = app('auth')->user()->id;
        return $this->preferredFormat(ContactRequests::where('user_id', $userId)->orderBy('created_at', 'DESC')->paginate());
    }

    /**
     * @OA\Get(
     *      path="/messages/{messageId}",
     *      operationId="getMessage",
     *      tags={"Contact"},
     *      summary="Retrieve specific message",
     *      description="Retrieve specific message",
     *      @OA\Parameter(
     *          name="messageId",
     *          in="path",
     *          example=1,
     *          description="The messageId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ContactResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function show($id)
    {
        $role = app('auth')->parseToken()->getPayload()->get('role');
        Log::info('[ContactController@show] Fetching message ID: ' . $id . ' for role: ' . $role);

        if ($role == "admin") {
            return $this->preferredFormat(ContactRequests::with(['user', 'replies', 'replies.user'])->where('id', $id)->orderBy('created_at', 'DESC')->first());
        }

        $userId = app('auth')->user()->id;
        return $this->preferredFormat(ContactRequests::with(['user', 'replies', 'replies.user'])->where('user_id', $userId)->orderBy('created_at', 'DESC')->first());
    }

    /**
     * @OA\Post(
     *      path="/messages/{messageId}/reply",
     *      operationId="replyToMessage",
     *      tags={"Contact"},
     *      summary="Send new contact message",
     *      description="Send new contact message by mail",
     *      @OA\Parameter(
     *          name="messageId",
     *          in="path",
     *          example=1,
     *          description="The messageId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Contact request object",
     *          @OA\JsonContent(ref="#/components/schemas/ContactRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ContactReplyResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function storeReply(StoreContactReply $request, $id)
    {
        Log::info('[ContactController@storeReply] Storing reply for message ID: ' . $id);

        $input = $request->all(['message']);
        $input['message_id'] = $id;
        $input['user_id'] = app('auth')->user()->id;

        ContactRequests::where('id', $id)->update(['status' => 'IN_PROGRESS']);
        Log::debug('[ContactController@storeReply] Contact request status updated to IN_PROGRESS');

        return $this->preferredFormat(ContactRequestReply::create($input), ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *      path="/messages/{messageId}/status",
     *      operationId="updateMessageStatus",
     *      tags={"Contact"},
     *      summary="Set a new message status",
     *      description="Set a new message status. Possible values: `NEW`, `IN_PROGRESS`, `RESOLVED`",
     *      @OA\Parameter(
     *          name="messageId",
     *          in="path",
     *          example=1,
     *          description="The messageId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                title="ContactStatusRequest",
     *                @OA\Property(property="status",
     *                    type="string",
     *                    enum={"NEW", "ON_HOLD", "IN_PROGRESS", "RESOLVED"},
     *                    example="IN_PROGRESS"
     *                )
     *             )
     *          )
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function updateStatus($id, Request $request)
    {
        Log::info('[ContactController@updateStatus] Updating status for message ID: ' . $id, ['status' => $request['status']]);

        $request->validate([
            'status' => Rule::in("NEW", "IN_PROGRESS", "RESOLVED")
        ]);

        $success = (bool)ContactRequests::where('id', $id)->update(['status' => $request['status']]);

        Log::debug('[ContactController@updateStatus] Update success: ' . json_encode($success));

        return $this->preferredFormat(['success' => $success]);
    }
}
