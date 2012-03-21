<?php

/**
 * Test: Nette\Database\SqlPreprocessor: order
 *
 * @author     Vlasta Neubauer
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/../connect.inc.php';


$processor = $connection->getSqlPreprocessor();


Assert::same(array("SELECT ... ORDER BY `a`, `b` DESC, `c`, `d` DESC", array()), 
	$processor->process("SELECT ... ORDER BY ?", 
		array(
			array('a' => TRUE, 'b' => FALSE, 'c' => 1, 'd' => -1)
		)
	)
);

Assert::same(array("SELECT ... ORDER BY `a`, `b` DESC, `c`, `d` DESC", array()), 
	$processor->process("SELECT ... ORDER BY ", 
		array(
			array('a' => TRUE, 'b' => FALSE, 'c' => 1, 'd' => -1)
		)
	)
);