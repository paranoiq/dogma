<?php

namespace Dogma;


/**
 * Type validator/normalizer/formater
 * 
 * @todo support enum
 * @todo support set
 */
class Normalizer {
    
    
    /** @var array */
    protected $formats = array(
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i:s',
        'true' => 'TRUE',
        'false' => 'FALSE',
        'null' => 'N/A',
        'decimalPoint' => '.',
        'thousandSeparator' => '');


    /**
     * @param string
     * @param string
     * @return self
     */
    public function setFormat($type, $format) {
        if (!isset($this->formats[$type]))
            throw new \InvalidArgumentException("Normalizer: Unknown formating option '$type' given.");
        
        $this->formats[$type] = $format;
        
        return $this;
    }


    /**
     * Autodetect type and normalize
     * @param string
     * @return mixed
     */
    public function autodetect($value) {
        if (NULL !== $val = $this->detectInt($value)) {
            return $val;
            
        } elseif (NULL !== $val = $this->detectFloat($value)) {
            return $val;
            
        } elseif (NULL !== $val = $this->detectBool($value)) {
            return $val;
            
        } elseif (NULL !== $val = $this->detectDate($value)) {
            return $val;
            
        } elseif (NULL !== $val = $this->detectDateTime($value)) {
            return $val;
            
        } else {
            return $value;
        }
    }


    /**
     * Detects NULL from string. Returns TRUE on success, FALSE otherwise.
     * @param string
     * @return bool
     */
    public function detectNull($value) {
        if (is_null($value)) return TRUE;
        
        return preg_match('/' . preg_quote($this->formats['null'], '/') . '/iu', $value);
    }


    /**
     * Detects NULL from string. Returns TRUE on success, FALSE otherwise.
     * @param string
     * @return bool
     */
    public function detectBool($value) {
        if (is_bool($value)) return $value;

        if (preg_match('/' . preg_quote($this->formats['true'], '/') . '/iu', $value)) return TRUE;
        if (preg_match('/' . preg_quote($this->formats['false'], '/') . '/iu', $value)) return FALSE;
        
        return NULL;
    }
    
    
    /**
     * @param string
     * @return int
     */
    public function detectInt($value) {
        if (is_int($value)) return $value;
        
        if (!preg_match('/^-?[0-9]{1,3}(' . preg_quote($this->formats['thousandSeparator'], '/') . '[0-9]{3})*$/', $value))
            return NULL;
        
        return (int) str_replace($this->formats['thousandSeparator'], '', $value);
    }


    /**
     * @param string
     * @return float
     */
    public function detectFloat($value) {
        if (is_float($value)) return $value;
        if (is_int($value)) return (float) $value;

        if (!preg_match('/^-?[0-9]{1,3}(?:' . 
            preg_quote($this->formats['thousandSeparator'], '/') . '[0-9]{3})*(?:' .
            preg_quote($this->formats['decimalPoint'], '/') . '[0-9]+)?([Ee][+-][0-9]+)?$/', $value))
            return NULL;

        return (float) str_replace(
            array($this->formats['thousandSeparator'], $this->formats['decimalPoint']), array('', '.'), $value);
    }


    /**
     * @param string
     * @return \Dogma\Date
     */
    function detectDate($value) {
        if ($value instanceof Date) return $value;
        if ($value instanceof DateTime) return new Date($value);
        
        if (!$date = Date::createFromFormat($this->formats['date'], $value))
            return NULL;
        
        return $date;
    }


    /**
     * @param string
     * @return \Dogma\DateTime
     */
    function detectDateTime($value) {
        if ($value instanceof Date) return new DateTime($value);
        if ($value instanceof DateTime) return $value;

        if (!$datetime = DateTime::createFromFormat($this->formats['datetime'], $value))
            return NULL;

        return $datetime;
    }
    
    
    /**
     * Normalize value
     * @param string
     * @param string
     * @param bool
     * @return mixed
     */
    public function normalize($value, $type, $nullable = FALSE) {
        if ($nullable && $this->detectNull($value) === TRUE) return NULL;

        switch ($type) {
            case Type::INT:
                $value = $this->detectInt(trim($value));
                if ($value === NULL)
                    throw new \InvalidArgumentException("Normalizer: Cannot convert value '$value' to integer.");
                return $value;

            case Type::FLOAT:
                $value = $this->detectFloat($value);
                if ($value === NULL)
                    throw new \InvalidArgumentException("Normalizer: Cannot convert value '$value' to float.");
                return $value;

            case Type::BOOL:
                $value = $this->detectBool($value);
                if ($value === NULL)
                    throw new \InvalidArgumentException("Normalizer: Cannot convert value '$value' to boolean.");
                return $value;

            case Type::DATE:
                $value = $this->detectDate($value);
                if ($value === NULL)
                    throw new \InvalidArgumentException("Normalizer: Cannot convert value '$value' to boolean.");
                return $value;

            case Type::DATETIME:
                $value = $this->detectDateTime($value);
                if ($value === NULL)
                    throw new \InvalidArgumentException("Normalizer: Cannot convert value '$value' to boolean.");
                return $value;

            case Type::STRING:
                return $value;

            default:
                throw new \InvalidArgumentException("Normalizer: Unsupported type '$type'.");
        }
    }
    
    
    /**
     * Format value to string
     * @todo tolerantní nebo striktní chování?
     * 
     * @param mixed
     * @param string
     * @param bool
     * @return string
     */
    public function format($value, $type = NULL, $nullable = FALSE) {

        if (is_int($value) || is_numeric($value) && $type === Type::INT) {
            return number_format((int) $value, 0, '.', $this->formats['thousandSeparator']);

        } elseif (is_float($value) || is_numeric($value) && $type === Type::FLOAT) {
            return rtrim(rtrim(number_format((float) $value, 20, 
                $this->formats['decimalPoint'], 
                $this->formats['thousandSeparator']), '0'), $this->formats['decimalPoint']);

        } elseif ($value instanceof \Dogma\Date) {
            if (isset($type) && $type !== Type::DATE)
                throw new \InvalidArgumentException("Normalizer: Wrong data type Date. $type expected.");

            return $this->formatDate($value);

        } elseif ($value instanceof \DateTime) {
            if (isset($type) && $type !== Type::DATETIME)
                throw new \InvalidArgumentException("Normalizer: Wrong data type DateTime. $type expected.");

            return $this->formatDateTime($value);

        } elseif (is_bool($value)) {
            if (isset($type) && $type !== Type::BOOL)
                throw new \InvalidArgumentException("Normalizer: Wrong data type bool. $type expected.");

            return $value ? $this->formats['true'] : $this->formats['false'];

        } elseif (is_null($value)) {
            if (!$nullable)
                throw new \InvalidArgumentException("Normalizer: Null value is not allowed.");

            return $this->formats['null'];

        } else {
            return (string) $value;
        }
    }


    /**
     * @param \DateTime
     * @return string
     */
    public function formatDate(\DateTime $date) {
        return $date->format($this->formats['date']);
    }


    /**
     * @param \DateTime
     * @return string
     */
    public function formatDateTime(\DateTime $date) {
        return $date->format($this->formats['datetime']);
    }
    
}
