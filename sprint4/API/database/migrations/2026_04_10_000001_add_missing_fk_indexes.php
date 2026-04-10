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
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->index('product_id', 'idx_invoice_items_product');
                $table->index('invoice_id', 'idx_invoice_items_invoice');
            });
        }

        if (Schema::hasTable('contact_requests')) {
            Schema::table('contact_requests', function (Blueprint $table) {
                $table->index('user_id', 'idx_contact_requests_user');
                $table->index('status', 'idx_contact_requests_status');
            });
        }

        if (Schema::hasTable('contact_request_replies')) {
            Schema::table('contact_request_replies', function (Blueprint $table) {
                $table->index('message_id', 'idx_contact_replies_message');
                $table->index('user_id', 'idx_contact_replies_user');
            });
        }

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->index('product_id', 'idx_favorites_product');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropIndex('idx_invoice_items_product');
                $table->dropIndex('idx_invoice_items_invoice');
            });
        }

        if (Schema::hasTable('contact_requests')) {
            Schema::table('contact_requests', function (Blueprint $table) {
                $table->dropIndex('idx_contact_requests_user');
                $table->dropIndex('idx_contact_requests_status');
            });
        }

        if (Schema::hasTable('contact_request_replies')) {
            Schema::table('contact_request_replies', function (Blueprint $table) {
                $table->dropIndex('idx_contact_replies_message');
                $table->dropIndex('idx_contact_replies_user');
            });
        }

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropIndex('idx_favorites_product');
            });
        }
    }
};
