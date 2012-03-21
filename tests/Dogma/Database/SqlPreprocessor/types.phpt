<?php

/**
 * Test: Nette\Database\SqlPreprocessor: types
 *
 * @author     Vlasta Neubauer
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/../connect.inc.php';


$processor = $connection->getSqlPreprocessor();


Assert::same(array("INSERT INTO ... VALUES (NULL, 123, 1.234, 'abc', '2012-01-16 00:00:00')", array()), 
	$processor->process(
		"INSERT INTO ... VALUES (?, ?, ?, ?, ?)", 
		array(
			NULL,
			123,
			1.234,
			"abc",
			new DateTime('2012-01-16')
		)
	)
);

Assert::same(array("INSERT INTO ... VALUES (NULL, 123, 1.234, 'abc', '2012-01-16 00:00:00')", array()), 
	$processor->process(
		"INSERT INTO ... VALUES (",
		array(NULL, ", ", 123, ", ", 1.234, ", ", "abc", ", ", new DateTime('2012-01-16'), ")")
	)
);

