<?php

namespace Dogma;


/**
 * Money object
 */
class Money extends \Nette\Object {
    
    private $amount;
    private $currency;
    
    
    /**
     * @param number
     * @param Currency
     */
    public function __construct($amount, Currency $currency) {
        if (!is_numeric($amount))
            throw new \InvalidArgumentException("Money: Amount must be a number. " . gettype($amount) . " given.");
        
        $this->amount = (float) $amount;
        $this->currency = $currency;
    }
    
    
    /**
     * @return \Dogma\Currency
     */
    public function getCurrency() {
        return $this->currency;
    }
    
    
    /**
     * @return float
     */
    public function getAmount() {
        return  $this->amount;
    }
    
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->currency . " " . $this->amount;
    }
    
}
