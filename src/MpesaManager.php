<?php

namespace Itsmurumba\Mpesa;

/**
 * Resolve Mpesa clients for different profiles (tenants/customers).
 *
 * Usage:
 *  - app('mpesa')->for('tenant-a')->expressPayment(...)
 *  - Mpesa::for('tenant-a')->expressPayment(...) // Facade
 */
class MpesaManager
{
    /**
     * Get the default profile Mpesa client.
     *
     * @return Mpesa
     */
    public function defaultInstance()
    {
        return new Mpesa();
    }

    /**
     * Resolve an Mpesa client for a given profile key.
     *
     * @param  string  $profile
     * @param  array  $overrides
     * @return Mpesa
     */
    public function for($profile, array $overrides = [])
    {
        return new Mpesa($profile, $overrides);
    }
}

