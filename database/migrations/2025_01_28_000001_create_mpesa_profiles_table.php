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
            $table->text('consumer_secret')->comment('Encrypted secret');
            $table->string('base_url')->default('https://sandbox.safaricom.co.ke');
            $table->string('paybill_number')->nullable();
            $table->string('lipa_na_mpesa_shortcode')->nullable();
            $table->text('lipa_na_mpesa_passkey')->nullable()->comment('Encrypted passkey');
            $table->string('lipa_na_mpesa_callback_url')->nullable();
            $table->string('lipa_na_mpesa_transaction_type')->nullable()->comment('CustomerPayBillOnline or CustomerBuyGoodsOnline');
            $table->string('lipa_na_mpesa_till_number')->nullable()->comment('PartyB when the passkey belongs to another shortcode');
            $table->string('callback_url')->nullable();
            $table->string('confirmation_url')->nullable();
            $table->string('validation_url')->nullable();
            $table->string('initiator_username')->nullable();
            $table->text('initiator_password')->nullable()->comment('Encrypted password');
            $table->text('security_credential')->nullable()->comment('Encrypted pre-encrypted credential from the Daraja portal');
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
