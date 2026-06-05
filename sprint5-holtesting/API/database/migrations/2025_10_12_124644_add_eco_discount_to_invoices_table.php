<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('eco_discount_percentage', 5, 2)->nullable()->after('additional_discount_amount');
            $table->decimal('eco_discount_amount', 10, 2)->nullable()->after('eco_discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['eco_discount_percentage', 'eco_discount_amount']);
        });
    }
};
