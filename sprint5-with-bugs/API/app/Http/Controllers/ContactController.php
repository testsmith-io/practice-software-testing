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
     *               @OA\Property(property="access_token",
     *                        type="boolean",
     *                        example="super-secret-token",
     *                        description=""
     *                    )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     * )
     */
    public function send(StoreContact $request)
    {
        if (app('auth')->user()) {
            $input = $request->all();
            $input['user_id'] = app('auth')->user()->id;
            $result = ContactRequests::create($input);
        } else {
            $input = $request->all();
            $result = ContactRequests::create($input);
        }

        if (App::environment('local')) {
            $email = ($request->input('email')) ? $request->input('email') : app('auth')->user()->email;
            $name = ($request->input('name')) ? $request->input('name') : app('auth')->user()->first_name . ' ' . app('auth')->user()->last_name;
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
     *                  @OA\Property(property="success",
     *                       type="boolean",
     *                       example=true,
     *                       description=""
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     * )
     */
    public function attachFile($id, Request $request)
    {
        if ($request->hasFile('file')) {
            if(empty($id)) {
                $result['errors'][] = "No messageId given.";
            }
            if ($request->file('file')->getClientOriginalExtension() != 'txt') {
                $result['errors'][] = "The file extension is incorrect, we only accept txt files.";
            }
        } else {
            $result['errors'][] = "No file attached.";
        }
        if(!empty($result['errors'])) {
            return $this->preferredFormat($result, ResponseAlias::HTTP_BAD_REQUEST);
        } else {
            return $this->preferredFormat(['success' => 'true'], ResponseAlias::HTTP_OK);
        }
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
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
            return $this->preferredFormat(ContactRequests::with('user')->orderBy('created_at', 'DESC')->paginate());
        } else {
            return $this->preferredFormat(ContactRequests::where('user_id', app('auth')->user()->id)->orderBy('created_at', 'DESC')->paginate());
        }
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
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function show($id)
    {
        if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
            return $this->preferredFormat(ContactRequests::with(['user', 'replies', 'replies.user'])->where('id', $id)->orderBy('created_at', 'DESC')->first());
        } else {
            return $this->preferredFormat(ContactRequests::with(['user', 'replies', 'replies.user'])->where('user_id', app('auth')->user()->id)->orderBy('created_at', 'DESC')->first());
        }
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
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function storeReply(StoreContactReply $request, $id)
    {
        $input = $request->all(['message']);
        $input['message_id'] = $id;
        $input['user_id'] = app('auth')->user()->id;

        ContactRequests::where('id', $id)->update(['status' => 'IN_PROGRESS']);
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
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *                mediaType="application/json",
     *           @OA\Schema(
     *               @OA\Property(property="status",
     *                        type="string",
     *                        example="IN_PROGRESS"
     *                    )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Result of the update",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="success",
     *                       type="boolean",
     *                       example=true,
     *                       description=""
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => Rule::in("NEW", "IN_PROGRESS", "RESOLVED")
        ]);

        return $this->preferredFormat(['success' => (bool)ContactRequests::where('id', $id)->update(array('status' => $request['status']))]);
    }

}
