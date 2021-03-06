<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Web;

use Dogma\Country\Country;
use Dogma\Enum\PartialStringEnum;
use function array_search;
use function strlen;

final class Tld extends PartialStringEnum
{

    // common TLD
    public const COM = 'com';
    public const ORG = 'org';
    public const NET = 'net';
    public const INT = 'int';
    public const EDU = 'edu';
    public const GOV = 'gov';
    public const MIL = 'mil';

    // country TLD
    public const AC = 'ac';
    public const AD = 'ad';
    public const AE = 'ae';
    public const AF = 'af';
    public const AG = 'ag';
    public const AI = 'ai';
    public const AL = 'al';
    public const AM = 'am';
    public const AN = 'an';
    public const AO = 'ao';
    public const AQ = 'aq';
    public const AR = 'ar';
    public const AS_TLD = 'as';
    public const AT = 'at';
    public const AU = 'au';
    public const AW = 'aw';
    public const AX = 'ax';
    public const AZ = 'az';
    public const BA = 'ba';
    public const BB = 'bb';
    public const BD = 'bd';
    public const BE = 'be';
    public const BF = 'bf';
    public const BG = 'bg';
    public const BH = 'bh';
    public const BI = 'bi';
    public const BJ = 'bj';
    public const BM = 'bm';
    public const BN = 'bn';
    public const BO = 'bo';
    public const BQ = 'bq';
    public const BR = 'br';
    public const BS = 'bs';
    public const BT = 'bt';
    public const BV = 'bv';
    public const BW = 'bw';
    public const BY = 'by';
    public const BZ = 'bz';
    public const BZH = 'bzh';
    public const CA = 'ca';
    public const CC = 'cc';
    public const CD = 'cd';
    public const CF = 'cf';
    public const CG = 'cg';
    public const CH = 'ch';
    public const CI = 'ci';
    public const CK = 'ck';
    public const CL = 'cl';
    public const CM = 'cm';
    public const CN = 'cn';
    public const CO = 'co';
    public const CR = 'cr';
    public const CS = 'cs';
    public const CU = 'cu';
    public const CV = 'cv';
    public const CW = 'cw';
    public const CX = 'cx';
    public const CY = 'cy';
    public const CZ = 'cz';
    public const DD = 'dd';
    public const DE = 'de';
    public const DJ = 'dj';
    public const DK = 'dk';
    public const DM = 'dm';
    public const DO_TLD = 'do';
    public const DZ = 'dz';
    public const EC = 'ec';
    public const EE = 'ee';
    public const EG = 'eg';
    public const EH = 'eh';
    public const ER = 'er';
    public const ES = 'es';
    public const ET = 'et';
    public const EU = 'eu';
    public const FI = 'fi';
    public const FJ = 'fj';
    public const FK = 'fk';
    public const FM = 'fm';
    public const FO = 'fo';
    public const FR = 'fr';
    public const GA = 'ga';
    public const GB = 'gb';
    public const GD = 'gd';
    public const GE = 'ge';
    public const GF = 'gf';
    public const GG = 'gg';
    public const GH = 'gh';
    public const GI = 'gi';
    public const GL = 'gl';
    public const GM = 'gm';
    public const GN = 'gn';
    public const GP = 'gp';
    public const GQ = 'gq';
    public const GR = 'gr';
    public const GS = 'gs';
    public const GT = 'gt';
    public const GU = 'gu';
    public const GW = 'gw';
    public const GY = 'gy';
    public const HK = 'hk';
    public const HM = 'hm';
    public const HN = 'hn';
    public const HR = 'hr';
    public const HT = 'ht';
    public const HU = 'hu';
    public const ID = 'id';
    public const IE = 'ie';
    public const IL = 'il';
    public const IM = 'im';
    public const IN = 'in';
    public const IO = 'io';
    public const IQ = 'iq';
    public const IR = 'ir';
    public const IS = 'is';
    public const IT = 'it';
    public const JE = 'je';
    public const JM = 'jm';
    public const JO = 'jo';
    public const JP = 'jp';
    public const KE = 'ke';
    public const KG = 'kg';
    public const KH = 'kh';
    public const KI = 'ki';
    public const KM = 'km';
    public const KN = 'kn';
    public const KP = 'kp';
    public const KR = 'kr';
    public const KRD = 'krd';
    public const KW = 'kw';
    public const KY = 'ky';
    public const KZ = 'kz';
    public const LA = 'la';
    public const LB = 'lb';
    public const LC = 'lc';
    public const LI = 'li';
    public const LK = 'lk';
    public const LR = 'lr';
    public const LS = 'ls';
    public const LT = 'lt';
    public const LU = 'lu';
    public const LV = 'lv';
    public const LY = 'ly';
    public const MA = 'ma';
    public const MC = 'mc';
    public const MD = 'md';
    public const ME = 'me';
    public const MG = 'mg';
    public const MH = 'mh';
    public const MK = 'mk';
    public const ML = 'ml';
    public const MM = 'mm';
    public const MN = 'mn';
    public const MO = 'mo';
    public const MP = 'mp';
    public const MQ = 'mq';
    public const MR = 'mr';
    public const MS = 'ms';
    public const MT = 'mt';
    public const MU = 'mu';
    public const MV = 'mv';
    public const MW = 'mw';
    public const MX = 'mx';
    public const MY = 'my';
    public const MZ = 'mz';
    public const NA = 'na';
    public const NC = 'nc';
    public const NE = 'ne';
    public const NF = 'nf';
    public const NG = 'ng';
    public const NI = 'ni';
    public const NL = 'nl';
    public const NO = 'no';
    public const NP = 'np';
    public const NR = 'nr';
    public const NU = 'nu';
    public const NZ = 'nz';
    public const OM = 'om';
    public const PA = 'pa';
    public const PE = 'pe';
    public const PF = 'pf';
    public const PG = 'pg';
    public const PH = 'ph';
    public const PK = 'pk';
    public const PL = 'pl';
    public const PM = 'pm';
    public const PN = 'pn';
    public const PR = 'pr';
    public const PS = 'ps';
    public const PT = 'pt';
    public const PW = 'pw';
    public const PY = 'py';
    public const QA = 'qa';
    public const RE = 're';
    public const RO = 'ro';
    public const RS = 'rs';
    public const RU = 'ru';
    public const RW = 'rw';
    public const SA = 'sa';
    public const SB = 'sb';
    public const SC = 'sc';
    public const SD = 'sd';
    public const SE = 'se';
    public const SG = 'sg';
    public const SH = 'sh';
    public const SI = 'si';
    public const SJ = 'sj';
    public const SK = 'sk';
    public const SL = 'sl';
    public const SM = 'sm';
    public const SN = 'sn';
    public const SO = 'so';
    public const SR = 'sr';
    public const SS = 'ss';
    public const ST = 'st';
    public const SU = 'su';
    public const SV = 'sv';
    public const SX = 'sx';
    public const SY = 'sy';
    public const SZ = 'sz';
    public const TC = 'tc';
    public const TD = 'td';
    public const TF = 'tf';
    public const TG = 'tg';
    public const TH = 'th';
    public const TJ = 'tj';
    public const TK = 'tk';
    public const TL = 'tl';
    public const TM = 'tm';
    public const TN = 'tn';
    public const TO = 'to';
    public const TP = 'tp';
    public const TR = 'tr';
    public const TT = 'tt';
    public const TV = 'tv';
    public const TW = 'tw';
    public const TZ = 'tz';
    public const UA = 'ua';
    public const UG = 'ug';
    public const UK = 'uk';
    public const US = 'us';
    public const UY = 'uy';
    public const UZ = 'uz';
    public const VA = 'va';
    public const VC = 'vc';
    public const VE = 've';
    public const VG = 'vg';
    public const VI = 'vi';
    public const VN = 'vn';
    public const VU = 'vu';
    public const WF = 'wf';
    public const WS = 'ws';
    public const YE = 'ye';
    public const YT = 'yt';
    public const YU = 'yu';
    public const ZA = 'za';
    public const ZM = 'zm';
    public const ZR = 'zr';
    public const ZW = 'zw';

