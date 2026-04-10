<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                if (Schema::hasColumn('invoice_items', 'product_id')) {
                    $table->index('product_id', 'idx_invoice_items_product_id');
                }
                if (Schema::hasColumn('invoice_items', 'invoice_id')) {
                    $table->index('invoice_id', 'idx_invoice_items_invoice_id');
                }
            });
        }

        if (Schema::hasTable('favorites') && Schema::hasColumn('favorites', 'product_id')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->index('product_id', 'idx_favorites_product_id');
            });
        }
    }

    public function down() {
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropIndex('idx_invoice_items_product_id');
                $table->dropIndex('idx_invoice_items_invoice_id');
            });
        }

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropIndex('idx_favorites_product_id');
            });
        }
    }
};
