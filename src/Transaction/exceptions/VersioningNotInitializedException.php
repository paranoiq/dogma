<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Transaction;

use Dogma\ExceptionValueFormater;

class VersioningNotInitializedException extends \Dogma\Exception
{

    public function __construct(VersionAware $listener, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Versioning of %s has not been initialized yet.', ExceptionValueFormater::format($listener)), $previous);
    }

}
