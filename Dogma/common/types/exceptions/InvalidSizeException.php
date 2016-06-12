<?php

namespace Dogma;

class InvalidSizeException extends \Dogma\Exception
{

    /**
     * @param string
     * @param int
     * @param int[]
     * @param \Throwable|null
     */
    public function __construct(string $type, int $actualSize, array $allowedSizes, \Throwable $previous = null)
    {
        if (!$allowedSizes) {
            parent::__construct(sprintf('Size parameter is not allowed on type %s.', $type), $previous);
        } else {
            $sizes = implode(', ', $allowedSizes);

            parent::__construct(sprintf('Size %s is not valid for type %s. Use one of these values: %s.', $actualSize, $type, $sizes), $previous);
        }
    }

}
