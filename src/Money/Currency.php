<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Money;

use Dogma\Enum\StringEnum;
use function array_search;
use function strtoupper;

class Currency extends StringEnum
{

    public const AFGHANI = 'AFN';
    public const ALGERIAN_DINAR = 'DZD';
    public const ARGENTINE_PESO = 'ARS';
    public const ARMENIAN_DRAM = 'AMD';
    public const ARUBAN_FLORIN = 'AWG';
    public const AUSTRALIAN_DOLLAR = 'AUD';
    public const AZERBAIJANIAN_MANAT = 'AZN';
    public const BAHAMIAN_DOLLAR = 'BSD';
    public const BAHRAINI_DINAR = 'BHD';
    public const BAHT = 'THB';
    public const BALBOA = 'PAB';
    public const BARBADOS_DOLLAR = 'BBD';
    public const BELARUSIAN_RUBLE = 'BYN';
    public const BELIZE_DOLLAR = 'BZD';
    public const BERMUDIAN_DOLLAR = 'BMD';
    public const BITCOIN = 'XBT';
    public const BOLIVAR_SOBERANO = 'VES';
    public const BOLIVIANO = 'BOB';
    public const BRAZILIAN_REAL = 'BRL';
    public const BRUNEI_DOLLAR = 'BND';
    public const BURUNDI_FRANC = 'BIF';
    public const CABO_VERDE_ESCUDO = 'CVE';
    public const CANADIAN_DOLLAR = 'CAD';
    public const CAYMAN_ISLANDS_DOLLAR = 'KYD';
    public const CFA_FRANC_BCEAO = 'XOF';
    public const CFA_FRANC_BEAC = 'XAF';
    public const CFP_FRANC = 'XPF';
    public const CHILEAN_PESO = 'CLP';
    public const COLOMBIAN_PESO = 'COP';
    public const COMORO_FRANC = 'KMF';
    public const CONGOLESE_FRANC = 'CDF';
    public const CONVERTIBLE_MARK = 'BAM';
    public const CORDOBA_ORO = 'NIO';
    public const COSTA_RICAN_COLON = 'CRC';
    public const CUBAN_PESO = 'CUP';
    public const CZECH_KORUNA = 'CZK';
    public const DALASI = 'GMD';
    public const DANISH_KRONE = 'DKK';
    public const DENAR = 'MKD';
    public const DJIBOUTI_FRANC = 'DJF';
    public const DOBRA = 'STN';
    public const DOMINICAN_PESO = 'DOP';
    public const DONG = 'VND';
    public const EAST_CARIBBEAN_DOLLAR = 'XCD';
    public const EGYPTIAN_POUND = 'EGP';
    public const EL_SALVADOR_COLON = 'SVC';
    public const ETHIOPIAN_BIRR = 'ETB';
    public const EURO = 'EUR';
    public const FALKLAND_ISLANDS_POUND = 'FKP';
    public const FIJI_DOLLAR = 'FJD';
    public const FORINT = 'HUF';
    public const GHANA_CEDI = 'GHS';
    public const GIBRALTAR_POUND = 'GIP';
    public const GOLD = 'XAU';
    public const GOURDE = 'HTG';
    public const GUARANI = 'PYG';
    public const GUINEA_FRANC = 'GNF';
    public const GUYANA_DOLLAR = 'GYD';
    public const HONG_KONG_DOLLAR = 'HKD';
    public const HRYVNIA = 'UAH';
    public const ICELAND_KRONA = 'ISK';
    public const INDIAN_RUPEE = 'INR';
    public const IRANIAN_RIAL = 'IRR';
    public const IRAQI_DINAR = 'IQD';
    public const JAMAICAN_DOLLAR = 'JMD';
    public const JORDANIAN_DINAR = 'JOD';
    public const KENYAN_SHILLING = 'KES';
    public const KINA = 'PGK';
    public const KIP = 'LAK';
    public const KUWAITI_DINAR = 'KWD';
    public const KWANZA = 'AOA';
    public const KYAT = 'MMK';
    public const LARI = 'GEL';
    public const LEBANESE_POUND = 'LBP';
    public const LEK = 'ALL';
    public const LEMPIRA = 'HNL';
    public const LEONE = 'SLE';
    public const LIBERIAN_DOLLAR = 'LRD';
    public const LIBYAN_DINAR = 'LYD';
    public const LILANGENI = 'SZL';
    public const LOTI = 'LSL';
    public const MALAGASY_ARIARY = 'MGA';
    public const MALAWI_KWACHA = 'MWK';
    public const MALAYSIAN_RINGGIT = 'MYR';
    public const MAURITIUS_RUPEE = 'MUR';
    public const MEXICAN_PESO = 'MXN';
    public const MOLDOVAN_LEU = 'MDL';
    public const MOROCCAN_DIRHAM = 'MAD';
    public const MOZAMBIQUE_METICAL = 'MZN';
    public const NAIRA = 'NGN';
    public const NAKFA = 'ERN';
    public const NAMIBIA_DOLLAR = 'NAD';
    public const NEPALESE_RUPEE = 'NPR';
    public const NETHERLANDS_ANTILLEAN_GUILDER = 'ANG';
    public const NEW_ISRAELI_SHEQEL = 'ILS';
    public const NEW_TAIWAN_DOLLAR = 'TWD';
    public const NEW_ZEALAND_DOLLAR = 'NZD';
    public const NGULTRUM = 'BTN';
    public const NORTH_KOREAN_WON = 'KPW';
    public const NORWEGIAN_KRONE = 'NOK';
    public const OUGUIYA = 'MRU';
    public const PAANGA = 'TOP';
    public const PAKISTAN_RUPEE = 'PKR';
    public const PALLADIUM = 'XPD';
    public const PATACA = 'MOP';
    public const PESO_URUGUAYO = 'UYU';
    public const PHILIPPINE_PESO = 'PHP';
    public const PLATINUM = 'XPT';
    public const POUND_STERLING = 'GBP';
    public const PULA = 'BWP';
    public const QATARI_RIAL = 'QAR';
    public const QUETZAL = 'GTQ';
    public const RAND = 'ZAR';
    public const RIAL_OMANI = 'OMR';
    public const RIEL = 'KHR';
    public const ROMANIAN_LEU = 'RON';
    public const RUFIYAA = 'MVR';
    public const RUPIAH = 'IDR';
    public const RUSSIAN_RUBLE = 'RUB';
    public const RWANDA_FRANC = 'RWF';
    public const SAINT_HELENA_POUND = 'SHP';
    public const SAUDI_RIYAL = 'SAR';
    public const SERBIAN_DINAR = 'RSD';
    public const SEYCHELLES_RUPEE = 'SCR';
    public const SILVER = 'XAG';
    public const SINGAPORE_DOLLAR = 'SGD';
    public const SOL = 'PEN';
    public const SOLOMON_ISLANDS_DOLLAR = 'SBD';
    public const SOM = 'KGS';
    public const SOMALI_SHILLING = 'SOS';
    public const SOMONI = 'TJS';
    public const SOUTH_SUDANESE_POUND = 'SSP';
    public const SRI_LANKA_RUPEE = 'LKR';
    public const SUDANESE_POUND = 'SDG';
    public const SURINAM_DOLLAR = 'SRD';
    public const SWEDISH_KRONA = 'SEK';
    public const SWISS_FRANC = 'CHF';
    public const SYRIAN_POUND = 'SYP';
    public const TAKA = 'BDT';
    public const TALA = 'WST';
    public const TANZANIAN_SHILLING = 'TZS';
    public const TENGE = 'KZT';
    public const TRINIDAD_AND_TOBAGO_DOLLAR = 'TTD';
    public const TUGRIK = 'MNT';
    public const TUNISIAN_DINAR = 'TND';
    public const TURKISH_LIRA = 'TRY';
    public const TURKMENISTAN_NEW_MANAT = 'TMT';
    public const UAE_DIRHAM = 'AED';
    public const UGANDA_SHILLING = 'UGX';
    public const US_DOLLAR = 'USD';
    public const UZBEKISTAN_SUM = 'UZS';
    public const VATU = 'VUV';
    public const WON = 'KRW';
    public const YEMENI_RIAL = 'YER';
    public const YEN = 'JPY';
    public const YUAN_RENMINBI = 'CNY';
    public const ZAMBIAN_KWACHA = 'ZMW';
    public const ZIMBABWE_GOLD = 'ZWG';
    public const ZLOTY = 'PLN';

