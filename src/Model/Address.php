<?php

namespace Fyennyi\Nominatim\Model;

class Address
{
    private array $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function getCountry(): ?string
    {
        return $this->details['country'] ?? null;
    }

    public function getCountryCode(): ?string
    {
        return $this->details['country_code'] ?? null;
    }

    public function getState(): ?string
    {
        return $this->details['state'] ?? null;
    }

    public function getCity(): ?string
    {
        return $this->details['city'] ?? $this->details['town'] ?? $this->details['village'] ?? null;
    }

    public function getDistrict(): ?string
    {
        return $this->details['district'] ?? $this->details['county'] ?? null;
    }

    public function toArray(): array
    {
        return $this->details;
    }
}
