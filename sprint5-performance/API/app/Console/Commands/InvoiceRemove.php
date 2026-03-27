<?php

namespace app\Console\Commands;

use App\Models\Download;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InvoiceRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:remove';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all PDF invoices';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = Storage::allFiles('invoices');
        Storage::delete($files);
        Download::query()->truncate();
    }
}
