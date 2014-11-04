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
 * - true: true, t, on, yes, y
 * - false: false, f, off, no, n
 * - null: null, nil, n/a, na, unknown, undefined
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
 * @property-write string $decimalPoint
 * @property-write string $thousandSeparator
 * @property-write string $setSeparator
 */
class Normalizer extends \Dogma\Object
{


    /** @var string[] */
    private $formats = [
        'date' => 'Y-m-d',
        'dateTime' => 'Y-m-d H:i:s',
        'true' => ['true', 't', 'on', 'yes', 'y'],
        'false' => ['false', 'f', 'off', 'no', 'n'],
        'null' => ['null', 'nil', 'n/a', 'na', 'unknown', 'undefined'],
        'decimalPoint' => '.',
        'thousandSeparator' => '',
        'setSeparator' => ',',
    ];

    /** @var string[] user types (Enum, Set, Validator…) */
    private $types = [];

    /**
     * @param string
     * @param string
     */
    public function setFormat($option, $format)
    {
        if (!isset($this->formats[$option])) {
            throw new \InvalidArgumentException(sprintf('Normalizer: Unknown formating option \'%s\' given.', $option));
        }

        if (is_string($format) && in_array($option, ['true', 'false', 'null'])) {
            $format = explode(',', $format);
        } elseif (is_array($format)) {
            $format = array_values($format);
        }
        $this->formats[$option] = $format;
    }

