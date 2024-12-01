<?php

namespace App\Services;

use App\Mail\Contact;
use App\Models\ContactRequestReply;
use App\Models\ContactRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function sendContactMessage(array $input, $isAuth)
    {
        if ($isAuth) {
            $input['user_id'] = Auth::user()->id;
        }
        $input['status'] = 'NEW';
        $result = ContactRequests::create($input);

        if (App::environment('local')) {
            $email = $input['email'] ?? Auth::user()->email;
            $name = $input['name'] ?? Auth::user()->first_name . ' ' . Auth::user()->last_name;
            Mail::to([$email])->send(new Contact($name, $input['subject'], $input['message']));
        }

        return $result;
    }

    public function attachFile($id, $file)
    {
        $errors = [];

        if (!$file) {
            $errors[] = "No file attached.";
        } elseif ($file->getSize() != 0) {
            $errors[] = "Currently we only allow empty files.";
        } elseif ($file->getClientOriginalExtension() != 'txt') {
            $errors[] = "The file extension is incorrect, we only accept txt files.";
        }

        return $errors;
    }

    public function getMessages($role, $userId)
    {
        if ($role === 'admin') {
            return ContactRequests::with('user')->orderBy('created_at', 'DESC')->paginate();
        } else {
            return ContactRequests::where('user_id', $userId)->orderBy('created_at', 'DESC')->paginate();
        }
    }

    public function getMessageById($id, $role, $userId)
    {
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

        ContactRequests::where('id', $id)->update(['status' => 'IN_PROGRESS']);
        return ContactRequestReply::create($data);
    }

    public function updateStatus($id, $status)
    {
        return ContactRequests::where('id', $id)->update(['status' => $status]);
    }
}
