<?php
namespace tests\Common\System;

use MySQLExtractor\Common\System;

class getPDOTest extends \PHPUnit_Framework_TestCase
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
        $credentials = new \stdClass;
        $credentials->host = 'localhost:3306';
        $credentials->dbname = 'sample';
        $credentials->dbuser = 'root';
        $credentials->dbpass = '';

        $expected = \Mockery::mock('\\PDO')->makePartial();

        $systemMock = \Mockery::mock('\\MySQLExtractor\\Common\\SystemMock')->makePartial();
        $systemMock->shouldReceive('getPDO')->with($credentials)->andReturn($expected);

        $system = new System();
        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, $systemMock);

        $this->assertEquals($expected, $system::getPDO($credentials));
    }

    /**
     * when not mocking then return PDO
     */
    public function testWhenNotMockingThenReturnPDO()
    {
        $credentials = new \stdClass;
        $credentials->host = 'localhost.real';
        $credentials->dbname = 'sample';
        $credentials->dbuser = 'root';
        $credentials->dbpass = '';

        $system = new System();

        try {
            $return = $system::getPDO($credentials);
        } catch (\PDOException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }
}