    /**
     * @param string
     * @param string|string[]
     */
    public function __set($name, $value)
    {
        if (isset($this->formats[$name])) {
            $this->setFormat($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @param object
     */
    public function addType($type)
    {
        if (is_subclass_of($type, 'Dogma\\Enum')) {
            $this->types[] = ['Enum', $type];

        } elseif (is_subclass_of($type, 'Dogma\\Set')) {
            $this->types[] = ['Set', $type];
        /*
        } elseif (is_subclass_of($type, 'Dogma\\Validator')) {
            $this->types[] = ['Validator', $type];

        } elseif (is_subclass_of($type, 'Dogma\\Regexp')) {
            $this->types[] = ['Regexp', $type];
        */
        } else {
            throw new \InvalidArgumentException('Unsupported type.');
        }
    }

    /**
     * Autodetect type and normalize
     * @param string
     * @return mixed
     */
    public function autodetect($value)
    {
        if (null !== $val = $this->detectInt($value)) {
            return $val;

        } elseif (null !== $val = $this->detectFloat($value)) {
            return $val;

        } elseif (null !== $val = $this->detectBool($value)) {
            return $val;

        } elseif (null !== $val = $this->detectDate($value)) {
            return $val;

        } elseif (null !== $val = $this->detectDateTime($value)) {
            return $val;

        }

        foreach ($this->types as $item) {
            list($type, $name) = $item;

            if ($type === 'Enum' && call_user_func($name . '::isValid', $value)) {
                return call_user_func($name . '::instance', $value);

            } elseif ($type === 'Set') {
                $set = explode($this->setSeparator, $value);
                if (call_user_func($name . '::isValid', $set)) {
                    return new $name($value);
                }
            }
        }

        return $value;
    }

    /**
     * Detects null from string. Returns true on match, false otherwise.
     * @param string
     * @return boolean
     */
    public function detectNull($value)
    {
        if (is_null($value)) {
            return true;
        }

        foreach ($this->formats['null'] as $v) {
            if (preg_match('/^' . preg_quote($v, '/') . '$/iu', $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detects null from string. Returns true on success, false otherwise.
     * @param string
     * @return boolean|null
     */
    public function detectBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        foreach ($this->formats['true'] as $v) {
            if (preg_match('/^' . preg_quote($v, '/') . '$/iu', $value)) {
                return true;
            }
        }
        foreach ($this->formats['false'] as $v) {
            if (preg_match('/^' . preg_quote($v, '/') . '$/iu', $value)) {
                return false;
            }
        }

        return null;
    }

    /**
     * @param string
     * @return integer|null
     */
    public function detectInt($value)
    {
        if (is_int($value)) {
            return $value;
        }

        if (!preg_match('/^-?[0-9]{1,3}(' . preg_quote($this->formats['thousandSeparator'], '/') . '[0-9]{3})*$/', $value)) {
            return null;
        }

        return (int) str_replace($this->formats['thousandSeparator'], '', $value);
    }

    /**
     * @param string
     * @return float|null
     */
    public function detectFloat($value)
    {
        if (is_float($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (float) $value;
        }

        if (!preg_match(
            '/^-?[0-9]{1,3}(?:' .
            preg_quote($this->formats['thousandSeparator'], '/') . '[0-9]{3})*(?:' .
            preg_quote($this->formats['decimalPoint'], '/') . '[0-9]+)?([Ee][+-][0-9]+)?$/',
            $value
        )) {
            return null;
        }

        return (float) str_replace([$this->formats['thousandSeparator'], $this->formats['decimalPoint']], ['', '.'], $value);
    }

    /**
     * @param string
     * @return \Dogma\Date|null
     */
    public function detectDate($value)
    {
        if ($value instanceof Date) {
            return $value;
        }
        if ($value instanceof DateTime) {
            return new Date($value);
        }

        if (!$date = Date::createFromFormat($this->formats['date'], $value)) {
            return null;
        }

        return $date;
    }

    /**
     * @param string
     * @return \Dogma\DateTime|null
     */
    public function detectDateTime($value)
    {
        if ($value instanceof Date) {
            return new DateTime($value);
        }
        if ($value instanceof DateTime) {
            return $value;
        }

        if (!$datetime = DateTime::createFromFormat($this->formats['dateTime'], $value)) {
            return null;
        }

        return $datetime;
    }

    /**
     * Normalize value
     * @param string
     * @param string
     * @param boolean
     * @return mixed
     */
    public function normalize($value, $type, $nullable = false)
    {
        if ($nullable && $this->detectNull($value) === true) {
            return null;
        }

        switch ($type) {
            case Type::INT:
                $value = $this->detectInt(trim($value));
                if ($value === null) {
                    throw new \InvalidArgumentException(sprintf('Normalizer: Cannot convert value \'%s\' to integer.', $value));
                }
                return $value;

            case Type::FLOAT:
                $value = $this->detectFloat($value);
                if ($value === null) {
                    throw new \InvalidArgumentException(sprintf('Normalizer: Cannot convert value \'%s\' to float.', $value));
                }
                return $value;

            case Type::BOOL:
                $value = $this->detectBool($value);
                if ($value === null) {
                    throw new \InvalidArgumentException(sprintf('Normalizer: Cannot convert value \'%s\' to boolean.', $value));
                }
                return $value;

            case Type::DATE:
                $value = $this->detectDate($value);
                if ($value === null) {
                    throw new \InvalidArgumentException(sprintf('Normalizer: Cannot convert value \'%s\' to Date.', $value));
                }
                return $value;

            case Type::DATETIME:
                $value = $this->detectDateTime($value);
                if ($value === null) {
                    throw new \InvalidArgumentException(sprintf('Normalizer: Cannot convert value \'%s\' to DateTime.', $value));
                }
                return $value;

            case Type::STRING:
                return $value;

            default:
                throw new \InvalidArgumentException(sprintf('Normalizer: Unsupported type \'%s\'.', $type));
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
    public function format($value, $type = null, $nullable = false)
    {
        if (is_int($value) || is_numeric($value) && $type === Type::INT) {
            return number_format((int) $value, 0, '.', $this->formats['thousandSeparator']);

        } elseif (is_float($value) || is_numeric($value) && $type === Type::FLOAT) {
            return rtrim(
                rtrim(number_format((float) $value, 20, $this->formats['decimalPoint'], $this->formats['thousandSeparator']), '0'),
                $this->formats['decimalPoint']
            );

        } elseif ($value instanceof \Dogma\Date) {
            if (isset($type) && $type !== Type::DATE) {
                throw new \InvalidArgumentException(sprintf('Normalizer: Wrong data type Date. %s expected.', $type));
            }

            return $this->formatDate($value);

        } elseif ($value instanceof \DateTime) {
            if (isset($type) && $type !== Type::DATETIME) {
                throw new \InvalidArgumentException(sprintf('Normalizer: Wrong data type DateTime. %s expected.', $type));
            }

            return $this->formatDateTime($value);

        } elseif (is_bool($value)) {
            if (isset($type) && $type !== Type::BOOL) {
                throw new \InvalidArgumentException(sprintf('Normalizer: Wrong data type bool. %s expected.', $type));
            }

            return $value ? $this->formats['true'][0] : $this->formats['false'][0];

        } elseif (is_null($value)) {
            if (!$nullable) {
                throw new \InvalidArgumentException('Normalizer: Null value is not allowed.');
            }

            return $this->formats['null'][0];

        } else {
            return (string) $value;
        }
    }

    /**
     * @param \DateTime
     * @return string
     */
    public function formatDate(\DateTime $date)
    {
        return $date->format($this->formats['date']);
    }

    /**
     * @param \DateTime
     * @return string
     */
    public function formatDateTime(\DateTime $date)
    {
        return $date->format($this->formats['dateTime']);
    }

}
