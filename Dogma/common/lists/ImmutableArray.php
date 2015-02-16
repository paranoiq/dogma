<?php

namespace Dogma;

class ImmutableArray implements \Countable, \IteratorAggregate, \ArrayAccess
{
    use StrictBehaviorMixin;
    use ImmutableArrayAccessMixin;

    /** @internal */
    const PRESERVE_KEYS = true;

    /** @var mixed[] */
    private $items;

    /**
     * @param mixed[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param mixed $value multiple
     * @return static
     */
    public static function create()
    {
        return new static(func_get_args());
    }

    /**
     * @param mixed[]|\Traversable $that
     * @return static
     */
    public static function from($that)
    {
        return new static(self::convertToArray($that));
    }

    /**
     * @param mixed[]|\Traversable $keys
     * @param mixed[]|\Traversable $values
     * @return static
     */
    public static function combine($keys, $values)
    {
        $keys = self::convertToArray($keys);
        $values = self::convertToArray($values);

        return new static(array_combine($keys, $values));
    }

    /**
     * @param array|\Traversable $that
     * @return array|\mixed[]
     */
    private static function convertToArray($that)
    {
        if (is_array($that)) {
            return $that;
        } elseif ($that instanceof self) {
            $that = $that->toArray();
        } elseif ($that instanceof \Traversable) {
            $that = iterator_to_array($that);
        } else {
            throw new \Dogma\InvalidTypeException(Type::PHP_ARRAY, $that);
        }
        return $that;
    }

    /**
     * @param integer $start
     * @param integer $end
     * @param integer $step >= 1
     * @return static
     */
    public static function range($start, $end, $step = 1)
    {
        Check::natural($step);

        return new static(range($start, $end, $step));
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return \Dogma\ReverseArrayIterator
     */
    public function getReverseIterator()
    {
        return new ReverseArrayIterator($this->items);
    }

    /**
     * @return static
     */
    public function getKeys()
    {
        return new static(array_keys($this->toArray()));
    }

    /**
     * @return static
     */
    public function getValues()
    {
        return new static(array_values($this->toArray()));
    }

    /**
     * @return mixed[]
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * @return mixed[]
     */
    public function toArrayRecursive()
    {
        $res = $this->toArray();
        foreach ($res as $key => $value) {
            $res[$key] = ($value instanceof self ? $value->toArray() : $value);
        }
        return $res;
    }

    /**
     * @return mixed
     */
    public function randomKey()
    {
        return array_rand($this->toArray());
    }

    /**
     * @return mixed
     */
    public function randomValue()
    {
        return $this[$this->randomKey()];
    }

    /**
     * @param callable $function
     */
    public function doForEach($function)
    {
        foreach ($this as $value) {
            $function($value);
        }
    }

    // querries --------------------------------------------------------------------------------------------------------

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return count($this->items) === 0;
    }

