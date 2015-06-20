<?php

namespace Dogma\Reflection;

use Dogma\Type;
use ReflectionMethod;

class MethodTypeParser implements \Dogma\NonIterable
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;

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
            throw new \Dogma\Reflection\UnprocessableParameterException($method, 'Variadic and by reference parameters are not supported.');
        }
        $itemTypes = [];
        $containerTypes = [];
        $otherTypes = [];
        foreach ($options['types'] as $type) {
            $typeParts = explode('[', $type);
            if (($count = count($typeParts)) > 2) {
                throw new \Dogma\Reflection\UnprocessableParameterException($method, 'Multidimensional arrays are not supported.');
            } elseif ($count === 2) {
                $itemTypes[] = $typeParts[0];
            } elseif ($type === Type::PHP_ARRAY || is_subclass_of($type, \Traversable::class)) {
                $containerTypes[] = $type;
            } else {
                $otherTypes[] = $type;
            }
        }
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
            } else {
                $types = [];
            }

            $nullable = $paramRef->isDefaultValueAvailable() && $paramRef->getDefaultValue() === null;

            $items[$paramRef->getName()] = [
                'types' => $types,
                'nullable' => $nullable,
                'reference' => $paramRef->isPassedByReference(),
                'variadic' => $paramRef->isVariadic(),
                'optional' => $paramRef->isOptional()
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
                @list(, $mod1, $types, $mod2, $name) = $matches;
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

    private function checkType(string $type, ReflectionMethod $method): string
    {
        $lower = strtolower($type);
        if ($lower === 'self' || $lower === 'static') {
            return $method->getDeclaringClass()->getName();
        } elseif (!in_array(rtrim($lower, '[]'), $this->typeList, Type::STRICT)) {
            if (substr($type, 0, 1) !== '\\' || !class_exists($type = trim($type, '\\'))) {
                throw new InvalidMethodAnnotationException($method, sprintf('Unknown class %s. Make sure that you use fully qualified class names.', $type));
            }
            return ltrim($type, '\\');
        } else {
            return $lower;
        }
    }

}
