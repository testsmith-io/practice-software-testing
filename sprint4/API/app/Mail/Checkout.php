<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Mail;

use Illuminate\Mail\Mailable;

class Checkout extends Mailable
{
    protected $name;
    protected $contactSubject;
    protected $contactMessage;

    public function __construct($name, $items, $total)
    {
        $this->name = $name;
        $this->items = $items;
        $this->total = $total;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Checkout')
            ->with(['name' => $this->name,
                'items' => $this->items,
                'contactSubject' => $this->contactSubject,
                'contactMessage' => $this->contactMessage,
                'total' => $this->total])
            ->markdown('emails.Checkout')
            ->text('emails.Checkout_plain');
    }
}
