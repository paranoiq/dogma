<?php

namespace Dogma\Data;

use Dogma\Object\PropertyAccessor;
use Dogma\ArrayObject;
use Dogma\Regexp;
use Dogma\String;
use Dogma\Language\Collator;


/**
 * Object filter. Callback.
 */
class Filter extends \Nette\Object implements IFilter {
    
    
    private static $positiveOperators = array(
        'is', '=', '<', '>', 'between', 'in', 'starts', 'like', 'match', 'contains', 'and', 'or', 'xor');
    private static $negativeOperators = array(
        'is not', '!=', '>=', '<=', 'not between', 'not in', 'not starts', 'not like', 'not match', 'not contains', 'not and', 'not or', 'not xor');
    
    
    const OP_AND = 'and';
    const OP_OR = 'or';
    const OP_XOR = 'xor';
    
    const NOT_AND = 'not and'; // !(a AND b)
    const NOT_OR = 'not or'; // !(a OR b)
    const NOT_XOR = 'not xor'; // !(a XOR b)
    
    
    const IS = 'is'; // IS NULL, IS TRUE, IS FALSE
    const EQUAL = '=';
    const LOWER = '<';
    const GREATER = '>';
    const BETWEEN = 'between';
    const IN = 'in';
    const STARTS = 'starts'; // ???
    const LIKE = 'like';
    const MATCH = 'match'; // MySQL: REGEXP (vždy ci), Postgre: ~
    const CONTAINS = 'contains'; // ARRAY_CONTAINS? MySQL: FIND_IN_SET() > 1
    
    const IS_NOT = 'is not';
    const NOT_EQUAL = '!=';
    const GREATER_EQUAL = '>=';
    const LOWER_EQUAL = '<=';
    const NOT_BETWEEN = 'not between';
    const NOT_IN = 'not in';
    const NOT_STARTS = 'not starts';
    const NOT_LIKE = 'not like';
    const NOT_MATCH = 'not match';
    const NOT_CONTAINS = 'not contains';
    
    
    
    /** @var string */
    protected $function;
    
    /** @var string */
    protected $property;
    
    /** @var string */
    protected $operator;
    
    /** @var bool */
    protected $negative;
    
    /** @var mixed|array(mixed) */
    protected $counterpart;
    
    /** @var string|Collator */
    protected $collation;
    
    
    /**
     * @param string
     * @param string (optional)
     * @param mixed
     * @param string|Collator
     */
    public function __construct($property, $operator, $counterpart = NULL, $collation = NULL) {
        if (!is_string($operator)) {
            $collation = $counterpart;
            $counterpart = $operator;
            
            if ($counterpart instanceof Regexp) {
                $operator = self::MATCH;
            } elseif (is_array($counterpart) || $counterpart instanceof ArrayObject) {
                $operator = self::IN;
            } elseif (is_bool($counterpart) || is_null($counterpart)) {
                $operator = self::IS;
            } else {
                $operator = self::EQUAL;
            }
        }
        
        $operator = $this->normalizeOperator($operator);
        
        switch ($operator) {
        
        case self::IS:
            if (!is_bool($counterpart) && !is_null($counterpart))
                throw new \InvalidArgumentException('Filter: Operator IS expects counterpart of TRUE, FALSE or NULL.');
            break;
        
        case self::BETWEEN:
            if ((!is_array($counterpart) && !$counterpart instanceof ArrayObject) || count($counterpart) != 2)
                throw new \InvalidArgumentException('Filter: Operator BETWEEN expects array of minimal and maximal value.');
            break;
            
        case self::MATCH:
            if (is_string($counterpart)) {
                $counterpart = new Regexp($counterpart);
            } elseif (!$counterpart instanceof Regexp) {
                throw new \InvalidArgumentException('Filter: Parameter of MATCH operator must be a valid regular expression.');
            }
            break;
            
        case self::IN:
            if (is_array($counterpart)) {
                $counterpart = new ArrayObject($counterpart);
            } elseif (!$counterpart instanceof ArrayObject) {
                throw new \InvalidArgumentException('Filter: Parameter of IN operator must be an array.');
            }
            break;
            
        default:
            if (!is_scalar($counterpart) && !($counterpart instanceof \DateTime))
                throw new \InvalidArgumentException('Filter: Filter parameter must be a scalar value.');
        }
        
        $this->property = $property;
        $this->operator = $operator;
        $this->counterpart = $counterpart;
        $this->setCollation($collation);
    }
    
    
    /**
     * Set collation
     * @param string
     * @return Filter
     */
    public function setCollation($collation) {
        $this->collation = $collation;
        return $this;
    }


