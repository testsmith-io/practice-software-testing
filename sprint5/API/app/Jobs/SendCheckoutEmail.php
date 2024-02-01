<?php

namespace App\Jobs;

use App\Mail\Checkout;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCheckoutEmail implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($id, $user) {
        $this->id = $id;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $invoice = Invoice::with('invoicelines', 'invoicelines.product')->where('id', $this->id)->first();

        Mail::to([$this->user->email])->send(new Checkout($this->user->first_name . ' ' . $this->user->last_name, $invoice->invoicelines, $invoice));
    }
}
