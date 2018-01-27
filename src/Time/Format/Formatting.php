<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

class Formatting
{
    use \Dogma\StrictBehaviorMixin;

    public const ESCAPE_CHARACTER = '\\';

    // variables inside block [] must not contain only zeros. otherwise is block removed
    // eg. "1.12.2017[ 00:00]" is converted to "1.12.2017"
    public const NO_ZEROS_GROUP_START = '[';
    public const NO_ZEROS_GROUP_END = ']';

    // variables inside block () must be different than current time. otherwise is block removed
    // eg. "1.12.(2017)" is converted to "1.12." if the current year is 2017, otherwise to "1.12.2017"
    public const OPTIONAL_GROUP_START = '(';
    public const OPTIONAL_GROUP_END = ')';

    // variables inside block {} must be different from other date from the pair. otherwise is block removed
    // eg. "1.12.{2017} - 31.12.2017" is converted to "1.12. - 31.12.2017"
    public const NO_DUPLICATION_GROUP_START = '{';
    public const NO_DUPLICATION_GROUP_END = '}';

    // print word with upper case letter
    // - eg. "c^" --> "FRI", "PÁ"
    public const UPPER_CASE_MODIFIER = '^';

    // print word with first upper case letter
    // - eg. "N!" --> "February", "Únor"
    public const FIRST_UPPER_MODIFIER = '!';

    // grammatical case used after 'at'
    // - eg. "C=" --> "at friday", "v pátek"
    public const IN_ON_AT_MODIFIER = '=';

    // grammatical case used after 'since'
    // - eg. "C<" --> "until friday", "od pátku"
    public const SINCE_MODIFIER = '>';

    // grammatical case used after 'until'
    // - eg. "C>" --> "since friday", "do pátku"
    public const UNTIL_MODIFIER = '<';

    // grammatical case used after "of" for names and ordinal suffix or dot for numbers
    // - eg. "d* N*" --> "27th of april", "27. dubna"
    // - eg. "N d*" --> "april 27th", "duben 27."
    public const ORDINAL_MODIFIER = '*';

}
