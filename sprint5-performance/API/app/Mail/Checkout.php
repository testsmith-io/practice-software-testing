<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class Checkout extends Mailable
{
    protected $name;
    protected $contactSubject;
    protected $contactMessage;

    public function __construct($name, $items, $invoice)
    {
        $this->name = $name;
        $this->items = $items;
        $this->invoice = $invoice;
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
                'additional_discount_percentage' => $this->invoice->additional_discount_percentage,
                'additional_discount_amount' => $this->invoice->additional_discount_amount,
                'subtotal' => $this->invoice->subtotal,
                'total' => $this->invoice->total])
            ->markdown('emails.Checkout')
            ->text('emails.Checkout_plain');
    }
}
