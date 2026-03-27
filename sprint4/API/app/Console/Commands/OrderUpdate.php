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
        Invoice::where('status', '=', 'SHIPPED')->update(array('status' => 'COMPLETED'));

        Invoice::where('status', '=', 'AWAITING_SHIPMENT')->update(array('status' => 'SHIPPED'));

        Invoice::where('status', '=', 'AWAITING_FULFILLMENT')->where('payment_method', '!=', 'Bank Transfer')->update(array('status' => 'AWAITING_SHIPMENT'));

        Invoice::where('status', '=', 'AWAITING_FULFILLMENT')->where('payment_method', '=', 'Bank Transfer')->update(array('status' => 'ON_HOLD'));
    }
}
