<?php

class SqlParameters extends \PHPUnit\Framework\TestCase
{
	public function testOrder() {
		$params = new \P4\MasterTheme\SqlParameters();

		$partB = 'part B with param 1: ' . $params->string('param 1');
		$partA = 'part A with param 2: ' . $params->int(2);
		$partC = 'part C with param 3: ' . $params->object('param 3');

		$this->assertEquals(
			'part A with param 2: %2$d,part B with param 1: \'%1$s\',part C with param 3: %3$s',
			implode(',', [$partA, $partB, $partC])
		);

		$this->assertEquals(
			['param 1', 2, 'param 3'],
			$params->get_values()
		);
	}

	public function testPrepare() {
		global $wpdb;
		$params = new \P4\MasterTheme\SqlParameters();

		$counter = 'counter = ' . $params->int(3);
		$query = 'SELECT col_a, col_b, col_c FROM ' . $params->object('my_table')
				. ' WHERE content LIKE ' . $params->string('foo')
				. ' AND ' . $counter;
		$prepared = $wpdb->prepare(
			$query,
			$params->get_values()
		);

		$this->assertEquals(
			'SELECT col_a, col_b, col_c FROM my_table'
			. ' WHERE content LIKE \'foo\' AND counter = 3',
			$prepared
		);
	}
}
