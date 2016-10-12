<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Identity;

use Dogma\Check;

/**
 * Simple UUID crate. Not generator, neither parser.
 */
class Uuid
{

    public const TYPE_TIME_MAC = 1;
    public const TYPE_TIME_MAC_DCE = 2;
    public const TYPE_NAMESPACE_MD5 = 3;
    public const TYPE_RANDOM = 4;
    public const TYPE_NAMESPACE_SHA1 = 5;

    public const FORMATTED_UUID_PATTERN = '[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}';

    /** @var int|null */
    private $type;

    /** @var string (16,binary) */
    private $value;

    /** @var string (36,ascii) */
    private $formatted;

    private function __construct()
    {
        // pass
    }

    public static function fromValue(string $value, ?int $type = null): self
    {
        Check::length($value, 16, 16);

        $self = new static();
        $self->type = $type;
        $self->value = $value;
        $self->formatted = self::format($value);

        return $self;
    }

    public static function fromFormatted(string $formatted, ?int $type = null): self
    {
        $formatted = strtoupper($formatted);
        Check::match($formatted, '/^' . self::FORMATTED_UUID_PATTERN . '$/');

        $self = new static();
        $self->type = $type;
        $self->value = self::unformat($formatted);
        $self->formatted = $formatted;

        return $self;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        return $this->formatted;
    }

    public function getHexadec(): string
    {
        return str_replace('-', '', $this->formatted);
    }

    public static function format(string $value): string
    {
        $hexadec = unpack('H*', $value);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hexadec, 0, 8),
            substr($hexadec, 8, 4),
            substr($hexadec, 12, 4),
            substr($hexadec, 16, 4),
            substr($hexadec, 20, 12)
        );
    }

    public static function unformat(string $value): string
    {
        $value = str_replace('-', '', $value);

        return pack('H*', $value);
    }

    public function generateShort(string $uuid): string
    {
        $plain = str_replace('-', '', $uuid);
        $binary = hex2bin($plain);
        $base64 = base64_encode($binary);

        return str_replace(['+', '/', '='], ['-', '_', ''], $base64);
    }

}
