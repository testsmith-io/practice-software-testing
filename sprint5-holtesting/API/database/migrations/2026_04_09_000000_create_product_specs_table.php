<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_specs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('spec_name', 100);
            $table->string('spec_value', 255);
            $table->string('spec_unit', 30)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->index(['product_id', 'spec_name'], 'idx_product_specs_product_name');
            $table->index(['spec_name', 'spec_value'], 'idx_product_specs_name_value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_specs');
    }
};
