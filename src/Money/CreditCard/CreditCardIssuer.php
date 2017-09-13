<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Money\CreditCard;

class CreditCardIssuer extends \Dogma\Enum\StringEnum
{

    public const AMERICAN_EXPRESS = 'AMEX';
    public const BANKCARD = 'BACA';
    public const CHINA_UNIONPAY = 'CHUN';
    public const DINERS_CLUB_CARTE_BLANCHE = 'DCCB';
    public const DINERS_CLUB_ENROUTE = 'DCEN';
    public const DINERS_CLUB_INTERNATIONAL = 'DCIN';
    public const DINERS_CLUB_UNITED_STATES_AND_CANADA = 'DCUS';
    public const DISCOVER_CARD = 'DISC';
    public const INTERPAYMENT = 'INTE';
    public const INSTAPAYMENT = 'INST';
    public const JCB = 'JCBX';
    public const LASER = 'LASR';
    public const MAESTRO = 'MAES';
    public const DANKORT = 'DANK';
    public const MIR = 'MIRX';
    public const MASTERCARD = 'MAST';
    public const SOLO = 'SOLO';
    public const SWITCH = 'SWIT';
    public const VISA = 'VISA';
    public const UATP = 'UATP';
    public const VERVE = 'VERV';
    public const CARDGUARD_EAD_BG_ILS = 'CAGA';

    /** @var string[] */
    private static $names = [
        self::AMERICAN_EXPRESS => 'American Express',
        self::BANKCARD => 'Bankcard',
        self::CHINA_UNIONPAY => 'China UnionPay',
        self::DINERS_CLUB_CARTE_BLANCHE => 'Diners Club Carte Blanche',
        self::DINERS_CLUB_ENROUTE => 'Diners Club enRoute',
        self::DINERS_CLUB_INTERNATIONAL => 'Diners Club International',
        self::DINERS_CLUB_UNITED_STATES_AND_CANADA => 'Diners Club United States & Canada',
        self::DISCOVER_CARD => 'Discover Card',
        self::INTERPAYMENT => 'InterPayment',
        self::INSTAPAYMENT => 'InstaPayment',
        self::JCB => 'JCB',
        self::LASER => 'Laser',
        self::MAESTRO => 'Maestro',
        self::DANKORT => 'Dankort',
        self::MIR => 'MIR',
        self::MASTERCARD => 'MasterCard',
        self::SOLO => 'Solo',
        self::SWITCH => 'Switch',
        self::VISA => 'Visa',
        self::UATP => 'UATP',
        self::VERVE => 'Verve',
        self::CARDGUARD_EAD_BG_ILS => 'CardGuard EAD BG ILS',
    ];

    /** @var string[] */
    private static $idents = [
        self::AMERICAN_EXPRESS => 'american-express',
        self::BANKCARD => 'bankcard',
        self::CHINA_UNIONPAY => 'china-unionpay',
        self::DINERS_CLUB_CARTE_BLANCHE => 'diners-club-carte-blanche',
        self::DINERS_CLUB_ENROUTE => 'diners-club-enroute',
        self::DINERS_CLUB_INTERNATIONAL => 'diners-club-international',
        self::DINERS_CLUB_UNITED_STATES_AND_CANADA => 'diners-club-united-states-canada',
        self::DISCOVER_CARD => 'discover-card',
        self::INTERPAYMENT => 'interpayment',
        self::INSTAPAYMENT => 'instapayment',
        self::JCB => 'jcb',
        self::LASER => 'laser',
        self::MAESTRO => 'maestro',
        self::DANKORT => 'dankort',
        self::MIR => 'mir',
        self::MASTERCARD => 'mastercard',
        self::SOLO => 'solo',
        self::SWITCH => 'switch',
        self::VISA => 'visa',
        self::UATP => 'uatp',
        self::VERVE => 'verve',
        self::CARDGUARD_EAD_BG_ILS => 'cardguard-ead-bg-its',
    ];

    public static function validateValue(string &$value): bool
    {
        $value = strtoupper($value);

        return parent::validateValue($value);
    }

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
        return self::get(array_search($ident, self::$idents));
    }

    public function getByCreditCardPrefix(): self
    {
        ///
    }

}
