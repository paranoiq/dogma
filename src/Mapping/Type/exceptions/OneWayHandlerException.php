<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Exception;
use function get_class;
use function sprintf;

class OneWayHandlerException extends Exception implements MappingTypeException
{

    /** @var mixed */
    private $instance;

    /** @var \Dogma\Mapping\Type\TypeHandler */
    private $handler;

    /**
     * @param mixed $instance
     * @param \Dogma\Mapping\Type\TypeHandler $handler
     * @param \Throwable|null $previous
     */
    public function __construct($instance, TypeHandler $handler, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cannot export an instance of %s using one way type handler %s.', get_class($instance), get_class($handler)),
            $previous
        );
        $this->instance = $instance;
        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    public function getHandler(): TypeHandler
    {
        return $this->handler;
    }

}
