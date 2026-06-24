<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        app()->instance('current_tenant_id', $tenant->id);
        app()->instance('current_tenant', $tenant);
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }
}
