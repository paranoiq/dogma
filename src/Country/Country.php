<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Country;

/**
 * 2-letter country code by ISO-3166-1
 */
class Country extends \Dogma\EnumString
{

    public const AFGHANISTAN = 'AF';
    public const ALAND_ISLANDS = 'AX';
    public const ALBANIA = 'AL';
    public const ALGERIA = 'DZ';
    public const AMERICAN_SAMOA = 'AS';
    public const ANDORRA = 'AD';
    public const ANGOLA = 'AO';
    public const ANGUILLA = 'AI';
    public const ANTARCTICA = 'AQ';
    public const ANTIGUA_AND_BARBUDA = 'AG';
    public const ARGENTINA = 'AR';
    public const ARMENIA = 'AM';
    public const ARUBA = 'AW';
    public const AUSTRALIA = 'AU';
    public const AUSTRIA = 'AT';
    public const AZERBAIJAN = 'AZ';
    public const BAHAMAS = 'BS';
    public const BAHRAIN = 'BH';
    public const BANGLADESH = 'BD';
    public const BARBADOS = 'BB';
    public const BELARUS = 'BY';
    public const BELGIUM = 'BE';
    public const BELIZE = 'BZ';
    public const BENIN = 'BJ';
    public const BERMUDA = 'BM';
    public const BHUTAN = 'BT';
    public const BOLIVIA = 'BO';
    public const BOSNIA_AND_HERZEGOVINA = 'BA';
    public const BOTSWANA = 'BW';
    public const BOUVET_ISLAND = 'BV';
    public const BRAZIL = 'BR';
    public const BRITISH_INDIAN_OCEAN_TERRITORY = 'IO';
    public const BRUNEI_DARUSSALAM = 'BN';
    public const BULGARIA = 'BG';
    public const BURKINA_FASO = 'BF';
    public const BURUNDI = 'BI';
    public const CAMBODIA = 'KH';
    public const CAMEROON = 'CM';
    public const CANADA = 'CA';
    public const CAPE_VERDE = 'CV';
    public const CAYMAN_ISLANDS = 'KY';
    public const CENTRAL_AFRICAN_REPUBLIC = 'CF';
    public const CHAD = 'TD';
    public const CHILE = 'CL';
    public const CHINA = 'CN';
    public const CHRISTMAS_ISLAND = 'CX';
    public const COCOS_ISLANDS = 'CC';
    public const COLOMBIA = 'CO';
    public const COMOROS = 'KM';
    public const CONGO = 'CG';
    public const COOK_ISLANDS = 'CK';
    public const COSTA_RICA = 'CR';
    public const COTE_D_IVOIRE = 'CI';
    public const CROATIA = 'HR';
    public const CUBA = 'CU';
    public const CYPRUS = 'CY';
    public const CZECHIA = 'CZ';
    public const DEMOCRATIC_REPUBLIC_OF_THE_CONGO = 'CD';
    public const DENMARK = 'DK';
    public const DJIBOUTI = 'DJ';
    public const DOMINICA = 'DM';
    public const DOMINICAN_REPUBLIC = 'DO';
    public const ECUADOR = 'EC';
    public const EGYPT = 'EG';
    public const EL_SALVADOR = 'SV';
    public const EQUATORIAL_GUINEA = 'GQ';
    public const ERITREA = 'ER';
    public const ESTONIA = 'EE';
    public const ETHIOPIA = 'ET';
    public const EU = 'EU';
    public const FALKLAND_ISLANDS = 'FK';
    public const FAROE_ISLANDS = 'FO';
    public const FIJI = 'FJ';
    public const FINLAND = 'FI';
    public const FRANCE = 'FR';
    public const FRENCH_GUIANA = 'GF';
    public const FRENCH_POLYNESIA = 'PF';
    public const FRENCH_SOUTHERN_TERRITORIES = 'TF';
    public const GABON = 'GA';
    public const GAMBIA = 'GM';
    public const GEORGIA = 'GE';
    public const GERMANY = 'DE';
    public const GHANA = 'GH';
    public const GIBRALTAR = 'GI';
    public const GREECE = 'GR';
    public const GREENLAND = 'GL';
    public const GRENADA = 'GD';
    public const GUADELOUPE = 'GP';
    public const GUAM = 'GU';
    public const GUATEMALA = 'GT';
    public const GUERNSEY = 'GG';
    public const GUINEA = 'GN';
    public const GUINEA_BISSAU = 'GW';
    public const GUYANA = 'GY';
    public const HAITI = 'HT';
    public const HEARD_ISLAND_AND_MCDONALD_ISLANDS = 'HM';
    public const HONDURAS = 'HN';
    public const HONG_KONG = 'HK';
    public const HUNGARY = 'HU';
    public const ICELAND = 'IS';
    public const INDIA = 'IN';
    public const INDONESIA = 'ID';
    public const IRAQ = 'IQ';
    public const IRELAND = 'IE';
    public const ISLAMIC_REPUBLIC_OF_IRAN = 'IR';
    public const ISLE_OF_MAN = 'IM';
    public const ISRAEL = 'IL';
    public const ITALY = 'IT';
    public const JAMAICA = 'JM';
    public const JAPAN = 'JP';
    public const JERSEY = 'JE';
    public const JORDAN = 'JO';
    public const KAZAKHSTAN = 'KZ';
    public const KENYA = 'KE';
    public const KIRIBATI = 'KI';
    public const KOSOVO = 'XK';
    public const KUWAIT = 'KW';
    public const KYRGYZSTAN = 'KG';
    public const LAOS = 'LA';
    public const LATVIA = 'LV';
    public const LEBANON = 'LB';
    public const LESOTHO = 'LS';
    public const LIBERIA = 'LR';
    public const LIBYA = 'LY';
    public const LIECHTENSTEIN = 'LI';
    public const LITHUANIA = 'LT';
    public const LUXEMBOURG = 'LU';
    public const MACAO = 'MO';
    public const MACEDONIA = 'MK';
    public const MADAGASCAR = 'MG';
    public const MALAWI = 'MW';
    public const MALAYSIA = 'MY';
    public const MALDIVES = 'MV';
    public const MALI = 'ML';
    public const MALTA = 'MT';
    public const MARSHALL_ISLANDS = 'MH';
    public const MARTINIQUE = 'MQ';
    public const MAURITANIA = 'MR';
    public const MAURITIUS = 'MU';
    public const MAYOTTE = 'YT';
    public const MEXICO = 'MX';
    public const MICRONESIA = 'FM';
    public const MOLDOVA = 'MD';
    public const MONACO = 'MC';
    public const MONGOLIA = 'MN';
    public const MONTENEGRO = 'ME';
    public const MONTSERRAT = 'MS';
    public const MOROCCO = 'MA';
    public const MOZAMBIQUE = 'MZ';
    public const MYANMAR = 'MM';
    public const NAMIBIA = 'NA';
    public const NAURU = 'NR';
    public const NEPAL = 'NP';
    public const NETHERLANDS = 'NL';
    public const NETHERLANDS_ANTILLES = 'AN';
    public const NEW_CALEDONIA = 'NC';
    public const NEW_ZEALAND = 'NZ';
    public const NICARAGUA = 'NI';
    public const NIGER = 'NE';
    public const NIGERIA = 'NG';
    public const NIUE = 'NU';
    public const NORFOLK_ISLAND = 'NF';
    public const NORTHERN_MARIANA_ISLANDS = 'MP';
    public const NORTH_KOREA = 'KP';
    public const NORWAY = 'NO';
    public const OMAN = 'OM';
    public const PAKISTAN = 'PK';
    public const PALAU = 'PW';
    public const PALESTINE = 'PS';
    public const PANAMA = 'PA';
    public const PAPUA_NEW_GUINEA = 'PG';
    public const PARAGUAY = 'PY';
    public const PERU = 'PE';
    public const PHILIPPINES = 'PH';
    public const PITCAIRN = 'PN';
    public const POLAND = 'PL';
    public const PORTUGAL = 'PT';
    public const PUERTO_RICO = 'PR';
    public const QATAR = 'QA';
    public const REUNION = 'RE';
    public const ROMANIA = 'RO';
    public const RUSSIA = 'RU';
    public const RWANDA = 'RW';
    public const SAINT_HELENA = 'SH';
    public const SAINT_KITTS_AND_NEVIS = 'KN';
    public const SAINT_LUCIA = 'LC';
    public const SAINT_MARTIN_DUTCH = 'SX';
    public const SAINT_MARTIN_FRENCH = 'MF';
    public const SAINT_PIERRE_AND_MIQUELON = 'PM';
    public const SAINT_VINCENT_AND_THE_GRENADINES = 'VC';
    public const SAMOA = 'WS';
    public const SAN_MARINO = 'SM';
    public const SAO_TOME_AND_PRINCIPE = 'ST';
    public const SAUDI_ARABIA = 'SA';
    public const SENEGAL = 'SN';
    public const SERBIA = 'RS';
    public const SEYCHELLES = 'SC';
    public const SIERRA_LEONE = 'SL';
    public const SINGAPORE = 'SG';
    public const SLOVAKIA = 'SK';
    public const SLOVENIA = 'SI';
    public const SOLOMON_ISLANDS = 'SB';
    public const SOMALIA = 'SO';
    public const SOUTH_AFRICA = 'ZA';
    public const SOUTH_GEORGIA_AND_THE_SOUTH_SANDWICH = 'GS';
    public const SOUTH_KOREA = 'KR';
    public const SOUTH_SUDAN = 'SS';
    public const SPAIN = 'ES';
    public const SRI_LANKA = 'LK';
    public const SUDAN = 'SD';
    public const SURINAME = 'SR';
    public const SVALBARD_AND_JAN_MAYEN = 'SJ';
    public const SWAZILAND = 'SZ';
    public const SWEDEN = 'SE';
    public const SWITZERLAND = 'CH';
    public const SYRIA = 'SY';
    public const TAIWAN = 'TW';
    public const TAJIKISTAN = 'TJ';
    public const TANZANIA = 'TZ';
    public const THAILAND = 'TH';
    public const TIMOR_LESTE = 'TL';
    public const TOGO = 'TG';
    public const TOKELAU = 'TK';
    public const TONGA = 'TO';
    public const TRINIDAD_AND_TOBAGO = 'TT';
    public const TUNISIA = 'TN';
    public const TURKEY = 'TR';
    public const TURKMENISTAN = 'TM';
    public const TURKS_AND_CAICOS_ISLANDS = 'TC';
    public const TUVALU = 'TV';
    public const UGANDA = 'UG';
    public const UKRAINE = 'UA';
    public const UNITED_ARAB_EMIRATES = 'AE';
    public const UNITED_KINGDOM = 'GB';
    public const UNITED_STATES = 'US';
    public const UNITED_STATES_MINOR_OUTLYING_ISLANDS = 'UM';
    public const URUGUAY = 'UY';
    public const UZBEKISTAN = 'UZ';
    public const VANUATU = 'VU';
    public const VATICAN = 'VA';
    public const VENEZUELA = 'VE';
    public const VIETNAM = 'VN';
    public const VIRGIN_ISLANDS_BRITISH = 'VG';
    public const VIRGIN_ISLANDS_US = 'VI';
    public const WALLIS_AND_FUTUNA = 'WF';
    public const WESTERN_SAHARA = 'EH';
    public const YEMEN = 'YE';
    public const ZAMBIA = 'ZM';
    public const ZIMBABWE = 'ZW';