    /** @var string[] */
    private static $countryMap = [
        self::AC => Country::SAINT_HELENA,
        self::AD => Country::ANDORRA,
        self::AE => Country::UNITED_ARAB_EMIRATES,
        self::AF => Country::AFGHANISTAN,
        self::AG => Country::ANTIGUA_AND_BARBUDA,
        self::AI => Country::ANGUILLA,
        self::AL => Country::ALBANIA,
        self::AM => Country::ARMENIA,
        self::AN => Country::NETHERLANDS_ANTILLES,
        self::AO => Country::ANGOLA,
        self::AQ => Country::ANTARCTICA,
        self::AR => Country::ARGENTINA,
        self::AS_TLD => Country::AMERICAN_SAMOA,
        self::AT => Country::AUSTRIA,
        self::AU => Country::AUSTRALIA,
        self::AW => Country::ARUBA,
        self::AX => Country::ALAND_ISLANDS,
        self::AZ => Country::AZERBAIJAN,
        self::BA => Country::BOSNIA_AND_HERZEGOVINA,
        self::BB => Country::BARBADOS,
        self::BD => Country::BANGLADESH,
        self::BE => Country::BELGIUM,
        self::BF => Country::BURKINA_FASO,
        self::BG => Country::BULGARIA,
        self::BH => Country::BAHRAIN,
        self::BI => Country::BURUNDI,
        self::BJ => Country::BENIN,
        self::BM => Country::BERMUDA,
        self::BN => Country::BRUNEI_DARUSSALAM,
        self::BO => Country::BOLIVIA,
        self::BQ => Country::NETHERLANDS,
        self::BR => Country::BRAZIL,
        self::BS => Country::BAHAMAS,
        self::BT => Country::BHUTAN,
        self::BV => Country::BOUVET_ISLAND,
        self::BW => Country::BOTSWANA,
        self::BY => Country::BELARUS,
        self::BZ => Country::BELIZE,
        self::BZH => Country::FRANCE,
        self::CA => Country::CANADA,
        self::CC => Country::COCOS_ISLANDS,
        self::CD => Country::DEMOCRATIC_REPUBLIC_OF_THE_CONGO,
        self::CF => Country::CENTRAL_AFRICAN_REPUBLIC,
        self::CG => Country::CONGO,
        self::CH => Country::SWITZERLAND,
        self::CI => Country::COTE_D_IVOIRE,
        self::CK => Country::COOK_ISLANDS,
        self::CL => Country::CHILE,
        self::CM => Country::CAMEROON,
        self::CN => Country::CHINA,
        self::CO => Country::COLOMBIA,
        self::CR => Country::COSTA_RICA,
        self::CU => Country::CUBA,
        self::CV => Country::CAPE_VERDE,
        self::CW => Country::NETHERLANDS,
        self::CX => Country::CHRISTMAS_ISLAND,
        self::CY => Country::CYPRUS,
        self::CZ => Country::CZECHIA,
        self::DD => Country::GERMANY,
        self::DE => Country::GERMANY,
        self::DJ => Country::DJIBOUTI,
        self::DK => Country::DENMARK,
        self::DM => Country::DOMINICA,
        self::DO_TLD => Country::DOMINICAN_REPUBLIC,
        self::DZ => Country::ALGERIA,
        self::EC => Country::ECUADOR,
        self::EE => Country::ESTONIA,
        self::EG => Country::EGYPT,
        self::EH => Country::WESTERN_SAHARA,
        self::ER => Country::ERITREA,
        self::ES => Country::SPAIN,
        self::ET => Country::ETHIOPIA,
        self::FI => Country::FINLAND,
        self::FJ => Country::FIJI,
        self::FK => Country::FALKLAND_ISLANDS,
        self::FM => Country::MICRONESIA,
        self::FO => Country::FAROE_ISLANDS,
        self::FR => Country::FRANCE,
        self::GA => Country::GABON,
        self::GB => Country::UNITED_KINGDOM,
        self::GD => Country::GRENADA,
        self::GE => Country::GEORGIA,
        self::GF => Country::FRENCH_GUIANA,
        self::GG => Country::GUERNSEY,
        self::GH => Country::GHANA,
        self::GI => Country::GIBRALTAR,
        self::GL => Country::GREENLAND,
        self::GM => Country::GAMBIA,
        self::GN => Country::GUINEA,
        self::GP => Country::GUADELOUPE,
        self::GQ => Country::EQUATORIAL_GUINEA,
        self::GR => Country::GREECE,
        self::GS => Country::SOUTH_GEORGIA_AND_THE_SOUTH_SANDWICH,
        self::GT => Country::GUATEMALA,
        self::GU => Country::GUAM,
        self::GW => Country::GUINEA_BISSAU,
        self::GY => Country::GUYANA,
        self::HK => Country::HONG_KONG,
        self::HM => Country::HEARD_ISLAND_AND_MCDONALD_ISLANDS,
        self::HN => Country::HONDURAS,
        self::HR => Country::CROATIA,
        self::HT => Country::HAITI,
        self::HU => Country::HUNGARY,
        self::ID => Country::INDONESIA,
        self::IE => Country::IRELAND,
        self::IL => Country::ISRAEL,
        self::IM => Country::ISLE_OF_MAN,
        self::IN => Country::INDIA,
        self::IO => Country::BRITISH_INDIAN_OCEAN_TERRITORY,
        self::IQ => Country::IRAQ,
        self::IR => Country::ISLAMIC_REPUBLIC_OF_IRAN,
        self::IS => Country::ICELAND,
        self::IT => Country::ITALY,
        self::JE => Country::JERSEY,
        self::JM => Country::JAMAICA,
        self::JO => Country::JORDAN,
        self::JP => Country::JAPAN,
        self::KE => Country::KENYA,
        self::KG => Country::KYRGYZSTAN,
        self::KH => Country::CAMBODIA,
        self::KI => Country::KIRIBATI,
        self::KM => Country::COMOROS,
        self::KN => Country::SAINT_KITTS_AND_NEVIS,
        self::KP => Country::NORTH_KOREA,
        self::KR => Country::SOUTH_KOREA,
        self::KW => Country::KUWAIT,
        self::KY => Country::CAYMAN_ISLANDS,
        self::KZ => Country::KAZAKHSTAN,
        self::LA => Country::LAOS,
        self::LB => Country::LEBANON,
        self::LC => Country::SAINT_LUCIA,
        self::LI => Country::LIECHTENSTEIN,
        self::LK => Country::SRI_LANKA,
        self::LR => Country::LIBERIA,
        self::LS => Country::LESOTHO,
        self::LT => Country::LITHUANIA,
        self::LU => Country::LUXEMBOURG,
        self::LV => Country::LATVIA,
        self::LY => Country::LIBYA,
        self::MA => Country::MOROCCO,
        self::MC => Country::MONACO,
        self::MD => Country::MOLDOVA,
        self::ME => Country::MONTENEGRO,
        self::MG => Country::MADAGASCAR,
        self::MH => Country::MARSHALL_ISLANDS,
        self::MK => Country::MACEDONIA,
        self::ML => Country::MALI,
        self::MM => Country::MYANMAR,
        self::MN => Country::MONGOLIA,
        self::MO => Country::MACAO,
        self::MP => Country::NORTHERN_MARIANA_ISLANDS,
        self::MQ => Country::MARTINIQUE,
        self::MR => Country::MAURITANIA,
        self::MS => Country::MONTSERRAT,
        self::MT => Country::MALTA,
        self::MU => Country::MAURITIUS,
        self::MV => Country::MALDIVES,
        self::MW => Country::MALAWI,
        self::MX => Country::MEXICO,
        self::MY => Country::MALAYSIA,
        self::MZ => Country::MOZAMBIQUE,
        self::NA => Country::NAMIBIA,
        self::NC => Country::NEW_CALEDONIA,
        self::NE => Country::NIGER,
        self::NF => Country::NORFOLK_ISLAND,
        self::NG => Country::NIGERIA,
        self::NI => Country::NICARAGUA,
        self::NL => Country::NETHERLANDS,
        self::NO => Country::NORWAY,
        self::NP => Country::NEPAL,
        self::NR => Country::NAURU,
        self::NU => Country::NIUE,
        self::NZ => Country::NEW_ZEALAND,
        self::OM => Country::OMAN,
        self::PA => Country::PANAMA,
        self::PE => Country::PERU,
        self::PF => Country::FRENCH_POLYNESIA,
        self::PG => Country::PAPUA_NEW_GUINEA,
        self::PH => Country::PHILIPPINES,
        self::PK => Country::PAKISTAN,
        self::PL => Country::POLAND,
        self::PM => Country::SAINT_PIERRE_AND_MIQUELON,
        self::PN => Country::PITCAIRN,
        self::PR => Country::PUERTO_RICO,
        self::PS => Country::PALESTINE,
        self::PT => Country::PORTUGAL,
        self::PW => Country::PALAU,
        self::PY => Country::PARAGUAY,
        self::QA => Country::QATAR,
        self::RE => Country::REUNION,
        self::RO => Country::ROMANIA,
        self::RS => Country::SERBIA,
        self::RU => Country::RUSSIA,
        self::RW => Country::RWANDA,
        self::SA => Country::SAUDI_ARABIA,
        self::SB => Country::SOLOMON_ISLANDS,
        self::SC => Country::SEYCHELLES,
        self::SD => Country::SUDAN,
        self::SE => Country::SWEDEN,
        self::SG => Country::SINGAPORE,
        self::SH => Country::SAINT_HELENA,
        self::SI => Country::SLOVENIA,
        self::SJ => Country::SVALBARD_AND_JAN_MAYEN,
        self::SK => Country::SLOVAKIA,
        self::SL => Country::SIERRA_LEONE,
        self::SM => Country::SAN_MARINO,
        self::SN => Country::SENEGAL,
        self::SO => Country::SOMALIA,
        self::SR => Country::SURINAME,
        self::SS => Country::SOUTH_SUDAN,
        self::ST => Country::SAO_TOME_AND_PRINCIPE,
        self::SV => Country::EL_SALVADOR,
        self::SX => Country::NETHERLANDS,
        self::SY => Country::SYRIA,
        self::SZ => Country::SWAZILAND,
        self::TC => Country::TURKS_AND_CAICOS_ISLANDS,
        self::TD => Country::CHAD,
        self::TF => Country::FRENCH_SOUTHERN_TERRITORIES,
        self::TG => Country::TOGO,
        self::TH => Country::THAILAND,
        self::TJ => Country::TAJIKISTAN,
        self::TK => Country::TOKELAU,
        self::TL => Country::TIMOR_LESTE,
        self::TM => Country::TURKMENISTAN,
        self::TN => Country::TUNISIA,
        self::TO => Country::TONGA,
        self::TP => Country::TIMOR_LESTE,
        self::TR => Country::TURKEY,
        self::TT => Country::TRINIDAD_AND_TOBAGO,
        self::TV => Country::TUVALU,
        self::TW => Country::TAIWAN,
        self::TZ => Country::TANZANIA,
        self::UA => Country::UKRAINE,
        self::UG => Country::UGANDA,
        self::UK => Country::UNITED_KINGDOM,
        self::US => Country::UNITED_STATES,
        self::UY => Country::URUGUAY,
        self::UZ => Country::UZBEKISTAN,
        self::VA => Country::VATICAN,
        self::VC => Country::SAINT_VINCENT_AND_THE_GRENADINES,
        self::VE => Country::VENEZUELA,
        self::VG => Country::VIRGIN_ISLANDS_BRITISH,
        self::VI => Country::VIRGIN_ISLANDS_US,
        self::VN => Country::VIETNAM,
        self::VU => Country::VANUATU,
        self::WF => Country::WALLIS_AND_FUTUNA,
        self::WS => Country::SAMOA,
        self::YE => Country::YEMEN,
        self::YT => Country::MAYOTTE,
        self::ZA => Country::SOUTH_AFRICA,
        self::ZM => Country::ZAMBIA,
        self::ZR => Country::DEMOCRATIC_REPUBLIC_OF_THE_CONGO,
        self::ZW => Country::ZIMBABWE,
    ];

    public function isCountryTld(): bool
    {
        return strlen($this->getValue()) === 2 || $this->equalsValue(self::BZH);
    }

    public function getCountry(): ?Country
    {
        $value = $this->getValue();
        if (isset(self::$countryMap[$value])) {
            return Country::get(self::$countryMap[$value]);
        }
        return null;
    }

    public static function getByCountry(Country $country): self
    {
        /** @var string $domain */
        $domain = array_search($country->getValue(), self::$countryMap, true);

        return self::get($domain);
    }

    public static function getValueRegexp(): string
    {
        return '^([a-z]{2,}|xn--[0-9a-z]{4,})$';
    }

}
