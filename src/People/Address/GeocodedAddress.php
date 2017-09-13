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
use Dogma\Geolocation\Position;
use Dogma\People\Address\State\State;

class GeocodedAddress extends \Dogma\People\Address\StreetAddress
{

    /** @var \Dogma\Geolocation\Position */
    private $position;

    public function __construct(
        Position $position,
        Country $country,
        string $city,
        string $street,
        ?string $zipCode = null,
        ?string $cityPart = null,
        ?string $district = null,
        ?string $region = null,
        ?State $state = null
    )
    {
        parent::__construct($country, $city, $street, $zipCode, $cityPart, $district, $region, $state);

        $this->position = $position;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

}
