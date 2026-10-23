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
     * Cached default instance (single mode).
     */
    protected $default;

    /**
     * Get the default profile Mpesa client.
     *
     * @return Mpesa
     */
    public function defaultInstance()
    {
        if ($this->default instanceof Mpesa) {
            return $this->default;
        }

        $this->default = new Mpesa();

        return $this->default;
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

    /**
     * Backwards compatibility: forward unknown calls to the default instance.
     *
     * This allows `Mpesa::expressPayment(...)` to keep working even though the
     * facade now resolves to the manager (`mpesa`) to support `for()`.
     */
    public function __call($method, $arguments)
    {
        return $this->defaultInstance()->{$method}(...$arguments);
    }
}

