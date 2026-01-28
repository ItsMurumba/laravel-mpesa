<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Profile identifier (e.g., tenant slug, customer ID)');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('base_url')->default('https://sandbox.safaricom.co.ke');
            $table->string('paybill_number')->nullable();
            $table->string('lipa_na_mpesa_shortcode')->nullable();
            $table->string('lipa_na_mpesa_passkey')->nullable();
            $table->string('lipa_na_mpesa_callback_url')->nullable();
            $table->string('callback_url')->nullable();
            $table->string('confirmation_url')->nullable();
            $table->string('validation_url')->nullable();
            $table->string('initiator_username')->nullable();
            $table->text('initiator_password')->nullable()->comment('Encrypted password');
            $table->string('environment')->default('sandbox');
            $table->string('queue_timeout_url')->nullable();
            $table->string('result_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_profiles');
    }
};
