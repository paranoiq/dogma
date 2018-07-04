<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Reflection;

use Dogma\NonIterable;
use Dogma\NonIterableMixin;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use ReflectionMethod;
use function array_keys;
use function array_merge;
use function array_search;
use function array_unique;
use function array_values;
use function class_exists;
use function count;
use function explode;
use function in_array;
use function is_subclass_of;
use function ltrim;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sprintf;
use function strstr;
use function strtolower;
use function trim;

class MethodTypeParser implements NonIterable
{
    use StrictBehaviorMixin;
    use NonIterableMixin;

    /** @var string[] */
    private $typeList;

    public function __construct()
    {
        $this->typeList = Type::listTypes();
    }

    /**
     * @param \ReflectionMethod $method
     * @return \Dogma\Type[]
     */
    public function getTypes(ReflectionMethod $method): array
    {
        $raw = $this->getTypesRaw($method);

        $types = [];
        foreach ($raw as $name => $options) {
            $types[$name] = $this->createType($options, $method);
        }

        return $types;
    }

    /**
     * @param \ReflectionMethod $method
     * @return \Dogma\Type[]
     */
    public function getParameterTypes(ReflectionMethod $method): array
    {
        $raw = $this->getTypesRaw($method);

        $types = [];
        foreach ($raw as $name => $options) {
            if ($name === '@return') {
                continue;
            }
            $types[$name] = $this->createType($options, $method);
        }

        return $types;
    }

