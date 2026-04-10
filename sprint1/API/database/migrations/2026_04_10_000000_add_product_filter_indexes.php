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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'brand_id'], 'idx_products_category_brand');
        });
    }
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_brand');
        });
    }
};
