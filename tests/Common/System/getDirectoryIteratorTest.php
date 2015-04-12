<?php
namespace tests\Common\System;

use MySQLExtractor\Common\System;

class getDirectoryIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // reset mock
        $system = new System();

        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, null);
    }

    /**
     * when mocking the internal method then return mocked value
     */
	public function testWhenMockingTheInternalMethodThenReturnMockedValue()
    {
        $path = '/dir/to/path';
        $expected = array('something');

        $systemMock = \Mockery::mock('\\MySQLExtractor\\Common\\SystemMock')->makePartial();
        $systemMock->shouldReceive('getDirectoryIterator')->with($path)->andReturn($expected);

        $system = new System();
        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, $systemMock);

        $this->assertEquals($expected, $system::getDirectoryIterator($path));
    }

    /**
     * when not mocking then return DirectoryIterator
     */
	public function testWhenNotMockingThenReturnDirectoryIterator()
    {
        $system = new System();
        $return = $system::getDirectoryIterator('/tmp');

        $this->assertInstanceOf('\\DirectoryIterator', $return);
    }
}
