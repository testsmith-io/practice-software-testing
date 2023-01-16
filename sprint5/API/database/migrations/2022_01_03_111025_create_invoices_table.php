<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('invoice_date');
            $table->string('invoice_number', 70)->nullable();
            $table->string('billing_address', 70);
            $table->string('billing_city', 40);
            $table->string('billing_state', 40)->nullable();
            $table->string('billing_country', 40);
            $table->string('billing_postcode', 10)->nullable();
            $table->decimal('total', 10, 2);
            $table->string('payment_method', 40)->nullable();
            $table->string('payment_account_name', 40)->nullable();
            $table->string('payment_account_number', 40)->nullable();
            $table->enum('status', ['AWAITING_FULFILLMENT', 'ON_HOLD', 'AWAITING_SHIPMENT', 'SHIPPED', 'COMPLETED'])->default('AWAITING_FULFILLMENT');
            $table->string('status_message', 255)->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
