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

class StateUs extends \Dogma\Enum\StringEnum implements \Dogma\People\Address\State\State
{

    public const ALABAMA = 'AL';
    public const ALASKA = 'AK';
    public const ARIZONA = 'AZ';
    public const ARKANSAS = 'AR';
    public const CALIFORNIA = 'CA';
    public const COLORADO = 'CO';
    public const CONNECTICUT = 'CT';
    public const DELAWARE = 'DE';
    public const FLORIDA = 'FL';
    public const GEORGIA = 'GA';
    public const HAWAII = 'HI';
    public const IDAHO = 'ID';
    public const ILLINOIS = 'IL';
    public const INDIANA = 'IN';
    public const IOWA = 'IA';
    public const KANSAS = 'KS';
    public const KENTUCKY = 'KY';
    public const LOUISIANA = 'LA';
    public const MAINE = 'ME';
    public const MARYLAND = 'MD';
    public const MASSACHUSETTS = 'MA';
    public const MICHIGAN = 'MI';
    public const MINNESOTA = 'MN';
    public const MISSISSIPPI = 'MS';
    public const MISSOURI = 'MO';
    public const MONTANA = 'MT';
    public const NEBRASKA = 'NE';
    public const NEVADA = 'NV';
    public const NEW_HAMPSHIRE = 'NH';
    public const NEW_JERSEY = 'NJ';
    public const NEW_MEXICO = 'NM';
    public const NEW_YORK = 'NY';
    public const NORTH_CAROLINA = 'NC';
    public const NORTH_DAKOTA = 'ND';
    public const OHIO = 'OH';
    public const OKLAHOMA = 'OK';
    public const OREGON = 'OR';
    public const PENNSYLVANIA = 'PA';
    public const RHODE_ISLAND = 'RI';
    public const SOUTH_CAROLINA = 'SC';
    public const SOUTH_DAKOTA = 'SD';
    public const TENNESSEE = 'TN';
    public const TEXAS = 'TX';
    public const UTAH = 'UT';
    public const VERMONT = 'VT';
    public const VIRGINIA = 'VA';
    public const WASHINGTON = 'WA';
    public const WEST_VIRGINIA = 'WV';
    public const WISCONSIN = 'WI';
    public const WYOMING = 'WY';

    /** @var string[] */
    private static $names = [
        self::ALABAMA => 'Alabama',
        self::ALASKA => 'Alaska',
        self::ARIZONA => 'Arizona',
        self::ARKANSAS => 'Arkansas',
        self::CALIFORNIA => 'California',
        self::COLORADO => 'Colorado',
        self::CONNECTICUT => 'Connecticut',
        self::DELAWARE => 'Delaware',
        self::FLORIDA => 'Florida',
        self::GEORGIA => 'Georgia',
        self::HAWAII => 'Hawaii',
        self::IDAHO => 'Idaho',
        self::ILLINOIS => 'Illinois',
        self::INDIANA => 'Indiana',
        self::IOWA => 'Iowa',
        self::KANSAS => 'Kansas',
        self::KENTUCKY => 'Kentucky',
        self::LOUISIANA => 'Louisiana',
        self::MAINE => 'Maine',
        self::MARYLAND => 'Maryland',
        self::MASSACHUSETTS => 'Massachusetts',
        self::MICHIGAN => 'Michigan',
        self::MINNESOTA => 'Minnesota',
        self::MISSISSIPPI => 'Mississippi',
        self::MISSOURI => 'Missouri',
        self::MONTANA => 'Montana',
        self::NEBRASKA => 'Nebraska',
        self::NEVADA => 'Nevada',
        self::NEW_HAMPSHIRE => 'New Hampshire',
        self::NEW_JERSEY => 'New Jersey',
        self::NEW_MEXICO => 'New Mexico',
        self::NEW_YORK => 'New York',
        self::NORTH_CAROLINA => 'North Carolina',
        self::NORTH_DAKOTA => 'North Dakota',
        self::OHIO => 'Ohio',
        self::OKLAHOMA => 'Oklahoma',
        self::OREGON => 'Oregon',
        self::PENNSYLVANIA => 'Pennsylvania',
        self::RHODE_ISLAND => 'Rhode Island',
        self::SOUTH_CAROLINA => 'South Carolina',
        self::SOUTH_DAKOTA => 'South Dakota',
        self::TENNESSEE => 'Tennessee',
        self::TEXAS => 'Texas',
        self::UTAH => 'Utah',
        self::VERMONT => 'Vermont',
        self::VIRGINIA => 'Virginia',
        self::WASHINGTON => 'Washington',
        self::WEST_VIRGINIA => 'West Virginia',
        self::WISCONSIN => 'Wisconsin',
        self::WYOMING => 'Wyoming',
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
