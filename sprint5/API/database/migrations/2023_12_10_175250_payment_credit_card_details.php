<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('payment_credit_card_details', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('credit_card_number', 40);
            $table->string('expiration_date', 10);
            $table->string('cvv', 10);
            $table->string('card_holder_name', 70);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('payment_credit_card_details');
    }
};
