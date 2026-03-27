<?php

namespace app\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class OrderUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:update';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the order to a new state.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Invoice::where('status', '=', 'SHIPPED')->update(array('status' => 'COMPLETED'));

        Invoice::where('status', '=', 'AWAITING_SHIPMENT')->update(array('status' => 'SHIPPED'));

        Invoice::with('payment', 'payment.payment_details')
            ->where('status', '=', 'AWAITING_FULFILLMENT')
            ->whereHas('payment', function ($query) {
                $query->where('payment_method', '!=', 'bank-transfer');
            })
            ->update(array('status' => 'AWAITING_SHIPMENT'));

        Invoice::with('payment', 'payment.payment_details')
            ->where('status', '=', 'AWAITING_FULFILLMENT')
            ->whereHas('payment', function ($query) {
                $query->where('payment_method', '=', 'bank-transfer');
            })
            ->update(array('status' => 'ON_HOLD'));
    }
}