    /** @deprecated replaced by EUR in 1999/2002 */
    public const AUSTRIAN_SCHILLING = 'ATS';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const BELGIAN_FRANC = 'BEF';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const DEUTSCHE_MARK = 'DEM';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const DUTCH_GUILDER = 'NLG';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const FINNISH_MARKA = 'FIM';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const FRENCH_FRANC = 'FRF';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const IRISH_POUNT = 'IEP';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const ITALIAN_LIRA = 'ITL';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const LUXEMBOURG_FRANC = 'LUF';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const PORTUGUESE_ESCUDO = 'PTE';
    /** @deprecated replaced by EUR in 1999/2002 */
    public const SPANISH_PESETA = 'ESP';
    /** @deprecated replaced by EUR in 2001/2002 */
    public const GREEK_DRACHMA = 'GRD';
    /** @deprecated replaced by EUR in 2007 */
    public const SLOVENIAN_TOLAR = 'SIT';
    /** @deprecated replaced by EUR in 2008 */
    public const CYPRUS_POUND = 'CYP';
    /** @deprecated replaced by EUR in 2008 */
    public const MALTESE_LIRA = 'MTL';
    /** @deprecated replaced by EUR in 2009 */
    public const SLOVAK_KORUNA = 'SKK';
    /** @deprecated replaced by EUR in 2011 */
    public const ESTONIAN_KROON = 'EEK';
    /** @deprecated replaced by EUR in 2014 */
    public const LATVIAN_LATS = 'LVL';
    /** @deprecated replaced by EUR in 2015 */
    public const LITHUANIAN_LITAS = 'LTL';
    /** @deprecated replaced by EUR in 2023 */
    public const KUNA = 'HRK';
    /** @deprecated replaced by EUR in 2026 */
    public const BULGARIAN_LEV = 'BGN';

