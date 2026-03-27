<?php

namespace App\Services;

use App\Mail\Contact;
use App\Models\ContactRequestReply;
use App\Models\ContactRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function sendContactMessage(array $input, $isAuth)
    {
        Log::info('Sending contact message', ['input' => $input, 'isAuth' => $isAuth]);

        if ($isAuth) {
            $input['user_id'] = Auth::user()->id;
            Log::debug('User is authenticated', ['user_id' => $input['user_id']]);
        }

        $input['status'] = 'NEW';
        $result = ContactRequests::create($input);
        Log::info('Contact request created', ['id' => $result->id]);

        if (App::environment('local')) {
            $email = $input['email'] ?? Auth::user()->email;
            $name = $input['name'] ?? Auth::user()->first_name . ' ' . Auth::user()->last_name;

            Log::debug('Sending email to user', ['email' => $email, 'name' => $name]);

            Mail::to([$email])->send(new Contact($name, $input['subject'], $input['message']));

            Log::info('Contact email sent');
        }

        return $result;
    }

    public function attachFile($id, $file)
    {
        Log::info('Attaching file to contact request', ['request_id' => $id]);

        $errors = [];

        if (!$file) {
            $errors[] = "No file attached.";
        } elseif ($file->getSize() != 0) {
            $errors[] = "Currently we only allow empty files.";
        } elseif ($file->getClientOriginalExtension() != 'txt') {
            $errors[] = "The file extension is incorrect, we only accept txt files.";
        }

        if (!empty($errors)) {
            Log::warning('File attachment validation failed', ['errors' => $errors]);
        } else {
            Log::info('File passed validation checks');
        }

        return $errors;
    }

    public function getMessages($role, $userId)
    {
        Log::info('Fetching messages', ['role' => $role, 'user_id' => $userId]);

        if ($role === 'admin') {
            return ContactRequests::with('user')->orderBy('created_at', 'DESC')->paginate();
        } else {
            return ContactRequests::where('user_id', $userId)->orderBy('created_at', 'DESC')->paginate();
        }
    }

    public function getMessageById($id, $role, $userId)
    {
        Log::info('Fetching message by ID', ['message_id' => $id, 'role' => $role, 'user_id' => $userId]);

        if ($role === 'admin') {
            return ContactRequests::with(['user', 'replies', 'replies.user'])
                ->where('id', $id)
                ->orderBy('created_at', 'DESC')
                ->first();
        } else {
            return ContactRequests::with(['user', 'replies', 'replies.user'])
                ->where('id', $id)
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->first();
        }
    }

    public function addReply($id, array $data)
    {
        $data['message_id'] = $id;
        $data['user_id'] = Auth::user()->id;

        Log::info('Adding reply to contact message', ['message_id' => $id, 'user_id' => $data['user_id']]);

        ContactRequests::where('id', $id)->update(['status' => 'IN_PROGRESS']);
        $reply = ContactRequestReply::create($data);

        Log::debug('Reply added', ['reply_id' => $reply->id]);

        return $reply;
    }

    public function updateStatus($id, $status)
    {
        Log::info('Updating contact request status', ['message_id' => $id, 'new_status' => $status]);
        return ContactRequests::where('id', $id)->update(['status' => $status]);
    }
}
