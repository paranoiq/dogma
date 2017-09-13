<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\People\Address;

use Dogma\Country\Country;
use Dogma\People\Address\State\State;

/**
 * Street address without a person, company or flat information
 */
class StreetAddress
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Country\Country */
    private $country;

    /** @var \Dogma\People\Address\State\State|null */
    private $state;

    /** @var string|null */
    private $region;

    /** @var string|null */
    private $district;

    /** @var string */
    private $city;

    /** @var string|null */
    private $cityPart;

    /** @var string */
    private $street;

    /** @var string|null */
    private $zipCode;

    public function __construct(
        Country $country,
        string $city,
        string $street,
        ?string $zipCode = null,
        ?string $cityPart = null,
        ?string $district = null,
        ?string $region = null,
        ?State $state = null
    ) {
        $this->country = $country;
        $this->state = $state;
        $this->region = $region;
        $this->district = $district;
        $this->city = $city;
        $this->cityPart = $cityPart;
        $this->street = $street;
        $this->zipCode = $zipCode;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCityPart(): ?string
    {
        return $this->cityPart;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

}
