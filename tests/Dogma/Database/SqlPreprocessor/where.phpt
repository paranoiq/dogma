<?php

/**
 * Test: Nette\Database\SqlPreprocessor: where
 *
 * @author     Vlasta Neubauer
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/../connect.inc.php';


$processor = $connection->getSqlPreprocessor();


Assert::same(
	array("UPDATE table WHERE (`a` IS NULL AND `b`=123 AND `c` LIKE 'text' AND `d` IN (123, 456))", array()), 
	$processor->process("UPDATE table WHERE ?", 
		array(
			array(
				'a' => NULL,
				'b' => 123,
				'c' => 'text',
				'd' => array(123, 456))
		)
	)
);

Assert::same(
	array("UPDATE table WHERE (`a` IS NULL AND `b`=123 AND `c` LIKE 'text' AND `d` IN (123, 456))", array()), 
	$processor->process("UPDATE table WHERE ", 
		array(
			array(
				'a' => NULL,
				'b' => 123,
				'c' => 'text',
				'd' => array(123, 456))
		)
	)
);
