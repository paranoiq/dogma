<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Proxy;

class UnsupportedClassTypeException extends \Dogma\Exception implements \Dogma\Entity\Exception
{

    /** @var string */
    private $className;

    /** @var string */
    private $option;

    /**
     * @param string $className
     * @param string $option
     * @param \Throwable $previous
     */
    public function __construct(string $className, string $option, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Cannot generate proxy for %s class %s.', $option, $className), $previous);

        $this->className = $className;
        $this->option = $option;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getOption(): string
    {
        return $this->option;
    }

}
