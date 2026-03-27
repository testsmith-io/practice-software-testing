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
        // Change stock column in products table from integer to bigInteger
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('stock')->nullable()->change();
        });

        // Change subtotal and total columns in invoices table to handle larger values
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 20, 2)->nullable()->change();
            $table->decimal('total', 20, 2)->nullable()->change();
        });

        // Change quantity column in invoice_items table to handle larger values
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->bigInteger('quantity')->change();
        });

        // Change quantity column in cart_items table to handle larger values
        Schema::table('cart_items', function (Blueprint $table) {
            $table->bigInteger('quantity')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert stock column in products table
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->nullable()->change();
        });

        // Revert subtotal and total columns in invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->nullable()->change();
            $table->decimal('total', 10, 2)->nullable()->change();
        });

        // Revert quantity column in invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        // Revert quantity column in cart_items table
        Schema::table('cart_items', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
        });
    }
};