    /**
     * @return boolean
     */
    public function isNotEmpty()
    {
        return count($this->items) !== 0;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public function contains($value)
    {
        return array_search($value, $this->toArray(), Type::STRICT) !== false;
    }

    /**
     * @param mixed $value
     * @param integer $from
     * @return mixed|null
     */
    public function indexOf($value, $from = 0)
    {
        if ($from > 0) {
            return $this->drop($from)->indexOf($value);
        }
        $result = array_search($value, $this->toArray(), Type::STRICT);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function indexesOf($value)
    {
        return new static(array_keys($this->items, $value, Type::STRICT));
    }

    /**
     * @param mixed $value
     * @param integer $end
     * @return mixed|null
     */
    public function lastIndexOf($value, $end = null)
    {
        if ($end !== null) {
            return $this->take($end)->indexesOf($value)->last();
        }
        return $this->indexesOf($value)->last();
    }

    /**
     * @param callable $function
     * @param integer $from
     * @return mixed|null
     */
    public function indexWhere($function, $from = 0)
    {
        foreach ($this->drop($from) as $key => $value) {
            if ($function($value)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @param callable $function
     * @param integer $end
     * @return mixed
     */
    public function lastIndexWhere($function, $end = null)
    {
        return $this->take($end)->reverse()->indexWhere($function);
    }

    /**
     * @param mixed $key
     * @return boolean
     */
    public function containsKey($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * @param callable $function
     * @return boolean
     */
    public function exists($function)
    {
        foreach ($this as $value) {
            if ($function($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param callable $function
     * @return boolean
     */
    public function forAll($function)
    {
        foreach ($this as $value) {
            if (!$function($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param callable $function
     * @return mixed|null
     */
    public function find($function)
    {
        foreach ($this as $value) {
            if ($function($value)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param callable $function
     * @return integer
     */
    public function prefixLength($function)
    {
        return $this->segmentLength($function, 0);
    }

    /**
     * @param callable $function
     * @param integer $from
     * @return integer
     */
    public function segmentLength($function, $from = 0)
    {
        $i = 0;
        $that = $this->drop($from);
        foreach ($that as $value) {
            if (!$function($value)) {
                break;
            }
            $i++;
        }
        return $i;
    }

    // stats -----------------------------------------------------------------------------------------------------------

    /**
     * @param callable $function
     * @return integer
     */
    public function count($function = null)
    {
        if ($function === null) {
            return count($this->toArray());
        }
        $count = 0;
        foreach ($this as $value) {
            if ($function($value)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Alias of count()
     * @return integer
     */
    public function size()
    {
        return count($this->items);
    }

    /**
     * @return static
     */
    public function countValues()
    {
        return new static(array_count_values($this->toArray()));
    }

    /**
     * @return mixed|null
     */
    public function max()
    {
        if ($this->isEmpty()) {
            return null;
        }
        return max($this->toArray());
    }

    /**
     * @return mixed|null
     */
    public function min()
    {
        if ($this->isEmpty()) {
            return null;
        }
        return min($this->toArray());
    }

    /**
     * @param callable $function
     * @return static
     */
    public function maxBy($function)
    {
        if ($this->isEmpty()) {
            return null;
        }
        $max = $this->map($function)->max();
        return $this->find(function ($value) use ($max, $function) {
            return $function($value) === $max;
        });
    }

    /**
     * @param callable $function
     * @return static
     */
    public function minBy($function)
    {
        if ($this->isEmpty()) {
            return null;
        }
        $min = $this->map($function)->min();
        return $this->find(function ($value) use ($min, $function) {
            return $function($value) === $min;
        });
    }

    /**
     * @return integer|float
     */
    public function product()
    {
        return array_product($this->toArray());
    }

    /**
     * @return integer|float
     */
    public function sum()
    {
        return array_sum($this->toArray());
    }

    // comparation -----------------------------------------------------------------------------------------------------

    /**
     * @param mixed[]|\Traversable $array
     * @return boolean
     */
    public function containsSlice($array)
    {
        return $this->indexOfSlice($array, 0) !== null;
    }

    /**
     * @param mixed[]|\Traversable $array
     * @param integer $from
     * @return integer|null
     */
    public function indexOfSlice($array, $from = 0)
    {
        /** @var self $that */
        $that = $this->drop($from);
        while ($that->isNotEmpty()) {
            if ($that->startsWith($array)) {
                return $from;
            }
            $from++;
            $that = $that->tail();
        }

        return null;
    }

    /**
     * @param mixed[]|\Traversable $array
     * @param callable $function
     * @return boolean
     */
    public function corresponds($array, $function)
    {
        $iterator = $this->getIterator();
        $iterator->rewind();
        foreach ($array as $value) {
            if (!$iterator->valid() || !$function($iterator->current(), $value)) {
                return false;
            }
            $iterator->next();
        }
        return !$iterator->valid();
    }

    /**
     * @param mixed[]|\Traversable $array
     * @return boolean
     */
    public function hasSameElements($array)
    {
        return $this->corresponds($array, function ($a, $b) {
            return $a === $b;
        });
    }

    /**
     * @param mixed[]|\Traversable $array
     * @param int $from
     * @return bool
     */
    public function startsWith($array, $from = 0)
    {
        /** @var \Iterator $iterator */
        $iterator = $this->drop($from)->getIterator();
        $iterator->rewind();
        foreach ($array as $value) {
            if ($iterator->valid() && $value === $iterator->current()) {
                $iterator->next();
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed[]|\Traversable $array
     * @return boolean
     */
    public function endsWith($array)
    {
        return $this->startsWith($array, $this->count() - count($array));
    }

    // transformating --------------------------------------------------------------------------------------------------

    /**
     * @param callable $function
     * @param mixed $init
     * @return mixed|null
     */
    public function fold($function, $init = null)
    {
        return $this->foldLeft($function, $init);
    }

    /**
     * @param callable $function
     * @param mixed $init
     * @return mixed|null
     */
    public function foldLeft($function, $init = null)
    {
        return array_reduce($this->toArray(), $function, $init);
    }

    /**
     * @param callable $function
     * @param mixed $init
     * @return mixed|null
     */
    public function foldRight($function, $init = null)
    {
        foreach ($this->getReverseIterator() as $value) {
            $init = $function($value, $init);
        }
        return $init;
    }

    /**
     * @param callable $function
     * @return mixed|null
     */
    public function reduce($function)
    {
        return $this->reduceLeft($function);
    }

    /**
     * @param callable $function
     * @return mixed|null
     */
    public function reduceLeft($function)
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->tail()->foldLeft($function, $this->head());
    }

    /**
     * @param callable $function
     * @return mixed|null
     */
    public function reduceRight($function)
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->init()->foldRight($function, $this->last());
    }

    /**
     * @param callable $function
     * @param mixed $init
     * @return static
     */
    public function scanLeft($function, $init)
    {
        $res = [];
        $res[] = $init;
        foreach ($this as $value) {
            $res[] = $init = $function($init, $value);
        }
        return new static($res);
    }

    /**
     * @param callable $function
     * @param mixed $init
     * @return static
     */
    public function scanRight($function, $init)
    {
        $res = [];
        $res[] = $init;
        foreach ($this->getReverseIterator() as $value) {
            $init = $function($value, $init);
            array_unshift($res, $init);
        }
        return new static($res);
    }

    // slicing ---------------------------------------------------------------------------------------------------------

    /**
     * @return mixed|null
     */
    public function head()
    {
        $result = reset($this->items);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * Alias of head()
     * @return mixed|null
     */
    public function first()
    {
        $result = reset($this->items);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * @return mixed|null
     */
    public function last()
    {
        $result = end($this->items);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * @return static
     */
    public function init()
    {
        return $this->slice(0, -1);
    }

    /**
     * @return static
     */
    public function tail()
    {
        return $this->drop(1);
    }

    /**
     * @return static
     */
    public function inits()
    {
        $res = [$this];
        $that = $this;
        while ($that->isNotEmpty()) {
            $res[] = $that = $that->init();
        }
        return new static($res);
    }

    /**
     * @return static
     */
    public function tails()
    {
        $res = [$this];
        $that = $this;
        while ($that->isNotEmpty()) {
            $res[] = $that = $that->tail();
        }
        return new static($res);
    }

    /**
     * @return mixed[] (mixed $head, static $tail)
     */
    public function headTail()
    {
        return [$this->head(), $this->tail()];
    }

    /**
     * @return mixed[] (static $init, mixed $last)
     */
    public function initLast()
    {
        return [$this->init(), $this->last()];
    }

    /**
     * @param integer $from
     * @param integer $length
     * @return static
     */
    public function slice($from, $length = null)
    {
        return new static(array_slice($this->toArray(), $from, $length, self::PRESERVE_KEYS));
    }

    /**
     * @param integer $size
     * @return static
     */
    public function chunks($size)
    {
        /** @var self $res */
        $res = new static(array_chunk($this->toArray(), $size, self::PRESERVE_KEYS));
        return $res->map(function ($array) {
            return new static($array);
        });
    }

    /**
     * @param integer $size
     * @param integer $step
     * @return static
     */
    public function sliding($size, $step = 1)
    {
        $res = [];
        for ($i = 0; $i <= $this->count() - $size + $step - 1; $i += $step) {
            $res[] = $this->slice($i, $size);
        }
        return new static($res);
    }

    /**
     * @param integer $count
     * @return static
     */
    public function drop($count)
    {
        return $this->slice($count, $this->count());
    }

    /**
     * @param integer $count
     * @return static
     */
    public function dropRight($count)
    {
        return $this->slice(0, $this->count() - $count);
    }

    /**
     * @param callable $function
     * @return static
     */
    public function dropWhile($function)
    {
        $res = [];
        $go = false;
        foreach ($this as $key => $value) {
            if (!$function($value)) {
                $go = true;
            }
            if ($go) {
                $res[$key] = $value;
            }
        }
        return new static($res);
    }

    /**
     * @param integer $length
     * @param mixed $value
     * @return static
     */
    public function padTo($length, $value)
    {
        return new static(array_pad($this->toArray(), $length, $value));
    }

    /**
     * @param callable $function
     * @return static[] (static $l, static $r)
     */
    public function span($function)
    {
        $l = [];
        $r = [];
        $toLeft = true;
        foreach ($this as $key => $value) {
            $toLeft = $toLeft && $function($value);
            if ($toLeft) {
                $l[$key] = $value;
            } else {
                $r[$key] = $value;
            }
        }
        return [new static($l), new static($r)];
    }

    /**
     * @param integer $count
     * @return static
     */
    public function take($count)
    {
        return $this->slice(0, $count);
    }

    /**
     * @param integer $count
     * @return static
     */
    public function takeRight($count)
    {
        return $this->slice(-$count, $count);
    }

    /**
     * @param callable $function
     * @return static
     */
    public function takeWhile($function)
    {
        $res = [];
        foreach ($this as $key => $value) {
            if (!$function($value)) {
                break;
            }
            $res[$key] = $value;
        } return new static($res);
    }

    // filtering -------------------------------------------------------------------------------------------------------

    /**
     * @param callable $function
     * @return static
     */
    public function collect($function)
    {
        return $this->map($function)->filter();
    }

    /**
     * @param callable $function
     * @return static
     */
    public function filter($function = null)
    {
        if ($function) {
            return new static(array_filter($this->toArray(), $function));
        } else {
            return new static(array_filter($this->toArray()));
        }
    }

    /**
     * @param callable $function
     * @return static
     */
    public function filterKeys($function)
    {
        $res = [];
        foreach ($this as $key => $value) {
            if ($function($key)) {
                $res[$key] = $value;
            }
        }
        return new static($res);
    }

    /**
     * @param callable $function
     * @return static
     */
    public function filterNot($function)
    {
        return $this->filter(function ($value) use ($function) {
            return !$function($value);
        });
    }

    /**
     * @param callable $function
     * @return static[] (static $fist, static $second)
     */
    public function partition($function)
    {
        $a = [];
        $b = [];
        foreach ($this as $key => $value) {
            if ($function($value)) {
                $a[$key] = $value;
            } else {
                $b[$key] = $value;
            }
        }
        return [new static($a), new static($b)];
    }

    // mapping ---------------------------------------------------------------------------------------------------------

    /**
     * @param callable $function
     * @return mixed
     */
    public function flatMap($function)
    {
        return $this->map($function)->flatten();
    }

    /**
     * @return static
     */
    public function flatten()
    {
        $res = [];
        foreach ($this as $values) {
            foreach ($values as $value) {
                $res[] = $value;
            }
        }
        return new static($res);
    }

    /**
     * @param callable $public function
     * @return static
     */
    public function groupBy($function)
    {
        $res = [];
        foreach ($this as $key => $value) {
            $groupKey = $function($value);
            $res[$groupKey][$key] = $value;
        }
        /** @var self $r */
        $r = new static($res);
        return $r->map(function ($array) {
            return new static($array);
        });
    }

    /**
     * @param callable $function
     * @return static
     */
    public function map($function)
    {
        return new static(array_map($function, $this->toArray()));
    }

    /**
     * @param callable $function
     * @return static
     */
    public function mapPairs($function)
    {
        return $this->remap(function ($key, $value) use ($function) {
            return [$key => $function($key, $value)];
        });
    }

    /**
     * @param callable $function
     * @return static
     */
    public function remap($function)
    {
        $res = [];
        foreach ($this as $key => $value) {
            foreach ($function($key, $value) as $newKey => $newValue) {
                $res[$newKey] = $newValue;
            }
        }
        return new static($res);
    }

    /**
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->toArray()));
    }

    /**
     * @return static
     */
    public function transpose()
    {
        $arr = $this->toArray();
        if ($this->isEmpty()) {
            return new static($arr);
        }
        array_unshift($arr, null);
        $arr = array_map(...$arr);
        foreach ($arr as $key => $value) {
            $arr[$key] = (array) $value;
        }
        return new static($arr);
    }

    // sorting ---------------------------------------------------------------------------------------------------------

    /**
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->toArray(), self::PRESERVE_KEYS));
    }

    /**
     * @return static
     */
    public function shuffle()
    {
        $arr = $this->toArray();
        shuffle($arr);
        return new static($arr);
    }

    /**
     * @param integer $flags
     * @return static
     */
    public function sort($flags = Sorting::REGULAR)
    {
        $arr = $this->toArray();
        if ($flags & Order::DESCENDING) {
            arsort($arr, $flags);
        } else {
            asort($arr, $flags);
        }
        return new static($arr);
    }

    /**
     * @param integer $flags
     * @return static
     */
    public function sortKeys($flags = Sorting::REGULAR)
    {
        $arr = $this->toArray();
        if ($flags & Order::DESCENDING) {
            krsort($arr, $flags);
        } else {
            ksort($arr, $flags);
        }
        return new static($arr);
    }

    /**
     * @param callable $function
     * @param integer $flags
     * @return static
     */
    public function sortWith($function, $flags = Order::ASCENDING)
    {
        $arr = $this->toArray();
        uasort($arr, $function);
        if ($flags & Order::DESCENDING) {
            $arr = array_reverse($arr);
        }
        return new static($arr);
    }

    /**
     * @param callable $function
     * @param integer $flags
     * @return static
     */
    public function sortKeysWith($function, $flags = Order::ASCENDING)
    {
        $arr = $this->toArray();
        uksort($arr, $function);
        if ($flags & Order::DESCENDING) {
            $arr = array_reverse($arr);
        }
        return new static($arr);
    }

    /**
     * @param integer $sortFlags
     * @return static
     */
    public function distinct($sortFlags = Sorting::REGULAR)
    {
        $arr = $this->toArray();
        $arr = array_unique($arr, $sortFlags);
        return new static($arr);
    }

    // merging ---------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @return static
     */
    public function append(...$values)
    {
        return $this->appendAll($values);
    }

    /**
     * @param mixed[]|\Traversable $values
     * @return static
     */
    public function appendAll($values)
    {
        $res = $this->toArray();
        foreach ($values as $value) {
            $res[] = $value;
        }
        return new static($res);
    }

    /**
     * @param mixed $values
     * @return static
     */
    public function prepend(...$values)
    {
        return $this->prependAll($values);
    }

    /**
     * @param mixed[]|\Traversable $values
     * @return static
     */
    public function prependAll($values)
    {
        if (!is_array($values)) {
            $values = self::convertToArray($values);
        }
        foreach ($this as $value) {
            $values[] = $value;
        }
        return new static($values);
    }

    /**
     * @param mixed $find
     * @param mixed $replace
     * @return static
     */
    public function replace($find, $replace)
    {
        $arr = $this->toArray();
        $arr = array_replace($arr, [$find => $replace]);
        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $replacements
     * @return static
     */
    public function replaceAll($replacements)
    {
        if (!is_array($replacements)) {
            $replacements = self::convertToArray($replacements);
        }
        $arr = $this->toArray();
        $arr = array_replace($arr, $replacements);
        return new static($arr);
    }

    /**
     * @param integer $from
     * @param integer $length
     * @return static
     */
    public function remove($from, $length = 0)
    {
        $arr = $this->toArray();
        array_splice($arr, $from, $length);
        return new static($arr);
    }

    /**
     * @param integer $from
     * @param mixed[] $patch
     * @param integer $length
     * @return static
     */
    public function patch($from, $patch, $length = null)
    {
        $arr = $this->toArray();
        if ($length === null) {
            $length = count($patch);
        }
        array_splice($arr, $from, $length, $patch);
        return new static($arr);
    }

    /**
     * @param integer $from
     * @param mixed[] $patch
     * @param integer $length
     * @return static
     */
    public function insert($from, $patch)
    {
        $arr = $this->toArray();
        array_splice($arr, $from, 0, $patch);
        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $that
     * @return static
     */
    public function merge($that)
    {
        if (!is_array($that)) {
            $that = self::convertToArray($that);
        }
        $self = $this->toArray();
        return new static(array_merge($self, $that));
    }

    // diffing ---------------------------------------------------------------------------------------------------------

    /**
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function diff(...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_diff(...$args);

        return new static($arr);
    }

    /**
     * @param callable $function
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function diffWith($function, ...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_udiff(...$args);

        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function diffKeys(...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_diff_key(...$args);

        return new static($arr);
    }

    /**
     * @param callable $function
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function diffKeysWith($function, ...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_diff_ukey(...$args);

        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $args
     * @param callable|null $function
     * @return static
     */
    public function diffPairs(...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_diff_assoc(...$args);

        return new static($arr);
    }

    /**
     * @param callable|null $function
     * @param callable|null $keysFunction
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function diffPairsWith($function, $keysFunction, ...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        if ($function && $keysFunction) {
            $args[] = $function;
            $args[] = $keysFunction;
            $arr = array_udiff_uassoc(...$args);
        } elseif ($function && !$keysFunction) {
            $args[] = $function;
            $arr = array_udiff_assoc(...$args);
        } elseif (!$function && $keysFunction) {
            $args[] = $keysFunction;
            $arr = array_diff_uassoc(...$args);
        } else {
            $arr = array_diff_assoc(...$args);
        }
        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function intersect(...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_intersect(...$args);

        return new static($arr);
    }

    /**
     * @param callable $function
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function intersectWith($function, ...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_uintersect(...$args);

        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function intersectKeys(...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_intersect_key(...$args);

        return new static($arr);
    }

    /**
     * @param callable $function
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function intersectKeysWith($function, ...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);
        $args[] = $function;

        $arr = array_intersect_ukey(...$args);

        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $args
     * @param callable|null $function
     * @return static
     */
    public function intersectPairs(...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        $arr = array_intersect_assoc(...$args);

        return new static($arr);
    }

    /**
     * @param callable|null $function
     * @param callable|null $keysFunction
     * @param mixed[]|\Traversable $args
     * @return static
     */
    public function intersectPairsWith($function, $keysFunction, ...$args)
    {
        $self = $this->toArray();
        $args = array_map([self::class, 'convertToArray'], $args);
        array_unshift($args, $self);

        if ($function && $keysFunction) {
            $args[] = $function;
            $args[] = $keysFunction;
            $arr = array_uintersect_uassoc(...$args);
        } elseif ($function && !$keysFunction) {
            $args[] = $function;
            $arr = array_uintersect_assoc(...$args);
        } elseif (!$function && $keysFunction) {
            $args[] = $keysFunction;
            $arr = array_intersect_uassoc(...$args);
        } else {
            $arr = array_intersect_assoc(...$args);
        }
        return new static($arr);
    }

}
