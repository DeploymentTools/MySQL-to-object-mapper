<?php
namespace tests\Driver\Server;
use \MySQLExtractor\Common\System;
use \MySQLExtractor\Driver\Server;

/**
 * @runInSeparateProcess
 */
class __constructTest extends \PHPUnit_Framework_TestCase
{
    protected $mysqlCredentials;

    public function setUp()
    {
        $this->mysqlCredentials = new \stdClass;
        $this->mysqlCredentials->host = 'mysql.host:3306';
        $this->mysqlCredentials->dbuser = 'user';
        $this->mysqlCredentials->dbpass = 'pass';
        $this->mysqlCredentials->dbname = 'dbname';
    }

    /**
     * when constructor is called then initialize the PDO object using the input credentials
     */
    public function testWhenConstructorIsCalledthenInitializeThePDOObjectUsingTheInputCredentials()
    {
        $PDOMock = \Mockery::mock('\\PDO')->makePartial();

        $systemMock = \Mockery::mock('\\MySQLExtractor\\SystemMock')->makePartial();
        $systemMock->shouldReceive('getPDO')->with($this->mysqlCredentials)->andReturn($PDOMock);

        $systemMockHelper = new \PHPUnitProtectedHelper(new System());
        $systemMockHelper->setValue('mock', $systemMock);

        $object = new Server($this->mysqlCredentials);
        $objectHelper = new \PHPUnitProtectedHelper($object);

        $this->assertSame($PDOMock, $objectHelper->getValue('PDO'));
    }

    /**
     * when pdo connection is not mocked then throw PDOException if invalid host
     */
    public function testWhenPDOConnectionIsNotMockedThenThrowPDOExceptionIfInvalidHost()
    {
        $systemMockHelper = new \PHPUnitProtectedHelper(new System());
        $systemMockHelper->setValue('mock', null);

        try {
            $object = new Server($this->mysqlCredentials);
            $this->fail();

        } catch (\PDOException $e) {
            $this->assertEquals("SQLSTATE[HY000] [2005] Unknown MySQL server host 'mysql.host:3306' (2)", $e->getMessage());
        }
    }
}
