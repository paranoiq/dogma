<?php

define('DOGMA_DIR', __DIR__);

require_once NETTE_DIR . '/loader.php';

require_once __DIR__ . '/common/helpers.php';
require_once __DIR__ . '/common/DogmaLoader.php';


Dogma\DogmaLoader::getInstance()->register();