    /** @deprecated replaced by UGX in 1987 */
    public const UGANDA_SHILLING_OLD = 'UGS';
    /** @deprecated German Reunification in 1990 */
    public const EAST_GERMAN_MARK = 'DDM';
    /** @deprecated replaced by PEN in 1991 */
    public const PERUVIAN_INTI = 'PEI';
    /** @deprecated dissolution of USSR in 1991 */
    public const SOVIET_RUBLE = 'SUR';
    /** @deprecated dissolution of Yugoslavia in 1992 */
    public const YUGOSLAV_DINAR = 'YUD';
    /** @deprecated replaced by MXN in 1993 */
    public const MEXICAN_PESO_OLD = 'MXP';
    /** @deprecated replaced by BRL in 1994 */
    public const BRAZILIAN_CRUZEIRO = 'BRB';
    /** @deprecated replaced by PLN in 1995 */
    public const POLISH_ZLOTY_OLD = 'PLZ';
    /** @deprecated replaced by AOA in 1999 */
    public const ANGOLAN_KWANZA_REAJUSTADO = 'AOR';
    /** @deprecated replaced by AFN in 2002 */
    public const AFGHANI_OLD = 'AFA';
    /** @deprecated replaced by MGA in 2005 */
    public const MALAGASY_FRANC = 'MGF';
    /** @deprecated replaced by RON in 2005 */
    public const ROMANIAN_LEU_OLD = 'ROL';
    /** @deprecated replaced by TRY in 2005 */
    public const TURKISH_LIRA_OLD = 'TRL';
    /** @deprecated replaced by AZN in 2006 */
    public const AZERBAIJAN_MANAT_OLD = 'AYM';
    /** @deprecated replaced by MZN in 2006 */
    public const MOZAMBIQUE_METICAL_OLD = 'MZM';
    /** @deprecated replaced by GHS in 2007 */
    public const GHANA_CEDI_OLD = 'GHC';
    /** @deprecated replaced by SDG in 2007 */
    public const SUDANESE_DINAR = 'SDD';
    /** @deprecated replaced by ZMW in 2013 */
    public const ZAMBIAN_KWACHA_OLD = 'ZMK';
    /** @deprecated replaced by BYN in 2016 */
    public const BELARUSIAN_RUBLE_OLD = 'BYR';
    /** @deprecated replaced by VES in 2018 */
    public const BOLIVAR_OLD = 'VEF';
    /** @deprecated replaced by MRU in 2018 */
    public const OUGUIYA_OLD = 'MRO';
    /** @deprecated replaced by STN in 2018 */
    public const DOBRA_OLD = 'STD';
    /** @deprecated merged into CUP 2021 */
    public const PESO_CONVERTIBLE = 'CUC';
    /** @deprecated replaced by SLE in 2022 */
    public const LEONE_OLD = 'SLL';
    /** @deprecated replaced by ZWG 2024 */
    public const ZIMBABWE_DOLLAR = 'ZWL';

