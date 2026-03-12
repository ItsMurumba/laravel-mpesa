<?php

use Itsmurumba\Mpesa\Mpesa;
use Itsmurumba\Mpesa\MpesaManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

describe('Database Profiles', function () {
    beforeEach(function () {
        if (! DB::getSchemaBuilder()->hasTable('mpesa_profiles')) {
            DB::getSchemaBuilder()->create('mpesa_profiles', function ($table) {
                $table->id();
                $table->string('name')->unique();
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
                $table->text('initiator_password')->nullable();
                $table->string('environment')->default('sandbox');
                $table->string('queue_timeout_url')->nullable();
                $table->string('result_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        config()->set('mpesa.use_database', true);
    });

    afterEach(function () {
        if (DB::getSchemaBuilder()->hasTable('mpesa_profiles')) {
            DB::table('mpesa_profiles')->truncate();
        }
    });

    it('resolves profile from database when enabled', function () {
        DB::table('mpesa_profiles')->insert([
            'name' => 'tenant_db',
            'consumer_key' => 'db-key',
            'consumer_secret' => 'db-secret',
            'base_url' => 'https://example.test',
            'lipa_na_mpesa_shortcode' => '999999',
            'lipa_na_mpesa_passkey' => 'db-passkey',
            'lipa_na_mpesa_callback_url' => 'https://example.test/db-callback',
            'environment' => 'sandbox',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpesa = new Mpesa('tenant_db');

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);
        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('999999');
        expect($payload['CallBackURL'])->toBe('https://example.test/db-callback');
    });

    it('falls back to config when database profile not found', function () {
        config()->set('mpesa.profiles', [
            'tenant_config' => [
                'consumerKey' => 'config-key',
                'consumerSecret' => 'config-secret',
                'lipaNaMpesaShortcode' => '888888',
                'lipaNaMpesaPasskey' => 'config-passkey',
                'lipaNaMpesaCallbackURL' => 'https://example.test/config-callback',
                'baseUrl' => 'https://example.test',
            ],
        ]);

        $mpesa = new Mpesa('tenant_config');

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);
        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('888888');
    });

    it('ignores inactive profiles in database', function () {
        DB::table('mpesa_profiles')->insert([
            'name' => 'inactive_tenant',
            'consumer_key' => 'inactive-key',
            'consumer_secret' => 'inactive-secret',
            'base_url' => 'https://example.test',
            'lipa_na_mpesa_shortcode' => '777777',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        config()->set('mpesa.profiles', [
            'inactive_tenant' => [
                'consumerKey' => 'config-key',
                'consumerSecret' => 'config-secret',
                'lipaNaMpesaShortcode' => '666666',
                'lipaNaMpesaPasskey' => 'config-passkey',
                'lipaNaMpesaCallbackURL' => 'https://example.test/config-callback',
                'baseUrl' => 'https://example.test',
            ],
        ]);

        $mpesa = new Mpesa('inactive_tenant');

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);
        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('666666');
    });

    it('uses database profile via MpesaManager', function () {
        DB::table('mpesa_profiles')->insert([
            'name' => 'manager_test',
            'consumer_key' => 'manager-key',
            'consumer_secret' => 'manager-secret',
            'base_url' => 'https://example.test',
            'lipa_na_mpesa_shortcode' => '555555',
            'lipa_na_mpesa_passkey' => 'manager-passkey',
            'lipa_na_mpesa_callback_url' => 'https://example.test/manager-callback',
            'environment' => 'sandbox',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $manager = new MpesaManager();
        $mpesa = $manager->for('manager_test');

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);
        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('555555');
        expect($payload['CallBackURL'])->toBe('https://example.test/manager-callback');
    });

    it('handles missing database table gracefully', function () {
        if (DB::getSchemaBuilder()->hasTable('mpesa_profiles')) {
            DB::getSchemaBuilder()->drop('mpesa_profiles');
        }

        config()->set('mpesa.profiles', [
            'fallback' => [
                'consumerKey' => 'fallback-key',
                'consumerSecret' => 'fallback-secret',
                'lipaNaMpesaShortcode' => '444444',
                'lipaNaMpesaPasskey' => 'fallback-passkey',
                'lipaNaMpesaCallbackURL' => 'https://example.test/fallback-callback',
                'baseUrl' => 'https://example.test',
            ],
        ]);

        $mpesa = new Mpesa('fallback');

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);
        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('444444');
    });

    it('respects use_database config flag', function () {
        DB::table('mpesa_profiles')->insert([
            'name' => 'disabled_db',
            'consumer_key' => 'db-key',
            'consumer_secret' => 'db-secret',
            'base_url' => 'https://example.test',
            'lipa_na_mpesa_shortcode' => '333333',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        config()->set('mpesa.use_database', false);
        config()->set('mpesa.profiles', [
            'disabled_db' => [
                'consumerKey' => 'config-key',
                'consumerSecret' => 'config-secret',
                'lipaNaMpesaShortcode' => '222222',
                'lipaNaMpesaPasskey' => 'config-passkey',
                'lipaNaMpesaCallbackURL' => 'https://example.test/config-callback',
                'baseUrl' => 'https://example.test',
            ],
        ]);

        $mpesa = new Mpesa('disabled_db');

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);
        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('222222');
    });

    it('uses database for default profile when no profile is provided', function () {
        config()->set('mpesa.default_profile', 'tenant_default_db');

        DB::table('mpesa_profiles')->insert([
            'name' => 'tenant_default_db',
            'consumer_key' => 'db-key',
            'consumer_secret' => 'db-secret',
            'base_url' => 'https://example.test',
            'lipa_na_mpesa_shortcode' => '101010',
            'lipa_na_mpesa_passkey' => 'db-passkey',
            'lipa_na_mpesa_callback_url' => 'https://example.test/default-db-callback',
            'environment' => 'sandbox',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpesa = new Mpesa(); // no profile passed

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);
        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('101010');
        expect($payload['CallBackURL'])->toBe('https://example.test/default-db-callback');
    });

    it('decrypts initiator password when stored encrypted', function () {
        $encrypted = Crypt::encryptString('plain-password');

        DB::table('mpesa_profiles')->insert([
            'name' => 'tenant_encrypted',
            'consumer_key' => 'db-key',
            'consumer_secret' => 'db-secret',
            'base_url' => 'https://example.test',
            'lipa_na_mpesa_shortcode' => '202020',
            'lipa_na_mpesa_passkey' => 'db-passkey',
            'initiator_password' => $encrypted,
            'environment' => 'sandbox',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpesa = new Mpesa('tenant_encrypted');

        expect(getProtected($mpesa, 'initiatorPassword'))->toBe('plain-password');
    });
});
