<?php

namespace App\Jobs;

use App\Models\Download;
use App\Models\Invoice;
use App\Models\JobsInformation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateInvoicePDF implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    /**
     * Create a new job instance.
     */
    public function __construct($id) {
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $jobInfo = new JobsInformation();
        $jobInfo->name = get_class();
        $jobInfo->start_time = now();
        $jobInfo->save();

        Download::where('name', $this->id)->update(['status' => 'IN_PROGRESS']);

        $invoice = Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->where('invoice_number', $this->id)->first();

        $pdf = PDF::loadView('invoice', ['invoice' => $invoice])->setPaper('legal', 'portrait');
        $fileName = sprintf('%s.pdf',
            $invoice['invoice_number']
        );

        $pdfFilePath = '/invoices/' . $fileName;
        Storage::disk('local')->put($pdfFilePath, $pdf->output());

        Download::where('name', $this->id)
            ->update(['status' => 'COMPLETED']);

        $jobInfo->end_time = now();
        $jobInfo->duration_ms = $jobInfo->start_time->diffInMilliseconds($jobInfo->end_time);
        $jobInfo->save();
    }

}
