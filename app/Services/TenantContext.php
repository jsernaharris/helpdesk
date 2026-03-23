<?php

namespace App\Services;

use App\Models\Organization;

class TenantContext
{
    private ?Organization $organization = null;

    public function set(Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function get(): ?Organization
    {
        return $this->organization;
    }

    public function id(): ?int
    {
        return $this->organization?->id;
    }

    public function isMsp(): bool
    {
        return $this->organization?->is_msp ?? false;
    }

    public function clear(): void
    {
        $this->organization = null;
    }
}
