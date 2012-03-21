<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Caching
 * @subpackage UnitTests
 */



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/../../../Dogma/Database/Connection.php';
require __DIR__ . '/../../../Dogma/Database/Table/Selection.php';
require __DIR__ . '/../../../Dogma/Database/Table/GroupedSelection.php';
require __DIR__ . '/../../../Dogma/Database/Table/ActiveRow.php';
require __DIR__ . '/../../../Dogma/Language/Inflector.php';
require __DIR__ . '/../../../Dogma/types/time/DateTime.php';
require __DIR__ . '/../../../Dogma/types/time/Date.php';



try {
	$connection = new Dogma\Database\Connection('mysql:host=testing', 'test', 'i86kyutytr3e2');
	$connection->loadFile(__DIR__ . '/nette_test.sql');

} catch (PDOException $e) {
	TestHelpers::skip('Requires corretly configured mysql connection and "nette_test" database.');

}
