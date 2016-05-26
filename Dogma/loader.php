<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

define('DOGMA_DIR', __DIR__);

require_once __DIR__ . '/common/mixins/StrictBehaviorMixin.php';
require_once __DIR__ . '/common/DogmaLoader.php';

Dogma\DogmaLoader::getInstance()->register();
