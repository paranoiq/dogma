<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\People\Address\Region;

class RegionCz extends \Dogma\Enum\IntEnum implements \Dogma\People\Address\Region\Region
{

    public const PRAGUE = 1;
    public const CENTRAL_BOHEMIA = 2;
    public const SOUTH_BOHEMIA = 3;
    public const PLZEN = 4;
    public const KARLOVY_VARY = 5;
    public const USTI_NAD_LABEM = 6;
    public const LIBEREC = 7;
    public const HRADEC_KRALOVE = 8;
    public const PARDUBICE = 9;
    public const HIGHLANDS = 10;
    public const SOUTH_MORAVIA = 11;
    public const OLOMOUC = 12;
    public const MORAVIA_SILESIA = 13;
    public const ZLIN = 14;

    /** @var string[] */
    private static $shortcuts = [
        self::PRAGUE => 'A',
        self::CENTRAL_BOHEMIA => 'S',
        self::SOUTH_BOHEMIA => 'C',
        self::PLZEN => 'P',
        self::KARLOVY_VARY => 'K',
        self::USTI_NAD_LABEM => 'U',
        self::LIBEREC => 'L',
        self::HRADEC_KRALOVE => 'H',
        self::PARDUBICE => 'E',
        self::HIGHLANDS => 'J',
        self::SOUTH_MORAVIA => 'B',
        self::OLOMOUC => 'M',
        self::MORAVIA_SILESIA => 'T',
        self::ZLIN => 'Z',
    ];

    public function getShortcut(): string
    {
        return self::$shortcuts[$this->getValue()];
    }

    /** @var string[] */
    private static $shortcuts3 = [
        self::PRAGUE => 'PHA',
        self::CENTRAL_BOHEMIA => 'STČ',
        self::SOUTH_BOHEMIA => 'JHČ',
        self::PLZEN => 'PLK',
        self::KARLOVY_VARY => 'KVK',
        self::USTI_NAD_LABEM => 'ULK',
        self::LIBEREC => 'LBK',
        self::HRADEC_KRALOVE => 'HKK',
        self::PARDUBICE => 'PAK',
        self::HIGHLANDS => 'VYS',
        self::SOUTH_MORAVIA => 'JHM',
        self::OLOMOUC => 'OLK',
        self::MORAVIA_SILESIA => 'MSK',
        self::ZLIN => 'ZLK',
    ];

    public function getShortcut3(): string
    {
        return self::$shortcuts3[$this->getValue()];
    }

    /** @var string[] */
    private static $names = [
        self::PRAGUE => 'Hlavní město Praha',
        self::CENTRAL_BOHEMIA => 'Středočeský kraj',
        self::SOUTH_BOHEMIA => 'Jihočeský kraj',
        self::PLZEN => 'Plzeňský kraj',
        self::KARLOVY_VARY => 'Karlovarský kraj',
        self::USTI_NAD_LABEM => 'Ústecký kraj',
        self::LIBEREC => 'Liberecký kraj',
        self::HRADEC_KRALOVE => 'Královéhradecký kraj',
        self::PARDUBICE => 'Pardubický kraj',
        self::HIGHLANDS => 'Kraj Vysočina',
        self::SOUTH_MORAVIA => 'Jihomoravský kraj',
        self::OLOMOUC => 'Olomoucký kraj',
        self::MORAVIA_SILESIA => 'Moravskoslezský kraj',
        self::ZLIN => 'Zlínský kraj',
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

    /** @var string[] */
    private static $cities = [
        self::PRAGUE => 'Praha',
        self::CENTRAL_BOHEMIA => 'Praha',
        self::SOUTH_BOHEMIA => 'České Budějovice',
        self::PLZEN => 'Plzeň',
        self::KARLOVY_VARY => 'Karlovy Vary',
        self::USTI_NAD_LABEM => 'Ústí nad Labem',
        self::LIBEREC => 'Liberec',
        self::HRADEC_KRALOVE => 'Hradec Králové',
        self::PARDUBICE => 'Pardubice',
        self::HIGHLANDS => 'Jihlava',
        self::SOUTH_MORAVIA => 'Brno',
        self::OLOMOUC => 'Olomouc',
        self::MORAVIA_SILESIA => 'Ostrava',
        self::ZLIN => 'Zlín',
    ];

    public function getCity(): string
    {
        return self::$cities[$this->getValue()];
    }

}
