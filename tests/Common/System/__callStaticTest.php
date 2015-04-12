<?php
namespace tests\Common\System;

use MySQLExtractor\Common\System;

class __callStaticTest extends \PHPUnit_Framework_TestCase
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
        $systemMock = \Mockery::mock('\\MySQLExtractor\\Common\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with('/invalid-folder/')->andReturn(true);

        $system = new System();

        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, $systemMock);

        $this->assertTrue($system::file_exists('/invalid-folder/'));
    }

    /**
     * when not mocking then use the internal method
     */
    public function testWhenNotMockingThenUseTheInternalMethod()
    {
        $system = new System();
        $this->assertFalse($system::file_exists('/invalid-folder/'));
        $this->assertEquals($system::time(), time());
    }
}