    /** @var array<string> */
    private static array $names = [
        self::AFGHANI => 'Afghani',
        self::AFGHANI_OLD => 'Afghani (Old)',
        self::ALGERIAN_DINAR => 'Algerian Dinar',
        self::ANGOLAN_KWANZA_REAJUSTADO => 'Angolan Kwanza Reajustado',
        self::ARGENTINE_PESO => 'Argentine Peso',
        self::ARMENIAN_DRAM => 'Armenian Dram',
        self::ARUBAN_FLORIN => 'Aruban Florin',
        self::AUSTRALIAN_DOLLAR => 'Australian Dollar',
        self::AUSTRIAN_SCHILLING => 'Austrian Schilling',
        self::AZERBAIJANIAN_MANAT => 'Azerbaijanian Manat',
        self::AZERBAIJAN_MANAT_OLD => 'Azerbaijanian Manat (Old)',
        self::BAHAMIAN_DOLLAR => 'Bahamian Dollar',
        self::BAHRAINI_DINAR => 'Bahraini Dinar',
        self::BAHT => 'Baht',
        self::BALBOA => 'Balboa',
        self::BARBADOS_DOLLAR => 'Barbados Dollar',
        self::BELARUSIAN_RUBLE => 'Belarusian Ruble',
        self::BELARUSIAN_RUBLE_OLD => 'Belarusian Ruble (Old)',
        self::BELGIAN_FRANC => 'Belgian Franc',
        self::BELIZE_DOLLAR => 'Belize Dollar',
        self::BERMUDIAN_DOLLAR => 'Bermudian Dollar',
        self::BITCOIN => 'Bitcoin',
        self::BOLIVAR_OLD => 'Bolívar',
        self::BOLIVIANO => 'Boliviano',
        self::BRAZILIAN_CRUZEIRO => 'Brazilian Cruzeiro',
        self::BRAZILIAN_REAL => 'Brazilian Real',
        self::BRUNEI_DOLLAR => 'Brunei Dollar',
        self::BULGARIAN_LEV => 'Bulgarian Lev',
        self::BURUNDI_FRANC => 'Burundi Franc',
        self::CABO_VERDE_ESCUDO => 'Cabo Verde Escudo',
        self::CANADIAN_DOLLAR => 'Canadian Dollar',
        self::CAYMAN_ISLANDS_DOLLAR => 'Cayman Islands Dollar',
        self::CFA_FRANC_BCEAO => 'CFA Franc BCEAO',
        self::CFA_FRANC_BEAC => 'CFA Franc BEAC',
        self::CFP_FRANC => 'CFP Franc',
        self::CHILEAN_PESO => 'Chilean Peso',
        self::COLOMBIAN_PESO => 'Colombian Peso',
        self::COMORO_FRANC => 'Comoro Franc',
        self::CONGOLESE_FRANC => 'Congolese Franc',
        self::CONVERTIBLE_MARK => 'Convertible Mark',
        self::CORDOBA_ORO => 'Cordoba Oro',
        self::COSTA_RICAN_COLON => 'Costa Rican Colon',
        self::CUBAN_PESO => 'Cuban Peso',
        self::CYPRUS_POUND => 'Cyprus Pound',
        self::CZECH_KORUNA => 'Czech Koruna',
        self::DALASI => 'Dalasi',
        self::DANISH_KRONE => 'Danish Krone',
        self::DENAR => 'Denar',
        self::DEUTSCHE_MARK => 'Deutsche Mark',
        self::DJIBOUTI_FRANC => 'Djibouti Franc',
        self::DOBRA => 'Dobra',
        self::DOBRA_OLD => 'Dobra (Old)',
        self::DOMINICAN_PESO => 'Dominican Peso',
        self::DONG => 'Dong',
        self::DUTCH_GUILDER => 'Dutch Guilder',
        self::EAST_CARIBBEAN_DOLLAR => 'East Caribbean Dollar',
        self::EAST_GERMAN_MARK => 'East German Mark',
        self::EGYPTIAN_POUND => 'Egyptian Pound',
        self::EL_SALVADOR_COLON => 'El Salvador Colon',
        self::ESTONIAN_KROON => 'Estonian Kroon',
        self::ETHIOPIAN_BIRR => 'Ethiopian Birr',
        self::EURO => 'Euro',
        self::FALKLAND_ISLANDS_POUND => 'Falkland Islands Pound',
        self::FIJI_DOLLAR => 'Fiji Dollar',
        self::FINNISH_MARKA => 'Finnish Markka',
        self::FORINT => 'Forint',
        self::FRENCH_FRANC => 'French Franc',
        self::GHANA_CEDI => 'Ghana Cedi',
        self::GHANA_CEDI_OLD => 'Ghana Cedi (Old)',
        self::GIBRALTAR_POUND => 'Gibraltar Pound',
        self::GOLD => 'Gold',
        self::GOURDE => 'Gourde',
        self::GREEK_DRACHMA => 'Greek Drachma',
        self::GUARANI => 'Guarani',
        self::GUINEA_FRANC => 'Guinea Franc',
        self::GUYANA_DOLLAR => 'Guyana Dollar',
        self::HONG_KONG_DOLLAR => 'Hong Kong Dollar',
        self::HRYVNIA => 'Hryvnia',
        self::ICELAND_KRONA => 'Iceland Krona',
        self::INDIAN_RUPEE => 'Indian Rupee',
        self::IRANIAN_RIAL => 'Iranian Rial',
        self::IRAQI_DINAR => 'Iraqi Dinar',
        self::IRISH_POUNT => 'Irish Pound',
        self::ITALIAN_LIRA => 'Italian Lira',
        self::JAMAICAN_DOLLAR => 'Jamaican Dollar',
        self::JORDANIAN_DINAR => 'Jordanian Dinar',
        self::KENYAN_SHILLING => 'Kenyan Shilling',
        self::KINA => 'Kina',
        self::KIP => 'Kip',
        self::KUNA => 'Kuna',
        self::KUWAITI_DINAR => 'Kuwaiti Dinar',
        self::KWANZA => 'Kwanza',
        self::KYAT => 'Kyat',
        self::LARI => 'Lari',
        self::LATVIAN_LATS => 'Latvian Lats',
        self::LEBANESE_POUND => 'Lebanese Pound',
        self::LEK => 'Lek',
        self::LEMPIRA => 'Lempira',
        self::LEONE => 'Leone',
        self::LEONE_OLD => 'Leone (Old)',
        self::LIBERIAN_DOLLAR => 'Liberian Dollar',
        self::LIBYAN_DINAR => 'Libyan Dinar',
        self::LILANGENI => 'Lilangeni',
        self::LITHUANIAN_LITAS => 'Lithuanian Litas',
        self::LOTI => 'Loti',
        self::LUXEMBOURG_FRANC => 'Luxembourg Franc',
        self::MALAGASY_ARIARY => 'Malagasy Ariary',
        self::MALAGASY_FRANC => 'Malagasy Franc',
        self::MALAWI_KWACHA => 'Malawi Kwacha',
        self::MALAYSIAN_RINGGIT => 'Malaysian Ringgit',
        self::MALTESE_LIRA => 'Maltese Lira',
        self::MAURITIUS_RUPEE => 'Mauritius Rupee',
        self::MEXICAN_PESO => 'Mexican Peso',
        self::MEXICAN_PESO_OLD => 'Mexican Peso (Old)',
        self::MOLDOVAN_LEU => 'Moldovan Leu',
        self::MOROCCAN_DIRHAM => 'Moroccan Dirham',
        self::MOZAMBIQUE_METICAL => 'Mozambique Metical',
        self::MOZAMBIQUE_METICAL_OLD => 'Mozambique Metical (Old)',
        self::NAIRA => 'Naira',
        self::NAKFA => 'Nakfa',
        self::NAMIBIA_DOLLAR => 'Namibia Dollar',
        self::NEPALESE_RUPEE => 'Nepalese Rupee',
        self::NETHERLANDS_ANTILLEAN_GUILDER => 'Netherlands Antillean Guilder',
        self::NEW_ISRAELI_SHEQEL => 'New Israeli Sheqel',
        self::NEW_TAIWAN_DOLLAR => 'New Taiwan Dollar',
        self::NEW_ZEALAND_DOLLAR => 'New Zealand Dollar',
        self::NGULTRUM => 'Ngultrum',
        self::NORTH_KOREAN_WON => 'North Korean Won',
        self::NORWEGIAN_KRONE => 'Norwegian Krone',
        self::OUGUIYA => 'Ouguiya',
        self::OUGUIYA_OLD => 'Ouguiya (Old)',
        self::PAANGA => 'Pa’anga',
        self::PAKISTAN_RUPEE => 'Pakistan Rupee',
        self::PALLADIUM => 'Palladium',
        self::PATACA => 'Pataca',
        self::PERUVIAN_INTI => 'Peruvian Inti',
        self::PESO_CONVERTIBLE => 'Peso Convertible',
        self::PESO_URUGUAYO => 'Peso Uruguayo',
        self::PHILIPPINE_PESO => 'Philippine Peso',
        self::PLATINUM => 'Platinum',
        self::POLISH_ZLOTY_OLD => 'Polish Zloty (Old)',
        self::PORTUGUESE_ESCUDO => 'Portuguese Escudo',
        self::POUND_STERLING => 'Pound Sterling',
        self::PULA => 'Pula',
        self::QATARI_RIAL => 'Qatari Rial',
        self::QUETZAL => 'Quetzal',
        self::RAND => 'Rand',
        self::RIAL_OMANI => 'Rial Omani',
        self::RIEL => 'Riel',
        self::ROMANIAN_LEU => 'Romanian Leu',
        self::ROMANIAN_LEU_OLD => 'Romanian Leu (Old)',
        self::RUFIYAA => 'Rufiyaa',
        self::RUPIAH => 'Rupiah',
        self::RUSSIAN_RUBLE => 'Russian Ruble',
        self::RWANDA_FRANC => 'Rwanda Franc',
        self::SAINT_HELENA_POUND => 'Saint Helena Pound',
        self::SAUDI_RIYAL => 'Saudi Riyal',
        self::SERBIAN_DINAR => 'Serbian Dinar',
        self::SEYCHELLES_RUPEE => 'Seychelles Rupee',
        self::SILVER => 'Silver',
        self::SINGAPORE_DOLLAR => 'Singapore Dollar',
        self::SLOVAK_KORUNA => 'Slovak Koruna',
        self::SLOVENIAN_TOLAR => 'Slovenian Tolar',
        self::SOL => 'Sol',
        self::SOLOMON_ISLANDS_DOLLAR => 'Solomon Islands Dollar',
        self::SOM => 'Som',
        self::SOMALI_SHILLING => 'Somali Shilling',
        self::SOMONI => 'Somoni',
        self::SOUTH_SUDANESE_POUND => 'South Sudanese Pound',
        self::SOVIET_RUBLE => 'Soviet Ruble',
        self::SPANISH_PESETA => 'Spanish Peseta',
        self::SRI_LANKA_RUPEE => 'Sri Lanka Rupee',
        self::SUDANESE_DINAR => 'Sudanese Dinar',
        self::SUDANESE_POUND => 'Sudanese Pound',
        self::SURINAM_DOLLAR => 'Surinam Dollar',
        self::SWEDISH_KRONA => 'Swedish Krona',
        self::SWISS_FRANC => 'Swiss Franc',
        self::SYRIAN_POUND => 'Syrian Pound',
        self::TAKA => 'Taka',
        self::TALA => 'Tala',
        self::TANZANIAN_SHILLING => 'Tanzanian Shilling',
        self::TENGE => 'Tenge',
        self::TRINIDAD_AND_TOBAGO_DOLLAR => 'Trinidad and Tobago Dollar',
        self::TUGRIK => 'Tugrik',
        self::TUNISIAN_DINAR => 'Tunisian Dinar',
        self::TURKISH_LIRA => 'Turkish Lira',
        self::TURKISH_LIRA_OLD => 'Turkish Lira (Old)',
        self::TURKMENISTAN_NEW_MANAT => 'Turkmenistan New Manat',
        self::UAE_DIRHAM => 'UAE Dirham',
        self::UGANDA_SHILLING => 'Uganda Shilling',
        self::UGANDA_SHILLING_OLD => 'Uganda Shilling (Old)',
        self::US_DOLLAR => 'US Dollar',
        self::UZBEKISTAN_SUM => 'Uzbekistan Sum',
        self::VATU => 'Vatu',
        self::WON => 'Won',
        self::YEMENI_RIAL => 'Yemeni Rial',
        self::YEN => 'Yen',
        self::YUAN_RENMINBI => 'Yuan Renminbi',
        self::YUGOSLAV_DINAR => 'Yugoslav Dinar',
        self::ZAMBIAN_KWACHA => 'Zambian Kwacha',
        self::ZAMBIAN_KWACHA_OLD => 'Zambian Kwacha (Old)',
        self::ZIMBABWE_DOLLAR => 'Zimbabwe Dollar',
        self::ZLOTY => 'Zloty',
    ];

