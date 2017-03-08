<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class ImmutableArray implements \Countable, \IteratorAggregate, \ArrayAccess
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\ImmutableArrayAccessMixin;

    /** @internal */
    private const PRESERVE_KEYS = true;

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
    public static function create(...$values): self
    {
        return new static($values);
    }

    /**
     * @param mixed[]|\Traversable $that
     * @return static
     */
    public static function from($that): self
    {
        return new static(self::convertToArray($that));
    }

    /**
     * @param mixed[]|\Traversable $keys
     * @param mixed[]|\Traversable $values
     * @return static
     */
    public static function combine($keys, $values): self
    {
        $keys = self::convertToArray($keys);
        $values = self::convertToArray($values);

        return new static(array_combine($keys, $values));
    }

    /**
     * @param mixed[]|\Traversable $that
     * @return mixed[]
     */
    private static function convertToArray($that): array
    {
        if (is_array($that)) {
            return $that;
        } elseif ($that instanceof self) {
            return $that->toArray();
        } elseif ($that instanceof \Traversable) {
            return iterator_to_array($that);
        } else {
            throw new \Dogma\InvalidTypeException(Type::PHP_ARRAY, $that);
        }
    }

    /**
     * @param int|string $start
     * @param int|string $end
     * @param int $step >= 1
     * @return static
     */
    public static function range($start, $end, int $step = 1): self
    {
        Check::min($step, 1);

        return new static(range($start, $end, $step));
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function getReverseIterator(): ReverseArrayIterator
    {
        return new ReverseArrayIterator($this->items);
    }

    public function keys(): self
    {
        return new static(array_keys($this->toArray()));
    }

    public function values(): self
    {
        return new static(array_values($this->toArray()));
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return mixed[]
     */
    public function toArrayRecursive(): array
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

    public function doForEach(callable $function): void
    {
        foreach ($this as $value) {
            $function($value);
        }
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    public function isNotEmpty(): bool
    {
        return count($this->items) !== 0;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value): bool
    {
        return array_search($value, $this->toArray(), Type::STRICT) !== false;
    }

    /**
     * @param mixed $value
     * @param int $from
     * @return mixed|null
     */
    public function indexOf($value, int $from = 0)
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
    public function indexesOf($value): self
    {
        return new static(array_keys($this->items, $value, Type::STRICT));
    }

    /**
     * @param mixed $value
     * @param int|null $end
     * @return mixed|null
     */
    public function lastIndexOf($value, int $end = null)
    {
        if ($end !== null) {
            return $this->take($end)->indexesOf($value)->last();
        }
        return $this->indexesOf($value)->last();
    }

    /**
     * @param callable $function
     * @param int $from
     * @return mixed|null
     */
    public function indexWhere(callable $function, int $from = 0)
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
     * @param int $end
     * @return mixed
     */
    public function lastIndexWhere(callable $function, int $end = null)
    {
        return $this->take($end)->reverse()->indexWhere($function);
    }

    /**
     * @param int|string $key
     * @return bool
     */
    public function containsKey($key): bool
    {
        return $this->offsetExists($key);
    }

    public function exists(callable $function): bool
    {
        foreach ($this as $value) {
            if ($function($value)) {
                return true;
            }
        }
        return false;
    }

    public function forAll(callable $function): bool
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
    public function find(callable $function)
    {
        foreach ($this as $value) {
            if ($function($value)) {
                return $value;
            }
        }
        return null;
    }

    public function prefixLength(callable $function): int
    {
        return $this->segmentLength($function, 0);
    }

    public function segmentLength(callable $function, int $from = 0): int
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

    public function count(callable $function = null): int
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

    public function size(): int
    {
        return count($this->items);
    }

    public function countValues(): self
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
     * @return mixed|null
     */
    public function maxBy(callable $function)
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
     * @return mixed|null
     */
    public function minBy(callable $function)
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
     * @return int|float
     */
    public function product()
    {
        return array_product($this->toArray());
    }

    /**
     * @return int|float
     */
    public function sum()
    {
        return array_sum($this->toArray());
    }

    // comparation -----------------------------------------------------------------------------------------------------

    /**
     * @param mixed[]|\Traversable $array
     * @return bool
     */
    public function containsSlice($array): bool
    {
        return $this->indexOfSlice($array, 0) !== null;
    }

    /**
     * @param mixed[]|\Traversable $array
     * @param int $from
     * @return int|null
     */
    public function indexOfSlice($array, int $from = 0): ?int
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
     * @return bool
     */
    public function corresponds($array, callable $function): bool
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
     * @return bool
     */
    public function hasSameElements($array): bool
    {
        return $this->corresponds($array, function ($a, $b) {
            return $a === $b;
        });
    }

    /**
     * @param mixed[]|\Traversable $slice
     * @param int $from
     * @return bool
     */
    public function startsWith($slice, int $from = 0): bool
    {
        /** @var \Iterator $iterator */
        $iterator = $this->drop($from)->getIterator();
        $iterator->rewind();
        foreach ($slice as $value) {
            if ($iterator->valid() && $value === $iterator->current()) {
                $iterator->next();
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed[]|\Traversable $slice
     * @return bool
     */
    public function endsWith($slice): bool
    {
        return $this->startsWith($slice, $this->count() - count($slice));
    }

    // transforming ----------------------------------------------------------------------------------------------------

    /**
     * @param callable $function
     * @param mixed $init
     * @return mixed|null
     */
    public function fold(callable $function, $init = null)
    {
        return $this->foldLeft($function, $init);
    }

    /**
     * @param callable $function
     * @param mixed $init
     * @return mixed|null
     */
    public function foldLeft(callable $function, $init = null)
    {
        return array_reduce($this->toArray(), $function, $init);
    }

    /**
     * @param callable $function
     * @param mixed $init
     * @return mixed|null
     */
    public function foldRight(callable $function, $init = null)
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
    public function reduce(callable $function)
    {
        return $this->reduceLeft($function);
    }

    /**
     * @param callable $function
     * @return mixed|null
     */
    public function reduceLeft(callable $function)
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
    public function reduceRight(callable $function)
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
    public function scanLeft(callable $function, $init): self
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
    public function scanRight(callable $function, $init): self
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
        if (count($this->items) === 0) {
            return null;
        }
        return reset($this->items);
    }

    /**
     * Alias of head()
     * @return mixed|null
     */
    public function first()
    {
        if (count($this->items) === 0) {
            return null;
        }
        return reset($this->items);
    }

    /**
     * @return mixed|null
     */
    public function last()
    {
        if (count($this->items) === 0) {
            return null;
        }
        return end($this->items);
    }

    public function init(): self
    {
        return $this->slice(0, -1);
    }

    public function tail(): self
    {
        return $this->drop(1);
    }

    public function inits(): self
    {
        $res = [$this];
        $that = $this;
        while ($that->isNotEmpty()) {
            $res[] = $that = $that->init();
        }
        return new static($res);
    }

    public function tails(): self
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
    public function headTail(): array
    {
        return [$this->head(), $this->tail()];
    }

    /**
     * @return mixed[] (static $init, mixed $last)
     */
    public function initLast(): array
    {
        return [$this->init(), $this->last()];
    }

    public function slice(int $from, int $length = null): self
    {
        return new static(array_slice($this->toArray(), $from, $length, self::PRESERVE_KEYS));
    }

    public function chunks(int $size): self
    {
        /** @var self $res */
        $res = new static(array_chunk($this->toArray(), $size, self::PRESERVE_KEYS));
        return $res->map(function ($array) {
            return new static($array);
        });
    }

    public function sliding(int $size, int $step = 1): self
    {
        $res = [];
        for ($i = 0; $i <= $this->count() - $size + $step - 1; $i += $step) {
            $res[] = $this->slice($i, $size);
        }
        return new static($res);
    }

    public function drop(int $count): self
    {
        return $this->slice($count, $this->count());
    }

    public function dropRight(int $count): self
    {
        return $this->slice(0, $this->count() - $count);
    }

    public function dropWhile(callable $function): self
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
     * @param int $length
     * @param mixed $value
     * @return static
     */
    public function padTo(int $length, $value): self
    {
        return new static(array_pad($this->toArray(), $length, $value));
    }

    /**
     * @param callable $function
     * @return static[] (static $l, static $r)
     */
    public function span(callable $function): array
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

    public function take(int $count = null): self
    {
        return $this->slice(0, $count);
    }

    public function takeRight(int $count): self
    {
        return $this->slice(-$count, $count);
    }

    public function takeWhile(callable $function): self
    {
        $res = [];
        foreach ($this as $key => $value) {
            if (!$function($value)) {
                break;
            }
            $res[$key] = $value;
        }
        return new static($res);
    }

    // filtering -------------------------------------------------------------------------------------------------------

    public function collect(callable $function): self
    {
        return $this->map($function)->filter();
    }

    public function filter(callable $function = null): self
    {
        if ($function) {
            return new static(array_filter($this->toArray(), $function));
        } else {
            return new static(array_filter($this->toArray()));
        }
    }

    public function filterKeys(callable $function): self
    {
        $res = [];
        foreach ($this as $key => $value) {
            if ($function($key)) {
                $res[$key] = $value;
            }
        }
        return new static($res);
    }

    public function filterNot(callable $function): self
    {
        return $this->filter(function ($value) use ($function) {
            return !$function($value);
        });
    }

    /**
     * @param callable $function
     * @return static[] (static $fist, static $second)
     */
    public function partition(callable $function): array
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

    public function flatMap(callable $function): self
    {
        return $this->map($function)->flatten();
    }

    public function flatten(): self
    {
        $res = [];
        foreach ($this as $values) {
            if (is_array($values)) {
                foreach (Arr::flatten($values) as $value) {
                    $res[] = $value;
                }
            } elseif ($values instanceof self) {
                foreach ($values->flatten() as $value) {
                    $res[] = $value;
                }
            } else {
                $res[] = $values;
            }
        }
        return new static($res);
    }

    public function groupBy(callable $function): self
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

    public function map(callable $function): self
    {
        return new static(array_map($function, $this->toArray()));
    }

    public function mapPairs(callable $function): self
    {
        return $this->remap(function ($key, $value) use ($function) {
            return [$key => $function($key, $value)];
        });
    }

    public function remap(callable $function): self
    {
        $res = [];
        foreach ($this as $key => $value) {
            foreach ($function($key, $value) as $newKey => $newValue) {
                $res[$newKey] = $newValue;
            }
        }
        return new static($res);
    }

    public function flip(): self
    {
        return new static(array_flip($this->toArray()));
    }

    public function transpose(): self
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

    /**
     * @param mixed $valueKey
     * @param mixed|null $indexKey
     * @return static
     */
    public function column($valueKey, $indexKey = null): self
    {
        return new static(array_column($this->toArrayRecursive(), $valueKey, $indexKey));
    }

    // sorting ---------------------------------------------------------------------------------------------------------

    public function reverse(): self
    {
        return new static(array_reverse($this->toArray(), self::PRESERVE_KEYS));
    }

    public function shuffle(): self
    {
        $arr = $this->toArray();
        shuffle($arr);
        return new static($arr);
    }

    public function sort(int $flags = Sorting::REGULAR): self
    {
        $arr = $this->toArray();
        if ($flags & Order::DESCENDING) {
            arsort($arr, $flags);
        } else {
            asort($arr, $flags);
        }
        return new static($arr);
    }

    public function sortKeys(int $flags = Sorting::REGULAR): self
    {
        $arr = $this->toArray();
        if ($flags & Order::DESCENDING) {
            krsort($arr, $flags);
        } else {
            ksort($arr, $flags);
        }
        return new static($arr);
    }

    public function sortWith(callable $function, int $flags = Order::ASCENDING): self
    {
        $arr = $this->toArray();
        uasort($arr, $function);
        if ($flags & Order::DESCENDING) {
            $arr = array_reverse($arr);
        }
        return new static($arr);
    }

    public function sortKeysWith(callable $function, int $flags = Order::ASCENDING): self
    {
        $arr = $this->toArray();
        uksort($arr, $function);
        if ($flags & Order::DESCENDING) {
            $arr = array_reverse($arr);
        }
        return new static($arr);
    }

    public function distinct(int $sortFlags = Sorting::REGULAR): self
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
    public function append(...$values): self
    {
        return $this->appendAll($values);
    }

    /**
     * @param mixed[]|\Traversable $values
     * @return static
     */
    public function appendAll($values): self
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
    public function prepend(...$values): self
    {
        return $this->prependAll($values);
    }

    /**
     * @param mixed[]|\Traversable $values
     * @return static
     */
    public function prependAll($values): self
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
    public function replace($find, $replace): self
    {
        $arr = $this->toArray();
        $arr = array_replace($arr, [$find => $replace]);
        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $replacements
     * @return static
     */
    public function replaceAll($replacements): self
    {
        if (!is_array($replacements)) {
            $replacements = self::convertToArray($replacements);
        }
        $arr = $this->toArray();
        $arr = array_replace($arr, $replacements);
        return new static($arr);
    }

    /**
     * @param int $from
     * @param int $length
     * @return static
     */
    public function remove(int $from, int $length = 0): self
    {
        $arr = $this->toArray();
        array_splice($arr, $from, $length);
        return new static($arr);
    }

    /**
     * @param int $from
     * @param mixed[] $patch
     * @param int $length
     * @return static
     */
    public function patch(int $from, array $patch, int $length = null): self
    {
        $arr = $this->toArray();
        if ($length === null) {
            $length = count($patch);
        }
        array_splice($arr, $from, $length, $patch);
        return new static($arr);
    }

    public function insert(int $from, array $patch): self
    {
        $arr = $this->toArray();
        array_splice($arr, $from, 0, $patch);
        return new static($arr);
    }

    /**
     * @param mixed[]|\Traversable $that
     * @return static
     */
    public function merge($that): self
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
    public function diff(...$args): self
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
    public function diffWith(callable $function, ...$args): self
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
    public function diffKeys(...$args): self
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
    public function diffKeysWith(callable $function, ...$args): self
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
    public function diffPairs(...$args): self
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
    public function diffPairsWith(callable $function = null, callable $keysFunction = null, ...$args): self
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
    public function intersect(...$args): self
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
    public function intersectWith(callable $function, ...$args): self
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
    public function intersectKeys(...$args): self
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
    public function intersectKeysWith(callable $function, ...$args): self
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
     * @return static
     */
    public function intersectPairs(...$args): self
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
    public function intersectPairsWith(callable $function = null, callable $keysFunction = null, ...$args): self
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
