<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Proxy;

use Dogma\Entity\Entity;
use Dogma\Php\PhpGenerator\ClassType as ClassGenerator;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Type;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Property;

class EntityProxyBuilder
{
    use \Dogma\StrictBehaviorMixin;

    public const CLASS_NAME_SUFFIX = 'Proxy';

    /** @var \Dogma\Reflection\MethodTypeParser */
    private $methodTypeParser;

    public function __construct(MethodTypeParser $methodTypeParser)
    {
        $this->methodTypeParser = $methodTypeParser;
    }

    public function registerAutoloader(bool $prepend = false): void
    {
        spl_autoload_register([$this, 'autoloadProxy'], true, $prepend);
    }

    public function autoloadProxy(string $proxyClass): void
    {
        /// temporary solution
        if (substr($proxyClass, 0, -5) !== self::CLASS_NAME_SUFFIX) {
            return;
        }
        $entityClass = substr($proxyClass, 0, -5);
        if (!class_exists($entityClass)) {
            return;
        }
        $proxyCode = $this->buildProxy(Type::get($entityClass));

        eval($proxyCode);
    }

    public function buildProxy(Type $type): string
    {
        $generator = $this->buildProxyGenerator($type);

        $nameParts = explode('\\', $type->getName());
        array_pop($nameParts);
        $namespace = implode('\\', $nameParts);

        return $namespace
            ? 'namespace ' . $namespace . " {\n\n" . (string) $generator . "\n}\n"
            : (string) $generator;
    }

    private function buildProxyGenerator(Type $type): ClassGenerator
    {
        if (!$type->isImplementing(Entity::class)) {
            throw new \Dogma\InvalidTypeException(Entity::class, $type->getName());
        }

        $class = new \ReflectionClass($type->getName());
        if ($class->isFinal() || $class->isAbstract()) {
            throw new \Dogma\Entity\Proxy\UnsupportedClassTypeException($class->getName(), $class->isFinal() ? 'final' : 'abstract');
        }

        $classGenerator = ClassGenerator::from($class);

        $classGenerator->setName($class->getShortName() . self::CLASS_NAME_SUFFIX);
        $classGenerator->addExtend($type->getName());
        $classGenerator->addImplement(EntityProxy::class);
        $classGenerator->addTrait(EntityProxyMixin::class);
        $classGenerator->addConst('ENTITY_CLASS', $class->getName());

        $classGenerator->setProperties([
            (new Property('identity'))->setVisibility('private'),
            (new Property('entity'))->setVisibility('private'),
            (new Property('entityMap'))->setVisibility('private'),
        ]);

        $methods = [];
        foreach ($classGenerator->getMethods() as $methodGenerator) {
            $method = $class->getMethod($methodGenerator->getName());

            if ($method->isPrivate() || $method->isConstructor() || $method->isDestructor() || $method->getName() === 'getIdentity') {
                continue;
            } elseif ($method->isFinal() || $method->isStatic()) {
                throw new \Dogma\Entity\Proxy\UnsupportedMethodTypeException($class->getName(), $method->getName(), $method->isFinal() ? 'final' : 'static');
            }

            $body = "\$_entity = \$this->getEntity();\n";
            $parentCall = $this->buildParentCall($methodGenerator);
            $body .= '$_result = ' . $parentCall . "\n";
            $body .= 'return $_result;';

            $methodGenerator->setBody($body);

            $methods[] = $methodGenerator;
        }
        $classGenerator->setMethods($methods);

        return $classGenerator;
    }

    private function buildParentCall(Method $methodGenerator): string
    {
        $params = [];
        foreach ($methodGenerator->getParameters() as $parameterGenerator) {
            /// todo: not implemented yet
            //if ($parameterGenerator->isVariadic()) {
            //    $params[] = '...$' . $parameterGenerator->getName();
            //} else {
                $params[] = '$' . $parameterGenerator->getName();
            //}
        }
        return $methodGenerator->getName() . '(' . implode(', ', $params) . ');';
    }

}
