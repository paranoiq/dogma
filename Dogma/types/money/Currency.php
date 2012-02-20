<?php

namespace Dogma;


/**
 * Currency identifier
 * 
 * @property-read $description
 * @property-read $symbol
 */
final class Currency extends Enum {
    
    const USD = 'USD'; // US dolar
    const EUR = 'EUR'; // Euro
    const GBP = 'GBP'; // Britská libra
    const CHF = 'CHF'; // Švýcarský frank
    const JPY = 'JPY'; // Japonský Yen
    const CNY = 'CNY'; // Čínský Yuan
    const CZK = 'CZK'; // Česká koruna
    const PLN = 'PLN'; // Polský zlotý
    const HUF = 'HUF'; // Maďarský forint
    
    
    private static $descr = array(
        self::USD => 'US Dollar',
        self::EUR => 'Euro',
        self::GBP => 'British Pound',
        self::CHF => 'Swiss Franc',
        self::JPY => 'Japanese Yen',
        self::CNY => 'Chinese Yuan',
        self::CZK => 'Czech Crown',
        self::PLN => 'Polish złoty',
        self::HUF => 'Hungarian forint',
    );
    
    private static $symbol = array(
        self::USD => '$',
        self::EUR => '€',
        self::GBP => '£',
        self::CHF => '₣',
        self::JPY => '¥',
        self::CNY => '元',
        self::CZK => 'Kč',
        self::PLN => 'zł',
        self::HUF => 'Ft',
    );
    
    
    /**
     * @return string
     */
    public function getDescription() {
        return self::$descr[$this->value];
    }
    
    
    /**
     * @return string
     */
    public function getSymbol() {
        return self::$symbol[$this->value];
    }
    
}
