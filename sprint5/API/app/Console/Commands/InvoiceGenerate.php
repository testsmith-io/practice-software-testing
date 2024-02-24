<?php

namespace app\Console\Commands;

use App\Jobs\CreateInvoicePDF;
use App\Models\Download;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InvoiceGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:generate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create PDF invoices.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $invoices = Invoice::all();
        foreach ($invoices as $invoice) {
            if(!Storage::exists("invoices/{$invoice['invoice_number']}.pdf")) {
                Download::create([
                    'name' => $invoice['invoice_number'],
                    'type' => 'INVOICE',
                    'status' => 'INITIATED',
                    'file_name' => $invoice['invoice_number'] . '.pdf'
                ]);
                CreateInvoicePDF::dispatch($invoice['invoice_number']);
            }
        }
    }
}
