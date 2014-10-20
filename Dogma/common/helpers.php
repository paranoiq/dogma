<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */


/**
 * ArrayObject constructor helper
 * @param mixed  list of items
 * @return Dogma\ArrayObject
 */
function arr() {
    $args = func_get_args();

    return new Dogma\ArrayObject($args);
}


/**
 * Collection constructor helper
 * @param mixed  list of items
 * @return Dogma\Collection
 */
// function coll() {
//     $args = func_get_args();
//
//     return new Dogma\Collection($args);
// }


if (!function_exists('is_traversable')) {
    /**
     * Returns true if variable is array or traversable object.
     *
     * @param mixed
     * @return boolean
     */
    function is_traversable($var) {
        return is_array($var) || $var instanceof Traversable;
    }
} else {
    trigger_error('Duplicite declaration of function is_traversable().');
}


function array_separate_keys(&$array, $keys) {
    if (is_string($keys)) {
        $keys = array_flip(explode(',', $keys));

    // Nette\Utils\Arrays::isList()
    } elseif (range(0, count($keys) - 1) === array_keys($keys)) {
        $keys = array_flip($keys);
    }

    $good = array_diff_key($array, $keys);
    $remainder = array_diff_key($array, $good);

    $array = $good;
    return $remainder;
}


/**
 * Shortcut for in_array($value, array(...)) similar to SQL operator IN
 * Call: in($value, $param1, $param2, ...)
 *
 * @param mixed
 * @param mixed multiple
 * @return bool
 */
// function in($value, $params) {
//    $params = func_get_args();
//    array_shift($params);
//
//    return in_array($value, $params);
// }


/**
 * SQL operator LIKE
 *
 * @param string
 * @param string
 * @return bool
 */
// function like($string, $pattern) {
//     return preg_match('/^' . str_replace(array('%', '_'), array('.*?', '.'),
//         preg_quote($pattern, '/')) . '$/ui', $string);
// }



/**
 * Object instantiation helper
 * @param string
 * @param array
 * @return object
 */
// function instance($class, &$data, $aliases = array()) {
//     return Dogma\Object\Prototyper::createInstance($class, $data, $aliases);
// }


function abbr($s, $maxLen, $append = "\xE2\x80\xA6") {
    if (Nette\Utils\Strings::length($s) <= $maxLen) return htmlspecialchars($s);

    return "<abbr title='" . htmlspecialchars($s) . "'>"
        . htmlspecialchars(Nette\Utils\Strings::truncate($s, $maxLen, $append)) . "</abbr>";
}
