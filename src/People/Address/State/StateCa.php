<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\People\Address\State;

use Dogma\Str;

class StateCa extends \Dogma\Enum\StringEnum implements \Dogma\People\Address\State\State
{

    public const ONTARIO = 'ON';
    public const QUEBEC = 'QC';
    public const NOVA_SCOTIA = 'NS';
    public const NEW_BRUNSWICK = 'NB';
    public const MANITOBA = 'MB';
    public const BRITISH_COLUMBIA = 'BC';
    public const PRINCE_EDWARD_ISLAND = 'PE';
    public const SASKATCHEWAN = 'SK';
    public const ALBERTA = 'AB';
    public const NEWFOUNDLAND_AND_LABRADOR = 'NL';

    /** @var string[] */
    private static $names = [
        self::ONTARIO => 'Ontario',
        self::QUEBEC => 'Quebec',
        self::NOVA_SCOTIA => 'Nova Scotia',
        self::NEW_BRUNSWICK => 'New Brunswick',
        self::MANITOBA => 'Manitoba',
        self::BRITISH_COLUMBIA => 'British Columbia',
        self::PRINCE_EDWARD_ISLAND => 'Prince Edward Island',
        self::SASKATCHEWAN => 'Saskatchewan',
        self::ALBERTA => 'Alberta',
        self::NEWFOUNDLAND_AND_LABRADOR => 'Newfoundland and Labrador',
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

    public function getIdent(): string
    {
        return Str::webalize($this->getName());
    }

    public static function validateValue(string &$value): bool
    {
        $value = strtoupper($value);

        return parent::validateValue($value);
    }

}
