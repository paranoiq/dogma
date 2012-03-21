<?php

/**
 * Test: Nette\Database\SqlPreprocessor: insert
 *
 * @author     Vlasta Neubauer
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/../connect.inc.php';


$processor = $connection->getSqlPreprocessor();


Assert::same(array("INSERT INTO table (col1, col2, col3) VALUES (123, 'abc', '2012-01-16 00:00:00')", array()), 
	$processor->process("INSERT INTO table (col1, col2, col3) VALUES (?)", 
		array(
			array(123, 'abc', new DateTime('2012-01-16'))
		)
	)
);

Assert::same(array("INSERT INTO table (col1, col2, col3) VALUES (123, 'abc', '2012-01-16 00:00:00')", array()), 
	$processor->process("INSERT INTO table (col1, col2, col3) VALUES ?", 
		array(
			array(123, 'abc', new DateTime('2012-01-16'))
		)
	)
);

Assert::same(array("INSERT INTO table (`col1`, `col2`, `col3`) VALUES (123, 'abc', '2012-01-16 00:00:00')", array()), 
	$processor->process("INSERT INTO table ?", 
		array(
			array('col1' => 123, 'col2' => 'abc', 'col3' => new DateTime('2012-01-16'))
		)
	)
);

Assert::same(array("INSERT INTO table (col1, col2, col3) VALUES (123, 'abc', '2012-01-16 00:00:00')", array()), 
	$processor->process("INSERT INTO table (col1, col2, col3) VALUES ", 
		array(
			array(123, 'abc', new DateTime('2012-01-16'))
		)
	)
);

Assert::same(array("INSERT INTO table (`col1`, `col2`, `col3`) VALUES (123, 'abc', '2012-01-16 00:00:00')", array()), 
	$processor->process("INSERT INTO table ", 
		array(
			array('col1' => 123, 'col2' => 'abc', 'col3' => new DateTime('2012-01-16'))
		)
	)
);
