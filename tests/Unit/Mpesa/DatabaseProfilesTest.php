<?php

use Itsmurumba\Mpesa\Mpesa;
use Itsmurumba\Mpesa\MpesaManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

describe('Database Profiles', function () {
    beforeEach(function () {
        if (! DB::getSchemaBuilder()->hasTable('mpesa_profiles')) {
            (require __DIR__.'/../../../database/migrations/2025_01_28_000001_create_mpesa_profiles_table.php')->up();
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
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = darajaRequests()[0]->data();
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
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = darajaRequests()[0]->data();
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
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = darajaRequests()[0]->data();
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
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = darajaRequests()[0]->data();
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
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = darajaRequests()[0]->data();
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
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = darajaRequests()[0]->data();
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
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->expressPayment(100, '254700000000');

        $payload = darajaRequests()[0]->data();
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

    it('keeps root defaults for fields a database profile leaves empty', function () {
        DB::table('mpesa_profiles')->insert(dbProfile('tenant_partial', ['lipa_na_mpesa_callback_url' => null]));

        (new Mpesa('tenant_partial'))->expressPayment(100, '254700000000');

        expect(darajaRequests()[0]->data()['CallBackURL'])->toBe('https://example.test/stk-callback');
    });

    it('decrypts the consumer secret and passkey stored encrypted', function () {
        DB::table('mpesa_profiles')->insert(dbProfile('tenant_encrypted', [
            'consumer_secret' => Crypt::encryptString('db-secret'),
            'lipa_na_mpesa_passkey' => Crypt::encryptString('db-passkey'),
        ]));

        (new Mpesa('tenant_encrypted'))->expressPayment(100, '254700000000');

        $requests = Http::recorded()->map(fn ($pair) => $pair[0])->values();
        expect($requests[0]->header('Authorization')[0])->toBe('Basic '.base64_encode('db-key:db-secret'));
        $payload = $requests[1]->data();
        expect($payload['Password'])->toBe(base64_encode('999999db-passkey'.$payload['Timestamp']));
    });

    it('reads the Buy Goods settings and security credential from the database profile', function () {
        config()->set('mpesa.securityCredential', 'root-credential');
        DB::table('mpesa_profiles')->insert(dbProfile('tenant_till', [
            'lipa_na_mpesa_transaction_type' => 'CustomerBuyGoodsOnline',
            'lipa_na_mpesa_till_number' => '5123456',
            'initiator_username' => 'till-operator',
            'security_credential' => Crypt::encryptString('till-credential'),
        ]));

        $mpesa = new Mpesa('tenant_till');
        $mpesa->expressPayment(100, '254700000000');
        $mpesa->reversals('QKA1B2C3D4', 100, '600000', '11', 'Refund');

        $express = darajaRequests()[0]->data();
        expect($express['TransactionType'])->toBe('CustomerBuyGoodsOnline');
        expect($express['PartyB'])->toBe('5123456');
        expect(darajaRequests()[1]->data()['SecurityCredential'])->toBe('till-credential');
    });

    it('surfaces database errors instead of falling back to other credentials', function () {
        $connection = config('database.default');
        config()->set('database.connections.unreachable', ['driver' => 'sqlite', 'database' => '/missing/mpesa.sqlite']);
        config()->set('database.default', 'unreachable');

        expect(fn () => new Mpesa('tenant_db'))->toThrow(QueryException::class);

        config()->set('database.default', $connection);
    });

    it('turns database profiles on from a string env value', function () {
        config()->set('mpesa.use_database', '1');
        DB::table('mpesa_profiles')->insert(dbProfile('tenant_env'));

        (new Mpesa('tenant_env'))->expressPayment(100, '254700000000');

        expect(darajaRequests()[0]->data()['BusinessShortCode'])->toBe('999999');
    });
});

/**
 * A row for the `mpesa_profiles` table with working defaults.
 */
function dbProfile(string $name, array $overrides = []): array
{
    return array_merge([
        'name' => $name,
        'consumer_key' => 'db-key',
        'consumer_secret' => 'db-secret',
        'base_url' => 'https://example.test',
        'lipa_na_mpesa_shortcode' => '999999',
        'lipa_na_mpesa_passkey' => 'db-passkey',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides);
}