    /**
     * @var string[]
     */
    private static $names = [
        self::AFGHANISTAN => 'Afghanistan',
        self::ALAND_ISLANDS => 'Aland Islands',
        self::ALBANIA => 'Albania',
        self::ALGERIA => 'Algeria',
        self::AMERICAN_SAMOA => 'American Samoa',
        self::ANDORRA => 'Andorra',
        self::ANGOLA => 'Angola',
        self::ANGUILLA => 'Anguilla',
        self::ANTARCTICA => 'Antarctica',
        self::ANTIGUA_AND_BARBUDA => 'Antigua and Barbuda',
        self::ARGENTINA => 'Argentina',
        self::ARMENIA => 'Armenia',
        self::ARUBA => 'Aruba',
        self::AUSTRALIA => 'Australia',
        self::AUSTRIA => 'Austria',
        self::AZERBAIJAN => 'Azerbaijan',
        self::BAHAMAS => 'Bahamas',
        self::BAHRAIN => 'Bahrain',
        self::BANGLADESH => 'Bangladesh',
        self::BARBADOS => 'Barbados',
        self::BELARUS => 'Belarus',
        self::BELGIUM => 'Belgium',
        self::BELIZE => 'Belize',
        self::BENIN => 'Benin',
        self::BERMUDA => 'Bermuda',
        self::BHUTAN => 'Bhutan',
        self::BOLIVIA => 'Bolivia',
        self::BOSNIA_AND_HERZEGOVINA => 'Bosnia and Herzegovina',
        self::BOTSWANA => 'Botswana',
        self::BOUVET_ISLAND => 'Bouvet Island',
        self::BRAZIL => 'Brazil',
        self::BRITISH_INDIAN_OCEAN_TERRITORY => 'British Indian Ocean Territory',
        self::BRUNEI_DARUSSALAM => 'Brunei Darussalam',
        self::BULGARIA => 'Bulgaria',
        self::BURKINA_FASO => 'Burkina Faso',
        self::BURUNDI => 'Burundi',
        self::CAMBODIA => 'Cambodia',
        self::CAMEROON => 'Cameroon',
        self::CANADA => 'Canada',
        self::CAPE_VERDE => 'Cape Verde',
        self::CAYMAN_ISLANDS => 'Cayman Islands',
        self::CENTRAL_AFRICAN_REPUBLIC => 'Central African Republic',
        self::CHAD => 'Chad',
        self::CHILE => 'Chile',
        self::CHINA => 'China',
        self::CHRISTMAS_ISLAND => 'Christmas Island',
        self::COCOS_ISLANDS => 'Cocos (Keeling) Islands',
        self::COLOMBIA => 'Colombia',
        self::COMOROS => 'Comoros',
        self::CONGO => 'Congo',
        self::COOK_ISLANDS => 'Cook Islands',
        self::COSTA_RICA => 'Costa Rica',
        self::COTE_D_IVOIRE => 'Cóte d\'Ivoire',
        self::CROATIA => 'Croatia',
        self::CUBA => 'Cuba',
        self::CYPRUS => 'Cyprus',
        self::CZECHIA => 'Czechia',
        self::DEMOCRATIC_REPUBLIC_OF_THE_CONGO => 'Congo, Democratic Republic of the',
        self::DENMARK => 'Denmark',
        self::DJIBOUTI => 'Djibouti',
        self::DOMINICA => 'Dominica',
        self::DOMINICAN_REPUBLIC => 'Dominican Republic',
        self::ECUADOR => 'Ecuador',
        self::EGYPT => 'Egypt',
        self::EL_SALVADOR => 'El Salvador',
        self::EQUATORIAL_GUINEA => 'Equatorial Guinea',
        self::ERITREA => 'Eritrea',
        self::ESTONIA => 'Estonia',
        self::ETHIOPIA => 'Ethiopia',
        self::EU => 'Europe (region)',
        self::FALKLAND_ISLANDS => 'Falkland Islands (Malvinas)',
        self::FAROE_ISLANDS => 'Faroe Islands',
        self::FIJI => 'Fiji',
        self::FINLAND => 'Finland',
        self::FRANCE => 'France',
        self::FRENCH_GUIANA => 'French Guiana',
        self::FRENCH_POLYNESIA => 'French Polynesia',
        self::FRENCH_SOUTHERN_TERRITORIES => 'French Southern Territories',
        self::GABON => 'Gabon',
        self::GAMBIA => 'Gambia',
        self::GEORGIA => 'Georgia',
        self::GERMANY => 'Germany',
        self::GHANA => 'Ghana',
        self::GIBRALTAR => 'Gibraltar',
        self::GREECE => 'Greece',
        self::GREENLAND => 'Greenland',
        self::GRENADA => 'Grenada',
        self::GUADELOUPE => 'Guadeloupe',
        self::GUAM => 'Guam',
        self::GUATEMALA => 'Guatemala',
        self::GUERNSEY => 'Guernsey',
        self::GUINEA => 'Guinea',
        self::GUINEA_BISSAU => 'Guinea-Bissau',
        self::GUYANA => 'Guyana',
        self::HAITI => 'Haiti',
        self::HEARD_ISLAND_AND_MCDONALD_ISLANDS => 'Heard Island and McDonald Islands',
        self::HONDURAS => 'Honduras',
        self::HONG_KONG => 'Hong Kong',
        self::HUNGARY => 'Hungary',
        self::ICELAND => 'Iceland',
        self::INDIA => 'India',
        self::INDONESIA => 'Indonesia',
        self::IRAQ => 'Iraq',
        self::IRELAND => 'Ireland',
        self::ISLAMIC_REPUBLIC_OF_IRAN => 'Iran, Islamic Republic of',
        self::ISLE_OF_MAN => 'Isle of Man',
        self::ISRAEL => 'Israel',
        self::ITALY => 'Italy',
        self::JAMAICA => 'Jamaica',
        self::JAPAN => 'Japan',
        self::JERSEY => 'Jersey',
        self::JORDAN => 'Jordan',
        self::KAZAKHSTAN => 'Kazakhstan',
        self::KENYA => 'Kenya',
        self::KIRIBATI => 'Kiribati',
        self::KOSOVO => 'Kosovo',
        self::KUWAIT => 'Kuwait',
        self::KYRGYZSTAN => 'Kyrgyzstan',
        self::LAOS => 'Laos',
        self::LATVIA => 'Latvia',
        self::LEBANON => 'Lebanon',
        self::LESOTHO => 'Lesotho',
        self::LIBERIA => 'Liberia',
        self::LIBYA => 'Libya',
        self::LIECHTENSTEIN => 'Liechtenstein',
        self::LITHUANIA => 'Lithuania',
        self::LUXEMBOURG => 'Luxembourg',
        self::MACAO => 'Macao',
        self::MACEDONIA => 'Macedonia',
        self::MADAGASCAR => 'Madagascar',
        self::MALAWI => 'Malawi',
        self::MALAYSIA => 'Malaysia',
        self::MALDIVES => 'Maldives',
        self::MALI => 'Mali',
        self::MALTA => 'Malta',
        self::MARSHALL_ISLANDS => 'Marshall Islands',
        self::MARTINIQUE => 'Martinique',
        self::MAURITANIA => 'Mauritania',
        self::MAURITIUS => 'Mauritius',
        self::MAYOTTE => 'Mayotte',
        self::MEXICO => 'Mexico',
        self::MICRONESIA => 'Micronesia',
        self::MOLDOVA => 'Moldova',
        self::MONACO => 'Monaco',
        self::MONGOLIA => 'Mongolia',
        self::MONTENEGRO => 'Montenegro',
        self::MONTSERRAT => 'Montserrat',
        self::MOROCCO => 'Morocco',
        self::MOZAMBIQUE => 'Mozambique',
        self::MYANMAR => 'Myanmar',
        self::NAMIBIA => 'Namibia',
        self::NAURU => 'Nauru',
        self::NEPAL => 'Nepal',
        self::NETHERLANDS => 'Netherlands',
        self::NETHERLANDS_ANTILLES => 'Netherlands Antilles',
        self::NEW_CALEDONIA => 'New Caledonia',
        self::NEW_ZEALAND => 'New Zealand',
        self::NICARAGUA => 'Nicaragua',
        self::NIGER => 'Niger',
        self::NIGERIA => 'Nigeria',
        self::NIUE => 'Niue',
        self::NORFOLK_ISLAND => 'Norfolk Island',
        self::NORTHERN_MARIANA_ISLANDS => 'Northern Mariana Islands',
        self::NORTH_KOREA => 'North Korea',
        self::NORWAY => 'Norway',
        self::OMAN => 'Oman',
        self::PAKISTAN => 'Pakistan',
        self::PALAU => 'Palau',
        self::PALESTINE => 'Palestine',
        self::PANAMA => 'Panama',
        self::PAPUA_NEW_GUINEA => 'Papua New Guinea',
        self::PARAGUAY => 'Paraguay',
        self::PERU => 'Peru',
        self::PHILIPPINES => 'Philippines',
        self::PITCAIRN => 'Pitcairn',
        self::POLAND => 'Poland',
        self::PORTUGAL => 'Portugal',
        self::PUERTO_RICO => 'Puerto Rico',
        self::QATAR => 'Qatar',
        self::REUNION => 'Réunion',
        self::ROMANIA => 'Romania',
        self::RUSSIA => 'Russia',
        self::RWANDA => 'Rwanda',
        self::SAINT_HELENA => 'Saint Helena',
        self::SAINT_KITTS_AND_NEVIS => 'Saint Kitts and Nevis',
        self::SAINT_LUCIA => 'Saint Lucia',
        self::SAINT_MARTIN_DUTCH => 'Saint Martin (Dutch)',
        self::SAINT_MARTIN_FRENCH => 'Saint Martin (French)',
        self::SAINT_PIERRE_AND_MIQUELON => 'Saint Pierre and Miquelon',
        self::SAINT_VINCENT_AND_THE_GRENADINES => 'Saint Vincent and the Grenadines',
        self::SAMOA => 'Samoa',
        self::SAN_MARINO => 'San Marino',
        self::SAO_TOME_AND_PRINCIPE => 'Sao Tome and Principe',
        self::SAUDI_ARABIA => 'Saudi Arabia',
        self::SENEGAL => 'Senegal',
        self::SERBIA => 'Serbia',
        self::SEYCHELLES => 'Seychelles',
        self::SIERRA_LEONE => 'Sierra Leone',
        self::SINGAPORE => 'Singapore',
        self::SLOVAKIA => 'Slovakia',
        self::SLOVENIA => 'Slovenia',
        self::SOLOMON_ISLANDS => 'Solomon Islands',
        self::SOMALIA => 'Somalia',
        self::SOUTH_AFRICA => 'South Africa',
        self::SOUTH_GEORGIA_AND_THE_SOUTH_SANDWICH => 'South Georgia and the South Sandwich',
        self::SOUTH_KOREA => 'South Korea',
        self::SOUTH_SUDAN => 'South Sudan',
        self::SPAIN => 'Spain',
        self::SRI_LANKA => 'Sri Lanka',
        self::SUDAN => 'Sudan',
        self::SURINAME => 'Suriname',
        self::SVALBARD_AND_JAN_MAYEN => 'Svalbard and Jan Mayen',
        self::SWAZILAND => 'Swaziland',
        self::SWEDEN => 'Sweden',
        self::SWITZERLAND => 'Switzerland',
        self::SYRIA => 'Syria',
        self::TAIWAN => 'Taiwan',
        self::TAJIKISTAN => 'Tajikistan',
        self::TANZANIA => 'Tanzania',
        self::THAILAND => 'Thailand',
        self::TIMOR_LESTE => 'Timor-Leste',
        self::TOGO => 'Togo',
        self::TOKELAU => 'Tokelau',
        self::TONGA => 'Tonga',
        self::TRINIDAD_AND_TOBAGO => 'Trinidad and Tobago',
        self::TUNISIA => 'Tunisia',
        self::TURKEY => 'Turkey',
        self::TURKMENISTAN => 'Turkmenistan',
        self::TURKS_AND_CAICOS_ISLANDS => 'Turks and Caicos Islands',
        self::TUVALU => 'Tuvalu',
        self::UGANDA => 'Uganda',
        self::UKRAINE => 'Ukraine',
        self::UNITED_ARAB_EMIRATES => 'United Arab Emirates',
        self::UNITED_KINGDOM => 'United Kingdom',
        self::UNITED_STATES => 'United States',
        self::UNITED_STATES_MINOR_OUTLYING_ISLANDS => 'United States Minor Outlying Islands',
        self::URUGUAY => 'Uruguay',
        self::UZBEKISTAN => 'Uzbekistan',
        self::VANUATU => 'Vanuatu',
        self::VATICAN => 'Vatican',
        self::VENEZUELA => 'Venezuela',
        self::VIETNAM => 'Vietnam',
        self::VIRGIN_ISLANDS_BRITISH => 'Virgin Islands, British',
        self::VIRGIN_ISLANDS_US => 'Virgin Islands, U.S.',
        self::WALLIS_AND_FUTUNA => 'Wallis and Futuna',
        self::WESTERN_SAHARA => 'Western Sahara',
        self::YEMEN => 'Yemen',
        self::ZAMBIA => 'Zambia',
        self::ZIMBABWE => 'Zimbabwe',
    ];

