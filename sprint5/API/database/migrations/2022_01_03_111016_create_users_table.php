<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('uid')->nullable();
            $table->string('provider')->nullable();
            $table->string('first_name', 40);
            $table->string('last_name', 20);
            $table->string('street', 70)->nullable();
            $table->string('city', 40)->nullable();
            $table->string('state', 40)->nullable();
            $table->string('country', 40)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 24)->nullable();
            $table->date('dob')->nullable();
            $table->string('email', 256)->unique();
            $table->string('password')->nullable();
            $table->string('role');
            $table->boolean('enabled')->default(true);
            $table->integer('failed_login_attempts')->default(0);
            $table->string('totp_secret')->nullable();
            $table->boolean('totp_enabled')->default(false);
            $table->timestamp('totp_verified_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->index(['email'], 'idx_invoices_users_email');
            $table->index(['email', 'failed_login_attempts'], 'idx_invoices_users_failed_login_attempts');
            $table->index(['email', 'enabled'], 'idx_invoices_users_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
