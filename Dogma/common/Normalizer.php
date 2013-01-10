<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


/**
 * Type validator/normalizer/formater.
 * Default formats (when formating bool or null, first case is used):
 * - TRUE: true, t, on, yes, y
 * - FALSE: false, f, off, no, n
 * - NULL: null, nil, n/a, na, unknown, undefined
 * - Date: Y-m-d
 * - DateTime: Y-m-d H:i:s
 * - decimal point: .
 * - thousand separator: none
 * - set separator: ,
 *
 * @property-write string[] $true
 * @property-write string[] $false
 * @property-write string[] $null
 * @property-write string $date
 * @property-write string $dateTime
 * @property-write string $decimalPouint
 * @property-write string $thousandSeparator
 * @property-write string $setSeparator
 */
class Normalizer extends \Dogma\Object {


    /** @var array */
    private $formats = array(
        'date' => 'Y-m-d',
        'dateTime' => 'Y-m-d H:i:s',
        'true' => array('TRUE', 't', 'on', 'yes', 'y'),
        'false' => array('FALSE', 'f', 'off', 'no', 'n'),
        'null' => array('NULL', 'nil', 'n/a', 'na', 'unknown', 'undefined'),
        'decimalPoint' => '.',
        'thousandSeparator' => '',
        'setSeparator' => ',',
    );

    /** @var array user types (Enum, Set, Validator…) */
    private $types = array();


    /**
     * @param string
     * @param string
     * @return self
     */
    public function setFormat($option, $format) {
        if (!isset($this->formats[$option]))
            throw new \InvalidArgumentException("Normalizer: Unknown formating option '$option' given.");

        if (is_string($format) && in_array($option, array('true', 'false', 'null'))) {
            $format = explode(',', $format);
        } elseif (is_array($format)) {
            $format = array_values($format);
        }
        $this->formats[$option] = $format;

        return $this;
    }


    /**
     * @param string
     * @param string|string[]
     */
    public function __set($name, $value) {
        if (isset($this->formats[$name])) {
            $this->setFormat($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }


    /**
     * @param string
     * @param object
     */
    public function addType($type) {
        if (is_subclass_of($type, 'Dogma\\Enum')) {
            $this->types[] = array('Enum', $type);

        } elseif (is_subclass_of($type, 'Dogma\\Set')) {
            $this->types[] = array('Set', $type);
        /*
        } elseif (is_subclass_of($type, 'Dogma\\Validator')) {
            $this->types[] = array('Validator', $type);

        } elseif (is_subclass_of($type, 'Dogma\\Regexp')) {
            $this->types[] = array('Regexp', $type);
        */
        } else {
            throw new \InvalidArgumentException("Unsupported type.");
        }
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

        }

        foreach ($this->types as $item) {
            list($type, $name) = $item;

            if ($type === 'Enum' && call_user_func($name . '::isValid', $value)) {
                return call_user_func($name . '::instance', $value);

            } elseif ($type === 'Set') {
                $set = explode($this->setSeparator, $value);
                if (call_user_func($name . '::isValid', $set))
                    return new $name($value);
            }
        }

        return $value;
    }


    /**
     * Detects NULL from string. Returns TRUE on match, FALSE otherwise.
     * @param string
     * @return bool
     */
    public function detectNull($value) {
        if (is_null($value)) return TRUE;

        foreach ($this->formats['null'] as $v) {
            if (preg_match('/^' . preg_quote($v, '/') . '$/iu', $value)) return TRUE;
        }

        return FALSE;
    }


    /**
     * Detects NULL from string. Returns TRUE on success, FALSE otherwise.
     * @param string
     * @return bool
     */
    public function detectBool($value) {
        if (is_bool($value)) return $value;

        foreach ($this->formats['true'] as $v) {
            if (preg_match('/^' . preg_quote($v, '/') . '$/iu', $value)) return TRUE;
        }
        foreach ($this->formats['false'] as $v) {
            if (preg_match('/^' . preg_quote($v, '/') . '$/iu', $value)) return FALSE;
        }

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

        if (!$datetime = DateTime::createFromFormat($this->formats['dateTime'], $value))
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
                    throw new \InvalidArgumentException("Normalizer: Cannot convert value '$value' to Date.");
                return $value;

            case Type::DATETIME:
                $value = $this->detectDateTime($value);
                if ($value === NULL)
                    throw new \InvalidArgumentException("Normalizer: Cannot convert value '$value' to DateTime.");
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

            return $value ? $this->formats['true'][0] : $this->formats['false'][0];

        } elseif (is_null($value)) {
            if (!$nullable)
                throw new \InvalidArgumentException("Normalizer: Null value is not allowed.");

            return $this->formats['null'][0];

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
        return $date->format($this->formats['dateTime']);
    }

}
