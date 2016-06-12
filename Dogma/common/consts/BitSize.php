<?php

namespace Dogma;

class BitSize
{
    use \Dogma\StaticClassMixin;

    const BITS_8 = 8;
    const BITS_16 = 16;
    const BITS_24 = 24;
    const BITS_32 = 32;
    const BITS_64 = 64;

    const DEFAULT_INT_SIZE = PHP_INT_SIZE * 8;
    const DEFAULT_FLOAT_SIZE = self::BITS_64;

    /**
     * @return int[]
     */
    public static function getIntSizes(): array
    {
        return [
            self::BITS_8,
            self::BITS_16,
            self::BITS_24,
            self::BITS_32,
            self::BITS_64,
        ];
    }

    /**
     * @return int[]
     */
    public static function getFloatSizes(): array
    {
        return [
            self::BITS_32,
            self::BITS_64,
        ];
    }

}