    /**
     * Convert negative operator to positive
     *
     * @param string
     * @return string
     */
    private function normalizeOperator($operator) {
        
        if (arr(self::$positiveOperators)->contains($operator)) {
            return $operator;
            
        } elseif ($key = arr(self::$negativeOperators)->find($operator) !== FALSE) {
            $this->negative = TRUE;
            return self::$positiveOperators[$key];
            
        } else {
            foreach (self::$positiveOperators as $op) {
                if ($operator === ~$op) {
                    $this->negative = TRUE;
                    return $op;
                }
            }
        }
        
        throw new \InvalidArgumentException("FIlter: Invalid operator.");
    }


    /**
     * @return string
     */
    public function getProperty() {
        return $this->property;
    }


    /**
     * @return string
     */
    public function getOperator() {
        return $this->operator;
    }


    /**
     * @return bool
     */
    public function isNegative() {
        return $this->negative;
    }


    /**
     * @return mixed
     */
    public function getCounterpart() {
        return $this->counterpart;
    }


    /**
     * @return Collator|string
     */
    public function getCollation() {
        return $this->collation;
    }
    
    
    /**
     * Apply filter on object and decide if it passes. Used as callback for arary_filter() or FilterIterator.
     * @param  object|array
     * @return bool
     */
    public function __invoke($object) {
        return $this->test($object);
    }
    
    
    /**
     * Test an object if it matches the filter.
     * @param  object|array
     * @return bool
     */
    public function test($object) {
        try {
            return $this->testValue(PropertyAccessor::getValue($object, $this->property));
        } catch (\Exception $e) {
            /// určitě?
            return FALSE;
        }
    }


    /**
     * @param bool
     * @return bool
     */
    private function decide($val) {
        if ($this->negative) {
            return !$val;
        } else {
            return $val;
        }
    }


    /**
     * @param \DateTime|array|string
     * @return \Dogma\ArrayObject|\Dogma\String
     */
    private function normalizeValue($value) {
        if (is_string($value)) {
            return new String($value);
            
        } elseif ($value instanceof \Dogma\Date) {
            return $value->format('Y-m-d');
            
        } elseif ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
            
        } elseif (is_array($value)) {
            return new ArrayObject($value);
            
        } else {
            return $value;
        }
    }


    /**
     * Test a value against filter condition.
     *
     * @param mixed
     * @return bool
     */
    public function testValue($value) {
        $value = $this->normalizeValue($value);
        
        switch ($this->operator) {
        
        case self::EQUAL:
            return $this->decide($value instanceof String
                ? $value->equalsTo($this->counterpart, $this->collation)
                : $value === $this->counterpart);
            
        case self::LOWER:
            return $this->decide($value instanceof String
                ? $value->compareTo($this->counterpart, $this->collation) === 1
                : $value < $this->counterpart);
            
        case self::GREATER:
            return $this->decide($value instanceof String
                ? $value->compareTo($this->counterpart, $this->collation) === -1
                : $value > $this->counterpart);
            
        case self::BETWEEN:
            return $this->decide($value instanceof String
                ? $value->compareTo($this->counterpart[0], $this->collation) === 1
                    && $value->compareTo($this->counterpart[1], $this->collation) === -1
                : $value >= $this->counterpart[0] && $value <= $this->counterpart[1]);
            
        case self::IN:
            return $this->decide($this->counterpart->contains($value, $this->collation));
            
        case self::STARTS:
            ///return $this->decide(mb_strtolower(mb_substr($value, 0, mb_strlen($this->value))) === mb_strtolower($this->value));
            
        case self::LIKE:
            ///
            break;
        
        case self::MATCH:
            return $this->decide($this->counterpart->match($value));
            
        case self::CONTAINS:
            return $this->decide($value->contains($this->counterpart, $this->collation));
            
        
        }
    }
    
}
