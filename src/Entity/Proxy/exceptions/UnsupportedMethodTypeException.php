<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Proxy;

class UnsupportedMethodTypeException extends \Dogma\Exception implements \Dogma\Entity\Exception
{

    /** @var string */
    private $className;

    /** @var string */
    private $methodName;

    /** @var string */
    private $option;

    public function __construct(string $className, string $methodName, string $option, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Cannot generate proxy for %s method %s::%s.', $option, $className, $methodName), $previous);

        $this->className = $className;
        $this->methodName = $methodName;
        $this->option = $option;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getOption(): string
    {
        return $this->option;
    }

}
