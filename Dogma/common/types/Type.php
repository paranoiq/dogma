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
 * Complex type representation
 */
class Type
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonCloneableMixin;
    use \Dogma\NonSerializableMixin;

    // types
    const BOOL = 'bool';
    const INT = 'int';
    const FLOAT = 'float';
    const STRING = 'string';
    const PHP_ARRAY = 'array';
    const OBJECT = 'object';
    const PHP_CALLABLE = 'callable';
    const RESOURCE = 'resource';

    // pseudotypes
    const NULL = 'null';
    const NUMERIC = 'numeric';
    const SCALAR = 'scalar';
    const MIXED = 'mixed';
    const VOID = 'void';

    // strict type checks flag
    const STRICT = true;

    // nullable type flag
    const NULLABLE = true;
    const NOT_NULLABLE = false;

    /** @var \Dogma\Type[] (string $id => $type) */
    private static $instances = [];

    /** @var string */
    private $id;

    /** @var string */
    private $type;

    /** @var \Dogma\Type|\Dogma\Type[]|null */
    private $itemType;

    /** @var bool */
    private $nullable = false;

    /** @var int|null */
    private $size;

    /**
     * May be signed/unsigned for int or collation for string
     * @var string|null
     */
    private $special;

    /**
     * @param string $id
     * @param string $type
     * @param \Dogma\Type|\Dogma\Type[] $itemType
     * @param bool $nullable
     * @param int|null $size
     * @param string|null $special
     */
    final private function __construct(string $id, string $type, $itemType, bool $nullable, int $size = null, string $special = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->itemType = $itemType;
        $this->nullable = $nullable;
        $this->size = $size;
        $this->special = $special;
    }

    /**
     * @param string $type
     * @param int|null $size
     * @param string|null $special
     * @param bool $nullable
     * @return self
     */
    public static function get(string $type, $size = null, $special = null, bool $nullable = false): self
    {
        if (is_bool($size)) {
            $nullable = $size;
            $size = null;
        } elseif (is_bool($special)) {
            $nullable = $special;
            $special = null;
        }
        if ($size !== null) {
            self::checkSize($type, $size);
        }
        if ($special !== null) {
            self::checkSpecial($type, $special);
        }

        // normalize "array" to "array<mixed>"
        if ($type === self::PHP_ARRAY) {
            return self::collectionOf(self::PHP_ARRAY, self::get(self::MIXED), $nullable);
        }

        $id = $type;
        if ($size !== null || $special !== null) {
            $id .= '(' . implode(',', array_filter([$size, $special])) . ')';
        }
        if ($nullable) {
            $id .= '?';
        }
        if (empty(self::$instances[$id])) {
            $that = new self($id, $type, null, $nullable);
            self::$instances[$id] = $that;
        }

        return self::$instances[$id];
    }

    private static function checkSize(string $type, int $size = null)
    {
        if ($type === self::INT) {
            $sizes = BitSize::getIntSizes();
            if (!Arr::contains($sizes, $size)) {
                throw new \Dogma\InvalidSizeException($type, $size, $sizes);
            }
        } elseif ($type === self::FLOAT) {
            $sizes = BitSize::getFloatSizes();
            if (!Arr::contains($sizes, $size)) {
                throw new \Dogma\InvalidSizeException($type, $size, $sizes);
            }
        } elseif ($type !== self::STRING) {
            throw new \Dogma\InvalidSizeException($type, $size, []);
        }
    }

    private static function checkSpecial(string $type, string $special = null)
    {
        if ($type === self::INT || $type === self::FLOAT) {
            if ($special !== Sign::SIGNED && $special !== Sign::UNSIGNED) {
                throw new \Dogma\InvalidTypeException($type, sprintf('%s(%s)', $type, $special));
            }
        } elseif ($type !== self::STRING) {
            throw new \Dogma\InvalidTypeException($type, sprintf('%s(%s)', $type, $special));
        }
    }

    public static function bool(bool $nullable = false): self
    {
        return self::get(self::BOOL, $nullable);
    }

    public static function int($size = null, $sign = null, bool $nullable = false): self
    {
        if (is_bool($size)) {
            $nullable = $size;
            $size = null;
        } elseif (is_bool($sign)) {
            $nullable = $sign;
            $sign = null;
        }
        return self::get(self::INT, $size, $sign, $nullable);
    }

    public static function float($size = null, bool $nullable = false): self
    {
        if (is_bool($size)) {
            $nullable = $size;
            $size = null;
        }
        return self::get(self::FLOAT, $size, $nullable);
    }

    public static function string($size = null, $collation = null, bool $nullable = false): self
    {
        if (is_bool($size)) {
            $nullable = $size;
            $size = null;
        } elseif (is_bool($collation)) {
            $nullable = $collation;
            $collation = null;
        }
        return self::get(self::STRING, $size, $collation, $nullable);
    }

    public static function callable(bool $nullable = false): self
    {
        return self::get(self::PHP_CALLABLE, $nullable);
    }

    /**
     * @param string|self $itemType
     * @param bool $nullable
     * @return self
     */
    public static function arrayOf($itemType, bool $nullable = false): self
    {
        Check::types($itemType, [Type::STRING, Type::class]);

        return self::collectionOf(self::PHP_ARRAY, $itemType, $nullable);
    }

    /**
     * @param string $type
     * @param string|self $itemType
     * @param bool $nullable
     * @return self
     */
    public static function collectionOf(string $type, $itemType, bool $nullable = false): self
    {
        Check::types($itemType, [Type::STRING, Type::class]);

        if (!$itemType instanceof self) {
            $itemType = self::get($itemType);
        }

        $id = $type . '<' . $itemType->getId() . '>' . ($nullable ? '?' : '');
        if (empty(self::$instances[$id])) {
            $that = new self($id, $type, $itemType, $nullable);
            self::$instances[$id] = $that;
        }

        return self::$instances[$id];
    }

    /**
     * @param string|self ...$itemTypes
     * @param bool $nullable
     * @return self
     */
    public static function tupleOf(...$arguments): self
    {
        $nullable = false;
        if (end($arguments) === true) {
            $nullable = true;
            array_pop($arguments);
        }

        Check::itemsOfTypes($arguments, [Type::STRING, Type::class]);

        $itemIds = [];
        foreach ($arguments as &$type) {
            if (!$type instanceof self) {
                $itemIds[] = $type;
                $type = self::get($type);
            } else {
                $itemIds[] = $type->getId();
            }
        }

        $id = Tuple::class . '<' . implode(',', $itemIds) . '>' . ($nullable ? '?' : '');
        if (empty(self::$instances[$id])) {
            $that = new self($id, Tuple::class, $arguments, $nullable);
            self::$instances[$id] = $that;
        }

        return self::$instances[$id];
    }

    /**
     * Converts string in syntax like "Foo<Bar,Baz<int>>" to a Type instance
     */
    public static function fromId(string $id): self
    {
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        if (!preg_match('/^([^(<?]+)(?:\\(([^)]+)\\))?(?:<(.*)>)?(\\?)?$/', $id, $match)) {
            throw new \Dogma\InvalidTypeDefinitionException($id);
        }
        $match = Arr::padTo($match, 5, false);
        list(, $baseId, $params, $itemIds, $nullable) = $match;
        $nullable = (bool) $nullable;

        $size = null;
        $special = null;
        if ($params) {
            $params = explode(',', $params);
            if (is_numeric($params[0])) {
                $size = (int) $params[0];
                if (isset($params[1])) {
                    $special = $params[1];
                }
            } else {
                $special = $params[0];
                if (isset($params[1])) {
                    throw new \Dogma\InvalidTypeDefinitionException($id);
                }
            }
            if ($size) {
                self::checkSize($baseId, $size);
            }
            if ($special) {
                self::checkSpecial($baseId, $special);
            }
        }

        if (!$itemIds) {
            return self::get($baseId, $size, $special, $nullable);
        }

        $itemIds = preg_split('/(?<![0-9]),/', $itemIds);
        $last = 0;
        $counter = 0;
        foreach ($itemIds as $i => $type) {
            $carry = strlen($type) - strlen(str_replace(['<', '>'], ['', '  '], $type));
            if ($counter === 0 && $carry > 0) {
                $last = $i;
            } elseif ($counter > 0) {
                unset($itemIds[$i]);
                $itemIds[$last] .= ',' . $type;
            }
            $counter += $carry;
        }

        $itemTypes = [];
        foreach ($itemIds as $id) {
            $itemTypes[] = self::fromId($id);
        }

        if ($baseId === Tuple::class) {
            if ($nullable) {
                $itemTypes[] = $nullable;
            }
            return self::tupleOf(...$itemTypes);
        } else {
            if (count($itemTypes) !== 1) {
                throw new \Dogma\InvalidTypeDefinitionException($id);
            }
            if ($baseId === Type::PHP_ARRAY) {
                return self::arrayOf($itemTypes[0], $nullable);
            } else {
                return self::collectionOf($baseId, $itemTypes[0], $nullable);
            }
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns new instance of type. Works only on simple class types with public constructor.
     * @param mixed ...$arguments
     * @return object
     */
    public function getInstance(...$arguments)
    {
        $className = $this->type;

        return new $className(...$arguments);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isScalar(): bool
    {
        return in_array($this->type, self::listScalarTypes());
    }

    public function isArray(): bool
    {
        return $this->type === Type::PHP_ARRAY;
    }

    public function isCollection(): bool
    {
        return $this->itemType && $this->type !== Type::PHP_ARRAY && $this->type !== Tuple::class;
    }

    public function isTuple(): bool
    {
        return $this->type === Tuple::class;
    }

    public function isClass(): bool
    {
        return !in_array($this->type, self::listTypes());
    }

    public function is(string $typeName): bool
    {
        return $this->type === $typeName;
    }

    public function isImplementing(string $interfaceName): bool
    {
        return $this->type === $interfaceName || is_subclass_of($this->type, $interfaceName);
    }

    public function getName(): string
    {
        return $this->type;
    }

    /**
     * Returns base of the type (without nullable and items)
     */
    public function getBaseType(): self
    {
        return self::get($this->type);
    }

    public function getNonNullableType(): self
    {
        switch (true) {
            case !$this->nullable:
                return $this;
            case $this->isArray():
                return self::collectionOf(self::PHP_ARRAY, $this->itemType);
            case $this->isCollection():
                return self::collectionOf($this->type, $this->itemType);
            case $this->isTuple():
                return self::tupleOf(...$this->itemType);
            default:
                return self::get($this->type);
        }
    }

    /**
     * Returns type of items or array of types for Tuple
     * @return self|self[]|null
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * List of types and pseudotypes, that can be used in annotations. Does not include 'null' and 'void'
     * @return string[]
     */
    public static function listTypes(): array
    {
        return [
            self::BOOL,
            self::INT,
            self::FLOAT,
            self::NUMERIC,
            self::STRING,
            self::SCALAR,
            self::MIXED,
            self::PHP_ARRAY,
            self::OBJECT,
            self::PHP_CALLABLE,
            self::RESOURCE,
        ];
    }

    /**
     * List of native PHP types. Does not include 'null'.
     * @return string[]
     */
    public static function listNativeTypes(): array
    {
        return [
            self::BOOL,
            self::INT,
            self::FLOAT,
            self::STRING,
            self::PHP_ARRAY,
            self::OBJECT,
            self::PHP_CALLABLE,
            self::RESOURCE,
        ];
    }

    /**
     * List of native PHP scalar types and pseudotype 'numeric'.
     * @return string[]
     */
    public static function listScalarTypes(): array
    {
        return [
            self::BOOL,
            self::INT,
            self::FLOAT,
            self::NUMERIC,
            self::STRING,
        ];
    }

    /**
     * @return self[]
     */
    public static function getDefinedTypes(): array
    {
        return self::$instances;
    }

}