    /**
     * @var string[]
     */
    private static $idents = [
        self::ANDORRA => 'andorra',
        self::UNITED_ARAB_EMIRATES => 'united-arab-emirates',
        self::AFGHANISTAN => 'afghanistan',
        self::ANTIGUA_AND_BARBUDA => 'antigua-and-barbuda',
        self::ANGUILLA => 'anguilla',
        self::ALBANIA => 'albania',
        self::ARMENIA => 'armenia',
        self::NETHERLANDS_ANTILLES => 'netherlands-antilles',
        self::ANGOLA => 'angola',
        self::ANTARCTICA => 'antarctica',
        self::ARGENTINA => 'argentina',
        self::AMERICAN_SAMOA => 'american-samoa',
        self::AUSTRIA => 'austria',
        self::AUSTRALIA => 'australia',
        self::ARUBA => 'aruba',
        self::ALAND_ISLANDS => 'aland-islands',
        self::AZERBAIJAN => 'azerbaijan',
        self::BOSNIA_AND_HERZEGOVINA => 'bosnia-and-herzegovina',
        self::BARBADOS => 'barbados',
        self::BANGLADESH => 'bangladesh',
        self::BELGIUM => 'belgium',
        self::BURKINA_FASO => 'burkina-faso',
        self::BULGARIA => 'bulgaria',
        self::BAHRAIN => 'bahrain',
        self::BURUNDI => 'burundi',
        self::BENIN => 'benin',
        self::BERMUDA => 'bermuda',
        self::BRUNEI_DARUSSALAM => 'brunei-darussalam',
        self::BOLIVIA => 'bolivia',
        self::BRAZIL => 'brazil',
        self::BAHAMAS => 'bahamas',
        self::BHUTAN => 'bhutan',
        self::BOUVET_ISLAND => 'bouvet-island',
        self::BOTSWANA => 'botswana',
        self::BELARUS => 'belarus',
        self::BELIZE => 'belize',
        self::CANADA => 'canada',
        self::COCOS_ISLANDS => 'cocos-islands',
        self::DEMOCRATIC_REPUBLIC_OF_THE_CONGO => 'democratic-republic-of-the-congo',
        self::CENTRAL_AFRICAN_REPUBLIC => 'central-african-republic',
        self::CONGO => 'congo',
        self::SWITZERLAND => 'switzerland',
        self::COTE_D_IVOIRE => 'cote-d-ivoire',
        self::COOK_ISLANDS => 'cook-islands',
        self::CHILE => 'chile',
        self::CAMEROON => 'cameroon',
        self::CHINA => 'china',
        self::COLOMBIA => 'colombia',
        self::COSTA_RICA => 'costa-rica',
        self::CUBA => 'cuba',
        self::CAPE_VERDE => 'cape-verde',
        self::CHRISTMAS_ISLAND => 'christmas-island',
        self::CYPRUS => 'cyprus',
        self::CZECHIA => 'czechia',
        self::GERMANY => 'germany',
        self::DJIBOUTI => 'djibouti',
        self::DENMARK => 'denmark',
        self::DOMINICA => 'dominica',
        self::DOMINICAN_REPUBLIC => 'dominican-republic',
        self::ALGERIA => 'algeria',
        self::ECUADOR => 'ecuador',
        self::ESTONIA => 'estonia',
        self::EGYPT => 'egypt',
        self::WESTERN_SAHARA => 'western-sahara',
        self::ERITREA => 'eritrea',
        self::SPAIN => 'spain',
        self::ETHIOPIA => 'ethiopia',
        self::EU => 'eu',
        self::FINLAND => 'finland',
        self::FIJI => 'fiji',
        self::FALKLAND_ISLANDS => 'falkland-islands',
        self::MICRONESIA => 'micronesia',
        self::FAROE_ISLANDS => 'faroe-islands',
        self::FRANCE => 'france',
        self::GABON => 'gabon',
        self::UNITED_KINGDOM => 'united-kingdom',
        self::GRENADA => 'grenada',
        self::GEORGIA => 'georgia',
        self::FRENCH_GUIANA => 'french-guiana',
        self::GUERNSEY => 'guernsey',
        self::GHANA => 'ghana',
        self::GIBRALTAR => 'gibraltar',
        self::GREENLAND => 'greenland',
        self::GAMBIA => 'gambia',
        self::GUINEA => 'guinea',
        self::GUADELOUPE => 'guadeloupe',
        self::EQUATORIAL_GUINEA => 'equatorial-guinea',
        self::GREECE => 'greece',
        self::SOUTH_GEORGIA_AND_THE_SOUTH_SANDWICH => 'south-georgia-and-the-south-sandwich',
        self::GUATEMALA => 'guatemala',
        self::GUAM => 'guam',
        self::GUINEA_BISSAU => 'guinea-bissau',
        self::GUYANA => 'guyana',
        self::HONG_KONG => 'hong-kong',
        self::HEARD_ISLAND_AND_MCDONALD_ISLANDS => 'heard-island-and-mcdonald-islands',
        self::HONDURAS => 'honduras',
        self::CROATIA => 'croatia',
        self::HAITI => 'haiti',
        self::HUNGARY => 'hungary',
        self::INDONESIA => 'indonesia',
        self::IRELAND => 'ireland',
        self::ISRAEL => 'israel',
        self::ISLE_OF_MAN => 'isle-of-man',
        self::INDIA => 'india',
        self::BRITISH_INDIAN_OCEAN_TERRITORY => 'british-indian-ocean-territory',
        self::IRAQ => 'iraq',
        self::ISLAMIC_REPUBLIC_OF_IRAN => 'islamic-republic-of-iran',
        self::ICELAND => 'iceland',
        self::ITALY => 'italy',
        self::JERSEY => 'jersey',
        self::JAMAICA => 'jamaica',
        self::JORDAN => 'jordan',
        self::JAPAN => 'japan',
        self::KENYA => 'kenya',
        self::KYRGYZSTAN => 'kyrgyzstan',
        self::CAMBODIA => 'cambodia',
        self::KIRIBATI => 'kiribati',
        self::COMOROS => 'comoros',
        self::SAINT_KITTS_AND_NEVIS => 'saint-kitts-and-nevis',
        self::NORTH_KOREA => 'north-korea',
        self::SOUTH_KOREA => 'south-korea',
        self::KUWAIT => 'kuwait',
        self::CAYMAN_ISLANDS => 'cayman-islands',
        self::KAZAKHSTAN => 'kazakhstan',
        self::LAOS => 'laos',
        self::LEBANON => 'lebanon',
        self::SAINT_LUCIA => 'saint-lucia',
        self::LIECHTENSTEIN => 'liechtenstein',
        self::SRI_LANKA => 'sri-lanka',
        self::LIBERIA => 'liberia',
        self::LESOTHO => 'lesotho',
        self::LITHUANIA => 'lithuania',
        self::LUXEMBOURG => 'luxembourg',
        self::LATVIA => 'latvia',
        self::LIBYA => 'libya',
        self::MOROCCO => 'morocco',
        self::MONACO => 'monaco',
        self::MOLDOVA => 'moldova',
        self::MONTENEGRO => 'montenegro',
        self::SAINT_MARTIN_FRENCH => 'saint-martin-french',
        self::MADAGASCAR => 'madagascar',
        self::MARSHALL_ISLANDS => 'marshall-islands',
        self::MACEDONIA => 'macedonia',
        self::MALI => 'mali',
        self::MYANMAR => 'myanmar',
        self::MONGOLIA => 'mongolia',
        self::MACAO => 'macao',
        self::NORTHERN_MARIANA_ISLANDS => 'northern-mariana-islands',
        self::MARTINIQUE => 'martinique',
        self::MAURITANIA => 'mauritania',
        self::MONTSERRAT => 'montserrat',
        self::MALTA => 'malta',
        self::MAURITIUS => 'mauritius',
        self::MALDIVES => 'maldives',
        self::MALAWI => 'malawi',
        self::MEXICO => 'mexico',
        self::MALAYSIA => 'malaysia',
        self::MOZAMBIQUE => 'mozambique',
        self::NAMIBIA => 'namibia',
        self::NEW_CALEDONIA => 'new-caledonia',
        self::NIGER => 'niger',
        self::NORFOLK_ISLAND => 'norfolk-island',
        self::NIGERIA => 'nigeria',
        self::NICARAGUA => 'nicaragua',
        self::NETHERLANDS => 'netherlands',
        self::NORWAY => 'norway',
        self::NEPAL => 'nepal',
        self::NAURU => 'nauru',
        self::NIUE => 'niue',
        self::NEW_ZEALAND => 'new-zealand',
        self::OMAN => 'oman',
        self::PANAMA => 'panama',
        self::PERU => 'peru',
        self::FRENCH_POLYNESIA => 'french-polynesia',
        self::PAPUA_NEW_GUINEA => 'papua-new-guinea',
        self::PHILIPPINES => 'philippines',
        self::PAKISTAN => 'pakistan',
        self::POLAND => 'poland',
        self::SAINT_PIERRE_AND_MIQUELON => 'saint-pierre-and-miquelon',
        self::PITCAIRN => 'pitcairn',
        self::PUERTO_RICO => 'puerto-rico',
        self::PALESTINE => 'palestine',
        self::PORTUGAL => 'portugal',
        self::PALAU => 'palau',
        self::PARAGUAY => 'paraguay',
        self::QATAR => 'qatar',
        self::REUNION => 'reunion',
        self::ROMANIA => 'romania',
        self::SERBIA => 'serbia',
        self::RUSSIA => 'russia',
        self::RWANDA => 'rwanda',
        self::SAUDI_ARABIA => 'saudi-arabia',
        self::SOLOMON_ISLANDS => 'solomon-islands',
        self::SEYCHELLES => 'seychelles',
        self::SUDAN => 'sudan',
        self::SWEDEN => 'sweden',
        self::SINGAPORE => 'singapore',
        self::SAINT_HELENA => 'saint-helena',
        self::SLOVENIA => 'slovenia',
        self::SVALBARD_AND_JAN_MAYEN => 'svalbard-and-jan-mayen',
        self::SLOVAKIA => 'slovakia',
        self::SIERRA_LEONE => 'sierra-leone',
        self::SAN_MARINO => 'san-marino',
        self::SENEGAL => 'senegal',
        self::SOMALIA => 'somalia',
        self::SURINAME => 'suriname',
        self::SOUTH_SUDAN => 'south-sudan',
        self::SAO_TOME_AND_PRINCIPE => 'sao-tome-and-principe',
        self::EL_SALVADOR => 'el-salvador',
        self::SAINT_MARTIN_DUTCH => 'saint-martin-dutch',
        self::SYRIA => 'syria',
        self::SWAZILAND => 'swaziland',
        self::TURKS_AND_CAICOS_ISLANDS => 'turks-and-caicos-islands',
        self::CHAD => 'chad',
        self::FRENCH_SOUTHERN_TERRITORIES => 'french-southern-territories',
        self::TOGO => 'togo',
        self::THAILAND => 'thailand',
        self::TAJIKISTAN => 'tajikistan',
        self::TOKELAU => 'tokelau',
        self::TIMOR_LESTE => 'timor-leste',
        self::TURKMENISTAN => 'turkmenistan',
        self::TUNISIA => 'tunisia',
        self::TONGA => 'tonga',
        self::TURKEY => 'turkey',
        self::TRINIDAD_AND_TOBAGO => 'trinidad-and-tobago',
        self::TUVALU => 'tuvalu',
        self::TAIWAN => 'taiwan',
        self::TANZANIA => 'tanzania',
        self::UKRAINE => 'ukraine',
        self::UGANDA => 'uganda',
        self::UNITED_STATES_MINOR_OUTLYING_ISLANDS => 'united-states-minor-outlying-islands',
        self::UNITED_STATES => 'united-states',
        self::URUGUAY => 'uruguay',
        self::UZBEKISTAN => 'uzbekistan',
        self::VATICAN => 'vatican',
        self::SAINT_VINCENT_AND_THE_GRENADINES => 'saint-vincent-and-the-grenadines',
        self::VENEZUELA => 'venezuela',
        self::VIRGIN_ISLANDS_BRITISH => 'virgin-islands-british',
        self::VIRGIN_ISLANDS_US => 'virgin-islands-us',
        self::VIETNAM => 'vietnam',
        self::VANUATU => 'vanuatu',
        self::WALLIS_AND_FUTUNA => 'wallis-and-futuna',
        self::SAMOA => 'samoa',
        self::KOSOVO => 'kosovo',
        self::YEMEN => 'yemen',
        self::MAYOTTE => 'mayotte',
        self::SOUTH_AFRICA => 'south-africa',
        self::ZAMBIA => 'zambia',
        self::ZIMBABWE => 'zimbabwe',
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

    public function getIdent(): string
    {
        return self::$idents[$this->getValue()];
    }

    public function getSymbol(): string
    {
        $code = $this->getValue();

        return "\xF0\x9F\x87" . chr(ord($code[0]) + 0x65) . "\xF0\x9F\x87" . chr(ord($code[1]) + 0x65);
    }

    public static function getByIdent(string $ident): self
    {
        return self::get(array_search($ident, self::$idents));
    }

    public static function validateValue(string &$value): bool
    {
        $value = strtoupper($value);

        return parent::validateValue($value);
    }

}
