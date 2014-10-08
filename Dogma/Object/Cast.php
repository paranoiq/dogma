<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace {

    /**
     * Alias for Dogma\Object\Cast::cast()
     * @param object
     * @param string
     * @param array
     * @return object
     * @throws Dogma\Object\CastException
     */
    function cast($object, $class, $data = array()) {
        return Dogma\Object\Cast::cast($object, $class, $data);
    }


    /**
     * Exception during class casting
     */
    class CastException extends \LogicException {
        const RECEIVER_IS_NOT_CASTABLE = 1;
        const RECEIVER_MUST_REIMPLEMENT_ICASTABLE = 2;
        const CAST_REJECTED_BY_RECEIVER = 3;
        const INCOMPLETE_DATA = 4;
        const INCONSISTENT_STATE = 5;
    }

}


namespace Dogma\Object {


/**
 * Interface 'ICastable' must be (re)implemented by classes capable to be casted
 */
interface ICastable {

    /**
     * @param string previous class name
     * @param array  additional data
     * @param array  reminding data from old object
     * @return bool  whether the cast is possible
     * @throws Dogma\Object\CastException
     */
    public function __cast($class, $data = array(), $reminder = array());

}


/**
 * Implementation of class casting (changing the class of an object)
 */
final class Cast {

    /** @var callback */
    private static $listeners = array();


    /**
     * Cast object from one class to another. Must be supported by receiving class via ICastable
     * @param object
     * @param string
     * @param array
     * @return object
     * @throws CastException
     */
    public static function cast($object, $class, $data = array()) {
        self::testClassCastable($class);

        $prototype = Prototyper::getPrototype($class);

        $dump = (array) $object;
        Prototyper::injectData($prototype, $dump);

        $prevClass = get_class($object);
        if (!$prototype->__cast($prevClass, $data, $dump))
            throw new \CastException("Casting object from class '$prevClass' to '$class' was rejected by receiving class.", \CastException::CAST_REJECTED_BY_RECEIVER);

        foreach (self::$listeners as $listener) {
            call_user_func($listener, $object, $prototype);
        }

        return $prototype;
    }


    /**
     * Test if given class (re)implements ICastable
     * @param string
     * @throws CastException
     */
    private static function testClassCastable($class) {
        static $castable = array();
        if (in_array($class, $castable)) return;

        if (!in_array('Dogma\Object\ICastable', class_implements($class)))
            throw new \CastException("Cannot cast object to class '$class'.", \CastException::RECEIVER_IS_NOT_CASTABLE);

        $ref = new \ReflectionClass($class);
        if ($ref->getMethod('__cast')->getDeclaringClass()->getName() !== $ref->getName())
            throw new \CastException("Each castable object must reimplement the ICastable interface itself.", \CastException::RECEIVER_MUST_REIMPLEMENT_ICASTABLE);

        $castable[] = $class;
    }


    /**
     * Add listener to cast() method. Listener receives original and new object
     * @param callback
     */
    public static function addCastListener($listener) {
        if (!is_callable($listener))
            throw new \LogicException('Listener must be a valid callback.');

        self::$listeners[] = $listener;
    }

}


}
