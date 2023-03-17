<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class Contact extends Mailable
{
    protected $name;
    protected $contactSubject;
    protected $contactMessage;

    public function __construct($name, $contactSubject, $contactMessage)
    {
        $this->name = $name;
        $this->contactSubject = $contactSubject;
        $this->contactMessage = $contactMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Contact')
            ->with(['name' => $this->name,
                'contactSubject' => $this->contactSubject,
                'contactMessage' => $this->contactMessage])
            ->markdown('emails.Contact')
            ->text('emails.Contact_plain');
    }
}
