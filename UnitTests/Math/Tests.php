<?php

namespace UnitTests\Math;
class Tests extends \PHPUnit\Framework\TestCase {

	/**
	 * Test \Math class
	 */
	public function testMathStatic() {
		// test scale
		\Math::scale(5);
		$this->assertEquals(5, \Math::$scale, 'scale?');
		$this->assertEquals(\Math::double(5), 12, 'double?');
		// compare
		$this->assertEquals(\Math::compare('1', '1.001', 2), 0, 'compare?');
		$this->assertEquals(\Math::compare('2', '1.001', 2), 1, 'compare?');
		$this->assertEquals(\Math::compare('-1', '1.001', 2), -1, 'compare?');
		// add / subtract
		$result = \Math::add('1.005', '2.006', 2);
		$this->assertEquals($result, '3.01', 'add?');
		$result = \Math::subtract($result, '1.3333333', 2);
		$this->assertEquals($result, '1.67', 'subtract?');
		// multiply / divide
		$result = \Math::multiply('3.33333', '2.222222', 2);
		$this->assertEquals($result, '7.40', 'multiply?');
		$result = \Math::divide('3.33333', '2.222222', 2);
		$this->assertEquals($result, '1.49', 'divide?');
		// round
		$this->assertEquals(\Math::round('1.1111', 2), '1.11', 'round?');
		$this->assertEquals(\Math::round('1.1177', 2), '1.12', 'round?');
		// floor / ceil / truncate
		$this->assertEquals(\Math::floor('1.1111', 2), '1.11', 'floor?');
		$this->assertEquals(\Math::floor('1.1177', 2), '1.11', 'floor?');
		$this->assertEquals(\Math::floor('-1.1111', 2), '-1.12', 'floor?');
		$this->assertEquals(\Math::floor('-1.1177', 2), '-1.12', 'floor?');
		$this->assertEquals(\Math::ceil('1.1111', 2), '1.12', 'ceil?');
		$this->assertEquals(\Math::ceil('1.1177', 2), '1.12', 'ceil?');
		$this->assertEquals(\Math::ceil('-1.1111', 2), '-1.11', 'ceil?');
		$this->assertEquals(\Math::ceil('-1.1177', 2), '-1.11', 'ceil?');
		$this->assertEquals(\Math::truncate('1.1177', 2), '1.11', 'truncate?');
		$this->assertEquals(\Math::truncate('-1.1177', 2), '-1.11', 'truncate?');
		// abs
		$this->assertEquals(\Math::abs('1.1177'), '1.1177', 'abs?');
		$this->assertEquals(\Math::abs('-1.1177'), '1.1177', 'abs?');
		// sum
		$this->assertEquals(\Math::sum(['1.11', '2.22', '3.33'], 2), '6.66', 'sum?');
	}

	/**
	 * Test \Object\Value\Math class
	 */
	public function testObjectValueMath() {
		$object = new \Object\Value\Math(2, '3.33333');
		$this->assertEquals($object->result(), '3.33', 'init?');
		// add / subtract
		$object->add('2.2222')->subtract('1.11111');
		$this->assertEquals($object->result(), '4.43', 'add/subtract?');
		// multiply / divide
		$object->multiply('2.2222')->divide('1.11111');
		$this->assertEquals($object->result(), '8.85', 'multiply/divide?');
	}
}