<?php
/**
 * Test: Nette\Database\SqlPreprocessor: multiinsert
 *
 * @author Vlasta Neubauer
 */

use Tester\Assert;

require_once __DIR__ . '/../connect.inc.php';


$processor = $connection->getSqlPreprocessor();


Assert::same(
    array('INSERT INTO table (col1, col2) VALUES (123, 456), (456, 789)', array()),
	$processor->process(
        'INSERT INTO table (col1, col2) VALUES ?',
		array(
			array(
				array(123, 456),
				array(456, 789),
			)
		)
	)
);

Assert::same(
    array('INSERT INTO table (`col1`, `col2`) VALUES (123, 456), (456, 789)', array()),
	$processor->process(
        'INSERT INTO table ?',
		array(
			array(
				array('col1' => 123, 'col2' => 456),
				array('col1' => 456, 'col2' => 789),
			)
		)
	)
);

Assert::same(
    array('INSERT INTO table (col1, col2) VALUES (123, 456), (456, 789)', array()),
	$processor->process(
        'INSERT INTO table (col1, col2) VALUES ',
		array(
			array(
				array(123, 456),
				array(456, 789),
			)
		)
	)
);

Assert::same(
    array('INSERT INTO table (`col1`, `col2`) VALUES (123, 456), (456, 789)', array()),
	$processor->process(
        'INSERT INTO table ',
		array(
			array(
				array('col1' => 123, 'col2' => 456),
				array('col1' => 456, 'col2' => 789),
			)
		)
	)
);
