<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Geolocation;

use Dogma\Check;
use Dogma\Math\Vector\Vector3;

/**
 * http://www.movable-type.co.uk/scripts/latlong.html
 */
class Position implements \Dogma\NonIterable, \Dogma\Mapping\Type\Exportable
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;

    public const PLANET_EARTH_RADIUS = 6371000.0;

    /** @var float [m] */
    private $planetRadius;

    /** @var float [degrees] */
    private $latitude;

    /** @var float [degrees] */
    private $longitude;

    /** @var float[] */
    private $normalVector;

    public function __construct(float $latitude, float $longitude, float $planetRadius = self::PLANET_EARTH_RADIUS)
    {
        Check::range($latitude, -90.0, 90.0);
        Check::range($longitude, -180.0, 180.0);
        Check::nullableFloat($planetRadius, 0.0);

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->planetRadius = $planetRadius;
    }

    public static function fromRadians(float $latRad, float $lonRad, ?float $planetRadius = null): self
    {
        Check::range($latRad, -M_PI_2, M_PI_2);
        Check::range($lonRad, -M_PI, M_PI);

        $position = new static(rad2deg($latRad), rad2deg($lonRad), $planetRadius);

        return $position;
    }

    public static function fromNormalVector(float $x, float $y, float $z, ?float $planetRadius = null): self
    {
        Check::range($x, 0.0, 1.0);
        Check::range($y, 0.0, 1.0);
        Check::range($z, 0.0, 1.0);

        list($latRad, $lonRad) = Vector3::normalVectorToRadians($x, $y, $z);

        $position = new static(rad2deg($latRad), rad2deg($lonRad), $planetRadius);
        $position->normalVector = [$x, $y, $z];

        return $position;
    }

    /**
     * @return float[] array('latitude' => $latitude, 'longitude' => $longitude)
     */
    public function export(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getPlanetRadius(): float
    {
        return $this->planetRadius;
    }

    /**
     * @return float[]
     */
    public function getNormalVector(): array
    {
        if ($this->normalVector === null) {
            $this->normalVector = Vector3::radiansToNormalVector(deg2rad($this->latitude), deg2rad($this->longitude));
        }
        return $this->normalVector;
    }

}
