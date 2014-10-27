<?php
/**
 * Test: Nette\Database\SqlPreprocessor: update
 *
 * @author Vlasta Neubauer
 */

use Tester\Assert;

require_once __DIR__ . '/../connect.inc.php';


$processor = $connection->getSqlPreprocessor();


Assert::same(
    array('UPDATE table SET `a`=123, `b`=456', array()),
	$processor->process(
        'UPDATE table SET ?',
		array(
			array('a' => 123, 'b' => 456)
		)
	)
);

Assert::same(
    array('UPDATE table SET `a`=123, `b`=456', array()),
	$processor->process(
        'UPDATE table SET ',
		array(
			array('a' => 123, 'b' => 456)
		)
	)
);
