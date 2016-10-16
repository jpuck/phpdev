<?php
use jpuck\phpdev\Functions as jp;

class FunctionsTest extends PHPUnit_Framework_TestCase {
	public function testCanExportArray(){
		$expectedStr = file_get_contents(__DIR__.'/dataProviders/array.php.txt');
		eval("\$expectedArr = $expectedStr;");

		$actualStr = jp::arr_export($expectedArr, true);
		eval("\$actualArr = $actualStr;");

		$this->assertEquals($expectedArr, $actualArr);
		$this->assertSame  ($expectedStr, $actualStr);
	}

	public function testCanExportArrayWithObject(){
		$expectedArr = require __DIR__.'/dataProviders/classA.php';
		$expectedStr = file_get_contents(__DIR__.'/dataProviders/classA.php.txt');

		$actualStr = jp::arr_export($expectedArr, true);
		eval("\$actualArr = $actualStr;");

		$this->assertEquals($expectedArr, $actualArr);
		$this->assertSame  ($expectedStr, $actualStr);
	}

	public function stringBeginsDataProvider(){
		return [
			['test','t',true],
			['test','T',false],
			['test','te',true],
			['test','tes',true],
			['test','test',true],
			['test','testy',false],
			['@special','@',true],
		];
	}

	/**
	 * @dataProvider stringBeginsDataProvider
	 */
	public function testCanCheckIfStringBeginsWithString(String $haystack, String $needle, Bool $begins){
		$this->assertSame($begins, jp::strbegins($haystack, $needle));
	}
}