    /** @var array<string> */
    private static array $idents = [
        self::AFGHANI => 'afghani',
        self::AFGHANI_OLD => 'afghani-old',
        self::ALGERIAN_DINAR => 'algerian-dinar',
        self::ANGOLAN_KWANZA_REAJUSTADO => 'angolan-kwanza-reajustado',
        self::ARGENTINE_PESO => 'argentine-peso',
        self::ARMENIAN_DRAM => 'armenian-dram',
        self::ARUBAN_FLORIN => 'aruban-florin',
        self::AUSTRALIAN_DOLLAR => 'australian-dollar',
        self::AUSTRIAN_SCHILLING => 'austrian-schilling',
        self::AZERBAIJANIAN_MANAT => 'azerbaijanian-manat',
        self::AZERBAIJAN_MANAT_OLD => 'azerbaijan-manat-old',
        self::BAHAMIAN_DOLLAR => 'bahamian-dollar',
        self::BAHRAINI_DINAR => 'bahraini-dinar',
        self::BAHT => 'baht',
        self::BALBOA => 'balboa',
        self::BARBADOS_DOLLAR => 'barbados-dollar',
        self::BELARUSIAN_RUBLE => 'belarusian-ruble',
        self::BELARUSIAN_RUBLE_OLD => 'belarusian-ruble-old',
        self::BELGIAN_FRANC => 'belgian-franc',
        self::BELIZE_DOLLAR => 'belize-dollar',
        self::BERMUDIAN_DOLLAR => 'bermudian-dollar',
        self::BITCOIN => 'bitcoin',
        self::BOLIVAR_OLD => 'bolivar',
        self::BOLIVIANO => 'boliviano',
        self::BRAZILIAN_CRUZEIRO => 'brazilian-cruzeiro',
        self::BRAZILIAN_REAL => 'brazilian-real',
        self::BRUNEI_DOLLAR => 'brunei-dollar',
        self::BULGARIAN_LEV => 'bulgarian-lev',
        self::BURUNDI_FRANC => 'burundi-franc',
        self::CABO_VERDE_ESCUDO => 'cabo-verde-escudo',
        self::CANADIAN_DOLLAR => 'canadian-dollar',
        self::CAYMAN_ISLANDS_DOLLAR => 'cayman-islands-dollar',
        self::CFA_FRANC_BCEAO => 'cfa-franc-bceao',
        self::CFA_FRANC_BEAC => 'cfa-franc-beac',
        self::CFP_FRANC => 'cfp-franc',
        self::CHILEAN_PESO => 'chilean-peso',
        self::COLOMBIAN_PESO => 'colombian-peso',
        self::COMORO_FRANC => 'comoro-franc',
        self::CONGOLESE_FRANC => 'congolese-franc',
        self::CONVERTIBLE_MARK => 'convertible-mark',
        self::CORDOBA_ORO => 'cordoba-oro',
        self::COSTA_RICAN_COLON => 'costa-rican-colon',
        self::CUBAN_PESO => 'cuban-peso',
        self::CYPRUS_POUND => 'cyprus-pound',
        self::CZECH_KORUNA => 'czech-koruna',
        self::DALASI => 'dalasi',
        self::DANISH_KRONE => 'danish-krone',
        self::DENAR => 'denar',
        self::DEUTSCHE_MARK => 'deutsche-mark',
        self::DJIBOUTI_FRANC => 'djibouti-franc',
        self::DOBRA => 'dobra',
        self::DOBRA_OLD => 'dobra-old',
        self::DOMINICAN_PESO => 'dominican-peso',
        self::DONG => 'dong',
        self::DUTCH_GUILDER => 'dutch-guilder',
        self::EAST_CARIBBEAN_DOLLAR => 'east-caribbean-dollar',
        self::EAST_GERMAN_MARK => 'east-german-mark',
        self::EGYPTIAN_POUND => 'egyptian-pound',
        self::EL_SALVADOR_COLON => 'el-salvador-colon',
        self::ESTONIAN_KROON => 'estonian-kroon',
        self::ETHIOPIAN_BIRR => 'ethiopian-birr',
        self::EURO => 'euro',
        self::FALKLAND_ISLANDS_POUND => 'falkland-islands-pound',
        self::FIJI_DOLLAR => 'fiji-dollar',
        self::FINNISH_MARKA => 'finnish-marka',
        self::FORINT => 'forint',
        self::FRENCH_FRANC => 'french-franc',
        self::GHANA_CEDI => 'ghana-cedi',
        self::GHANA_CEDI_OLD => 'ghana-cedi-old',
        self::GIBRALTAR_POUND => 'gibraltar-pound',
        self::GOLD => 'gold',
        self::GOURDE => 'gourde',
        self::GREEK_DRACHMA => 'greek-drachma',
        self::GUARANI => 'guarani',
        self::GUINEA_FRANC => 'guinea-franc',
        self::GUYANA_DOLLAR => 'guyana-dollar',
        self::HONG_KONG_DOLLAR => 'hong-kong-dollar',
        self::HRYVNIA => 'hryvnia',
        self::ICELAND_KRONA => 'iceland-krona',
        self::INDIAN_RUPEE => 'indian-rupee',
        self::IRANIAN_RIAL => 'iranian-rial',
        self::IRAQI_DINAR => 'iraqi-dinar',
        self::IRISH_POUNT => 'irish-pound',
        self::ITALIAN_LIRA => 'italian-lira',
        self::JAMAICAN_DOLLAR => 'jamaican-dollar',
        self::JORDANIAN_DINAR => 'jordanian-dinar',
        self::KENYAN_SHILLING => 'kenyan-shilling',
        self::KINA => 'kina',
        self::KIP => 'kip',
        self::KUNA => 'kuna',
        self::KUWAITI_DINAR => 'kuwaiti-dinar',
        self::KWANZA => 'kwanza',
        self::KYAT => 'kyat',
        self::LARI => 'lari',
        self::LATVIAN_LATS => 'latvian-lats',
        self::LEBANESE_POUND => 'lebanese-pound',
        self::LEK => 'lek',
        self::LEMPIRA => 'lempira',
        self::LEONE => 'leone',
        self::LEONE_OLD => 'leone-old',
        self::LIBERIAN_DOLLAR => 'liberian-dollar',
        self::LIBYAN_DINAR => 'libyan-dinar',
        self::LILANGENI => 'lilangeni',
        self::LITHUANIAN_LITAS => 'lithuanian-litas',
        self::LOTI => 'loti',
        self::LUXEMBOURG_FRANC => 'luxembourg-franc',
        self::MALAGASY_ARIARY => 'malagasy-ariary',
        self::MALAGASY_FRANC => 'malagasy-franc',
        self::MALAWI_KWACHA => 'malawi-kwacha',
        self::MALAYSIAN_RINGGIT => 'malaysian-ringgit',
        self::MALTESE_LIRA => 'maltese-lira',
        self::MAURITIUS_RUPEE => 'mauritius-rupee',
        self::MEXICAN_PESO => 'mexican-peso',
        self::MEXICAN_PESO_OLD => 'mexican-peso-old',
        self::MOLDOVAN_LEU => 'moldovan-leu',
        self::MOROCCAN_DIRHAM => 'moroccan-dirham',
        self::MOZAMBIQUE_METICAL => 'mozambique-metical',
        self::MOZAMBIQUE_METICAL_OLD => 'mozambique-metical-old',
        self::NAIRA => 'naira',
        self::NAKFA => 'nakfa',
        self::NAMIBIA_DOLLAR => 'namibia-dollar',
        self::NEPALESE_RUPEE => 'nepalese-rupee',
        self::NETHERLANDS_ANTILLEAN_GUILDER => 'netherlands-antillean-guilder',
        self::NEW_ISRAELI_SHEQEL => 'new-israeli-sheqel',
        self::NEW_TAIWAN_DOLLAR => 'new-taiwan-dollar',
        self::NEW_ZEALAND_DOLLAR => 'new-zealand-dollar',
        self::NGULTRUM => 'ngultrum',
        self::NORTH_KOREAN_WON => 'north-korean-won',
        self::NORWEGIAN_KRONE => 'norwegian-krone',
        self::OUGUIYA => 'ouguiya',
        self::OUGUIYA_OLD => 'ouguiya-old',
        self::PAANGA => 'paanga',
        self::PAKISTAN_RUPEE => 'pakistan-rupee',
        self::PALLADIUM => 'palladium',
        self::PATACA => 'pataca',
        self::PERUVIAN_INTI => 'peruvian-inti',
        self::PESO_CONVERTIBLE => 'peso-convertible',
        self::PESO_URUGUAYO => 'peso-uruguayo',
        self::PHILIPPINE_PESO => 'philippine-peso',
        self::PLATINUM => 'platinum',
        self::POLISH_ZLOTY_OLD => 'polish-zloty-old',
        self::PORTUGUESE_ESCUDO => 'portuguese-escudo',
        self::POUND_STERLING => 'pound-sterling',
        self::PULA => 'pula',
        self::QATARI_RIAL => 'qatari-rial',
        self::QUETZAL => 'quetzal',
        self::RAND => 'rand',
        self::RIAL_OMANI => 'rial-omani',
        self::RIEL => 'riel',
        self::ROMANIAN_LEU => 'romanian-leu',
        self::ROMANIAN_LEU_OLD => 'romanian-leu-old',
        self::RUFIYAA => 'rufiyaa',
        self::RUPIAH => 'rupiah',
        self::RUSSIAN_RUBLE => 'russian-ruble',
        self::RWANDA_FRANC => 'rwanda-franc',
        self::SAINT_HELENA_POUND => 'saint-helena-pound',
        self::SAUDI_RIYAL => 'saudi-riyal',
        self::SERBIAN_DINAR => 'serbian-dinar',
        self::SEYCHELLES_RUPEE => 'seychelles-rupee',
        self::SILVER => 'silver',
        self::SINGAPORE_DOLLAR => 'singapore-dollar',
        self::SLOVAK_KORUNA => 'slovak-koruna',
        self::SLOVENIAN_TOLAR => 'slovenian-tolar',
        self::SOL => 'sol',
        self::SOLOMON_ISLANDS_DOLLAR => 'solomon-islands-dollar',
        self::SOM => 'som',
        self::SOMALI_SHILLING => 'somali-shilling',
        self::SOMONI => 'somoni',
        self::SOUTH_SUDANESE_POUND => 'south-sudanese-pound',
        self::SOVIET_RUBLE => 'soviet-ruble',
        self::SPANISH_PESETA => 'spanish-peseta',
        self::SRI_LANKA_RUPEE => 'sri-lanka-rupee',
        self::SUDANESE_DINAR => 'sudanese-dinar',
        self::SUDANESE_POUND => 'sudanese-pound',
        self::SURINAM_DOLLAR => 'surinam-dollar',
        self::SWEDISH_KRONA => 'swedish-krona',
        self::SWISS_FRANC => 'swiss-franc',
        self::SYRIAN_POUND => 'syrian-pound',
        self::TAKA => 'taka',
        self::TALA => 'tala',
        self::TANZANIAN_SHILLING => 'tanzanian-shilling',
        self::TENGE => 'tenge',
        self::TRINIDAD_AND_TOBAGO_DOLLAR => 'trinidad-and-tobago-dollar',
        self::TUGRIK => 'tugrik',
        self::TUNISIAN_DINAR => 'tunisian-dinar',
        self::TURKISH_LIRA => 'turkish-lira',
        self::TURKISH_LIRA_OLD => 'turkish-lira-old',
        self::TURKMENISTAN_NEW_MANAT => 'turkmenistan-new-manat',
        self::UAE_DIRHAM => 'uae-dirham',
        self::UGANDA_SHILLING => 'uganda-shilling',
        self::UGANDA_SHILLING_OLD => 'uganda-shilling-old',
        self::US_DOLLAR => 'us-dollar',
        self::UZBEKISTAN_SUM => 'uzbekistan-sum',
        self::VATU => 'vatu',
        self::WON => 'won',
        self::YEMENI_RIAL => 'yemeni-rial',
        self::YEN => 'yen',
        self::YUAN_RENMINBI => 'yuan-renminbi',
        self::YUGOSLAV_DINAR => 'yugoslav-dinar',
        self::ZAMBIAN_KWACHA => 'zambian-kwacha',
        self::ZAMBIAN_KWACHA_OLD => 'zambian-kwacha-old',
        self::ZIMBABWE_DOLLAR => 'zimbabwe-dollar',
        self::ZLOTY => 'zloty',
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

    public function getIdent(): string
    {
        return self::$idents[$this->getValue()];
    }

    public function getByIdent(string $ident): self
    {
        /** @var string $code */
        $code = array_search($ident, self::$idents, true);

        return self::get($code);
    }

    public static function validateValue(string &$value): bool
    {
        $value = strtoupper($value);

        return parent::validateValue($value);
    }

}
