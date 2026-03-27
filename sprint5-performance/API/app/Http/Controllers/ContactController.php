<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContact;
use App\Http\Requests\Contact\StoreContactReply;
use App\Services\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ContactController extends Controller
{

    private $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
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
     *          @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(ref="#/components/schemas/ContactRequest"),
     *                  @OA\Schema(ref="#/components/schemas/ContactRequestAuthenticated")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Result of the insert",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  title="AddContactMessageResponse",
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
     * )
     */
    public function send(StoreContact $request)
    {
        $input = $request->all();
        $result = $this->contactService->sendContactMessage($input, Auth::check());
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
     *          @OA\Schema(type="string")
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
     * )
     */
    public function attachFile($id, Request $request)
    {
        $errors = $this->contactService->attachFile($id, $request->file('file'));
        if (!empty($errors)) {
            return $this->preferredFormat(['errors' => $errors], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return $this->preferredFormat(['success' => true], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/messages",
     *      operationId="getMessages",
     *      tags={"Contact"},
     *      summary="Retrieve messages",
     *      description="`admin` retrieves all messages, `user` retrieves only related messages",
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
     *               title="PaginatedContactMessageResponse",
     *               @OA\Property(property="current_page", type="integer", example=1),
     *               @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                       anyOf={
     *                           @OA\Schema(ref="#/components/schemas/ContactResponse"),
     *                           @OA\Schema(ref="#/components/schemas/ContactResponseAuthenticated")
     *                       }
     *                   )
     *               ),
     *               @OA\Property(property="from", type="integer", example=1),
     *               @OA\Property(property="last_page", type="integer", example=1),
     *               @OA\Property(property="per_page", type="integer", example=1),
     *               @OA\Property(property="to", type="integer", example=1),
     *               @OA\Property(property="total", type="integer", example=1),
     *           )
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
        $messages = $this->contactService->getMessages($role, Auth::id());
        return $this->preferredFormat($messages);
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
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(ref="#/components/schemas/ContactResponse"),
     *                  @OA\Schema(ref="#/components/schemas/ContactResponseAuthenticated")
     *              }
     *          )
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
        $message = $this->contactService->getMessageById($id, $role, Auth::id());
        return $this->preferredFormat($message);
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
     *          @OA\Schema(type="string")
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
        $input = $request->all(['message']);
        $reply = $this->contactService->addReply($id, $input);
        return $this->preferredFormat($reply, ResponseAlias::HTTP_CREATED);
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
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *                mediaType="application/json",
     *           @OA\Schema(
     *               title="ContactStatusRequest",
     *               @OA\Property(property="status",
     *                    type="string",
     *                    enum={"NEW", "ON_HOLD", "IN_PROGRESS", "RESOLVED"},
     *                    example="IN_PROGRESS"
     *                )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *     @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *     @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:NEW,IN_PROGRESS,RESOLVED',
        ]);

        $success = $this->contactService->updateStatus($id, $request->input('status'));
        return $this->preferredFormat(['success' => (bool)$success]);
    }

}