    /**
     * @param mixed[] $options
     * @param \ReflectionMethod $method
     * @return \Dogma\Type
     */
    private function createType(array $options, ReflectionMethod $method): Type
    {
        if (!empty($options['reference']) || !empty($options['variadic'])) {
            throw new UnprocessableParameterException($method, 'Variadic and by reference parameters are not supported.');
        }
        $itemTypes = [];
        $containerTypes = [];
        $otherTypes = [];
        foreach ($options['types'] as $type) {
            $typeParts = explode('[', $type);
            $count = count($typeParts);
            if ($count > 2) {
                throw new UnprocessableParameterException($method, 'Multidimensional arrays are not supported.');
            } elseif ($count === 2) {
                $itemTypes[] = $typeParts[0];
            } elseif ($type === Type::PHP_ARRAY || is_subclass_of($type, \Traversable::class)) {
                $containerTypes[] = $type;
            } elseif (strstr($type, '(')) {
                $typeBase = explode('(', $type)[0];
                if (in_array($typeBase, $otherTypes)) {
                    unset($otherTypes[array_search($typeBase, $otherTypes)]);
                }
                $otherTypes[] = $type;
            } else {
                $add = true;
                foreach ($otherTypes as $otherType) {
                    $otherTypeBase = explode('(', $otherType)[0];
                    if ($otherTypeBase === $type) {
                        $add = false;
                        break;
                    }
                }
                if ($add) {
                    $otherTypes[] = $type;
                }
            }
        }
        $otherTypes = array_values($otherTypes);
        if ($itemTypes && !$containerTypes) {
            $containerTypes[] = Type::PHP_ARRAY;
        } elseif ($containerTypes && !$itemTypes) {
            $itemTypes[] = Type::MIXED;
        }
        if (($containerTypes && $otherTypes) || count($containerTypes) > 1 || count($otherTypes) > 1 || count($itemTypes) > 1) {
            throw new InvalidMethodAnnotationException($method, 'Invalid combination of types.');
        } elseif ($itemTypes) {
            return Type::collectionOf($containerTypes[0], $itemTypes[0], $options['nullable']);
        } elseif ($otherTypes) {
            return Type::get($otherTypes[0], $options['nullable']);
        } else {
            return Type::get(Type::MIXED, $options['nullable']);
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @return mixed[] ($name => ($types, $nullable, $reference, $variadic, $optional))
     */
    public function getTypesRaw(ReflectionMethod $method): array
    {
        $items = [];
        $paramRefs = $method->getParameters();
        foreach ($paramRefs as $paramRef) {
            if ($paramRef->isArray()) {
                $types = [Type::PHP_ARRAY];
            } elseif ($paramRef->isCallable()) {
                $types = [Type::PHP_CALLABLE];
            } elseif ($paramRef->getClass()) {
                $types = [$paramRef->getClass()->getName()];
            } elseif ($paramRef->hasType()) {
                $types = [(string) $paramRef->getType()];
            } else {
                $types = [];
            }

            $nullable = $paramRef->isDefaultValueAvailable() && $paramRef->getDefaultValue() === null;

            $items[$paramRef->getName()] = [
                'types' => $types,
                'nullable' => $nullable,
                'reference' => $paramRef->isPassedByReference(),
                'variadic' => $paramRef->isVariadic(),
                'optional' => $paramRef->isOptional(),
            ];
        }

        $docComment = $method->getDocComment();
        if (!empty($docComment)) {
            $comments = $this->parseDocComment($docComment, $method);
            if (count($items) !== count($comments) - (isset($comments['@return']) ? 1 : 0)) {
                throw new InvalidMethodAnnotationException($method, '@param annotations count does not match with parameters count');
            }

            $names = array_keys($items);
            foreach ($comments as $i => $comment) {
                if ($i === '@return') {
                    $items[$i] = $comment;
                    continue;
                }
                $item = &$items[$names[$i]];
                if ($comment['name'] !== null && $comment['name'] !== $names[$i]) {
                    throw new InvalidMethodAnnotationException($method, 'Parameter names in annotation and in declaration does not match.');
                }
                $item['nullable'] = $item['nullable'] || $comment['nullable'];
                $item['variadic'] = $item['variadic'] || $comment['variadic'];
                $item['types'] = array_unique(array_merge($item['types'], $comment['types']));
            }
        }

        return $items;
    }

    /**
     * @param string $docComment
     * @param \ReflectionMethod $method
     * @return mixed[]
     */
    public function parseDocComment(string $docComment, ReflectionMethod $method): array
    {
        $docComment = trim(trim(trim($docComment, '/'), '*'));
        $items = [];
        foreach (explode("\n", $docComment) as $row) {
            if (strstr($row, '@param')) {
                if (!preg_match('/@param\\s+(&|[.]{3})?\\s*((?:\\\\?[^\\s\\[\\]\\|]+(?:\\[\\])*\\|?)+)\s*(&|[.]{3})?(?:\\s*\\$([^\\s]+))?/u', $row, $matches)) {
                    throw new InvalidMethodAnnotationException($method, 'invalid @param annotation format at: ' . $row);
                }
                @[, $mod1, $types, $mod2, $name] = $matches;
                $variadic = $mod1 === '...' || $mod2 === '...';
                $types = explode('|', $types);
                $nullable = false;
                foreach ($types as $i => $type) {
                    if (strtolower($type) === Type::NULL) {
                        unset($types[$i]);
                        $nullable = true;
                        continue;
                    }
                    $types[$i] = $this->checkType($type, $method);
                }

                $items[] = [
                    'name' => $name,
                    'types' => $types,
                    'nullable' => $nullable,
                    'reference' => false,
                    'variadic' => $variadic, // may be simulated by func_get_args()
                ];

            } elseif (strstr($row, '@return')) {
                if (!preg_match('/@return\\s+([^\\s]+)/u', $row, $matches)) {
                    throw new InvalidMethodAnnotationException($method, 'invalid @param annotation format at: ' . $row);
                }
                $types = explode('|', $matches[1]);
                $nullable = false;
                foreach ($types as $i => $type) {
                    if (strtolower($type) === Type::NULL) {
                        unset($types[$i]);
                        $nullable = true;
                        continue;
                    }
                    $types[$i] = $this->checkType($type, $method);
                }
                $items['@return'] = [
                    'types' => $types,
                    'nullable' => $nullable,
                ];
            }
        }

        return $items;
    }

    private function checkType(string $typeString, ReflectionMethod $method): string
    {
        if ($typeString === 'self' || $typeString === 'static') {
            return $method->getDeclaringClass()->getName();
        }

        $typeString = preg_replace('/\\(([0-9]+)u\\)/', '(\\1,unsigned)', $typeString);

        $trimmed = rtrim(ltrim($typeString, '\\'), '[]');

        $type = Type::fromId($trimmed);
        if ($type->isClass()) {
            if ($typeString[0] !== '\\') {
                throw new InvalidMethodAnnotationException($method, 'Always use fully qualified names in type annotations.');
            } elseif (!class_exists($type->getName())) {
                throw new InvalidMethodAnnotationException($method, sprintf('Unknown class %s. Make sure that you use fully qualified class names.', $typeString));
            }
        } else {
            if ($typeString[0] === '\\') {
                throw new InvalidMethodAnnotationException($method, 'Cannot prefix scalar type with backslash.');
            }
        }
        return ltrim($typeString, '\\');
    }

}
