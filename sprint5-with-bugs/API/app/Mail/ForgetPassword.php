<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ForgetPassword extends Mailable
{
    protected $name;
    protected $password;

    public function __construct($name, $password)
    {
        $this->name = $name;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Forget password')
            ->with(['name' => $this->name,
                'password' => $this->password])
            ->markdown('emails.ForgotPassword')
            ->text('emails.ForgotPassword_plain');
    }
}
