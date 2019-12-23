<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Country;

use Dogma\Enum\StringEnum;
use function array_search;
use function strtoupper;

/**
 * State codes by ISO 3166-2
 */
class StateCa extends StringEnum
{

    public const ALBERTA = 'AB';
    public const BRITISH_COLUMBIA = 'BC';
    public const MANITOBA = 'MB';
    public const NEW_BRUNSWICK = 'NB';
    public const NEWFOUNDLAND_AND_LABRADOR = 'NL';
    public const NORTHWEST_TERRITORIES = 'NT';
    public const NOVA_SCOTIA = 'NS';
    public const NUNAVUT = 'NU';
    public const ONTARIO = 'ON';
    public const PRINCE_EDWARD_ISLAND = 'PE';
    public const QUEBEC = 'QC';
    public const SASKATCHEWAN = 'SK';
    public const YUKON = 'YT';

    /** @var string[] */
    private static $names = [
        self::ALBERTA => 'Alberta',
        self::BRITISH_COLUMBIA => 'British Columbia',
        self::MANITOBA => 'Manitoba',
        self::ONTARIO => 'Ontario',
        self::PRINCE_EDWARD_ISLAND => 'Prince Edward Island',
        self::QUEBEC => 'Quebec',
        self::NEW_BRUNSWICK => 'New Brunswick',
        self::NEWFOUNDLAND_AND_LABRADOR => 'Newfoundland and Labrador',
        self::NORTHWEST_TERRITORIES => 'Northwest Territories',
        self::NOVA_SCOTIA => 'Nova Scotia',
        self::NUNAVUT => 'Nunavut',
        self::SASKATCHEWAN => 'Saskatchewan',
        self::YUKON => 'Yukon',
    ];

    /** @var string[] */
    private static $idents = [
        self::ALBERTA => 'alberta',
        self::BRITISH_COLUMBIA => 'british-columbia',
        self::MANITOBA => 'manitoba',
        self::ONTARIO => 'ontario',
        self::PRINCE_EDWARD_ISLAND => 'prince-edward-island',
        self::QUEBEC => 'quebec',
        self::NEW_BRUNSWICK => 'new-brunswick',
        self::NEWFOUNDLAND_AND_LABRADOR => 'newfoundland-and-labrador',
        self::NORTHWEST_TERRITORIES => 'northwest-territories',
        self::NOVA_SCOTIA => 'nova-scotia',
        self::NUNAVUT => 'nunavut',
        self::SASKATCHEWAN => 'saskatchewan',
        self::YUKON => 'yukon',
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

    public function getIdent(): string
    {
        return self::$idents[$this->getValue()];
    }

    public static function getByIdent(string $ident): self
    {
        return self::get(array_search($ident, self::$idents, true));
    }

    public static function validateValue(string &$value): bool
    {
        $value = strtoupper($value);

        return parent::validateValue($value);
    }

}
