<?php
namespace tests\DiskExtractor\Databases;

use MySQLExtractor\DiskExtractor\Databases;

class getDatabaseNameTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * when input filename does not match patterns then use standard name
	 */
	public function testWhenInputFilenameDoesNotMatchPatternsThenUseStandardName()
	{
		$DatabaseExtractor = new Databases();

        $class = new \ReflectionClass(get_class($DatabaseExtractor));
        $method = $class->getMethod('getDatabaseName');
        $method->setAccessible(true);
        $return = $method->invokeArgs($DatabaseExtractor, array('filename_with_no_pattern_matching.txt'));

        $this->assertEquals('_no_name_', $return);
	}

}