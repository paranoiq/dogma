<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Dogma\Io\Path;

/**
 * Text statistics and actions.
 * As opposed to Str, this class is used for large text bodies and receives also files as arguments.
 */
class Text
{

    /**
     * @param string|Path $text
     * @param int $position
     * @return int|null
     */
    public function getLineByPosition($text, int $position): ?int
    {
        // todo
        return 1;
    }

    /**
     * @param string|Path $text
     * @param int $position
     * @return string|null
     */
    public function getLineAtPosition($text, int $position): ?string
    {
        // todo
        return '';
    }

    /**
     * @param string|Path $text
     * @return int[]
     */
    public function getLineStarts($text): array
    {
        // todo
        return [];
    }

    /**
     * @param string|Path $text
     * @return int
     */
    public function countLines($text): int
    {
        // todo
        return 1;
    }

}
