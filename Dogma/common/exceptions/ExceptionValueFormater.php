<?php

namespace Dogma;

class ExceptionValueFormater
{
    use StaticClassMixin;

    /**
     * @param mixed $value
     * @return string
     */
    public static function format($value)
    {
        if (is_object($value)) {
            return sprintf('%s #%s', get_class($value), substr(md5(spl_object_hash($value)), 0, 8));
        } elseif (is_resource($value)) {
            return sprintf('resource (%s) #%d', get_resource_type($value), substr($value, 13));
        } elseif (is_array($value)) {
            return sprintf('array (%d) #%s', count($value), substr(md5(serialize($value)), 0, 8));
        } elseif (is_string($value)) {
            return sprintf('%s (%d)', $value, strlen($value));
        } elseif (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } elseif (is_null($value)) {
            return 'NULL';
        } else { // integer, float
            return $value;
        }
    }

}
