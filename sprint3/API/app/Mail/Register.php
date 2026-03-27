<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class Register extends Mailable
{
    protected $name;
    protected $contactSubject;
    protected $contactMessage;

    public function __construct($name, $email, $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Register')
            ->with(['name' => $this->name,
                'email' => $this->email,
                'password' => $this->password])
            ->markdown('emails.Register')
            ->text('emails.Register_plain');
    }
}
