<?php
namespace tests\Driver\Server;
use \MySQLExtractor\Common\System;
use \MySQLExtractor\Driver\Server;
use MySQLExtractor\Exceptions\InvalidSourceException;

/**
 * @runInSeparateProcess
 */
class extractRemoteDatabasesTest extends \PHPUnit_Framework_TestCase
{
    protected $mysqlCredentials;
    protected $PDOMock;
    protected $object;
    protected $objectHelper;
    protected $table = array(
        'table_1' => "CREATE TABLE `wp_users` (
            `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_login` varchar(60) NOT NULL DEFAULT '',
            PRIMARY KEY (`ID`),
            KEY `user_login_key` (`user_login`)
        ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8",

        'table_2' => "CREATE TABLE `wp_users_2` (
            `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_login` varchar(60) NOT NULL DEFAULT '',
            PRIMARY KEY (`ID`),
            KEY `user_login_key` (`user_login`)
        ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8",

        'table_3' => "CREATE TABLE `wp_users_3` (
            `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT
            PRIMARY KEY (`ID`)
        )"
    );

    public function setUp()
    {
        $this->mysqlCredentials = new \stdClass;
        $this->mysqlCredentials->host = 'mysql.host';
        $this->mysqlCredentials->dbuser = 'user';
        $this->mysqlCredentials->dbpass = 'pass';
        $this->mysqlCredentials->dbname = 'database1';

        $this->PDOMock = \Mockery::mock('\\PDOMock')->makePartial();

        $databaseEntries = array(
            array('database1'),
            array('database2')
        );
        $this->PDOMock->shouldReceive('query')->with('SHOW DATABASES')->andReturn($databaseEntries);
        $this->PDOMock->shouldReceive('query')->with('use database1');
        $this->PDOMock->shouldReceive('query')->with('use database2');

        $this->PDOMock->shouldReceive('query')->with('SHOW TABLES')->once()->andReturn(array(
            array('table1'),
            array('table2')
        ));

        $this->PDOMock->shouldReceive('query')->with('SHOW TABLES')->once()->andReturn(array(
            array('table3')
        ));

        $database1_table1_TablesEntries = array(
            'Create Table' => $this->table['table_1']
        );

        $database1_table2_TablesEntries = array(
            'Create Table' => $this->table['table_2']
        );

        $database2_table3_TablesEntries = array(
            'Create Table' => $this->table['table_3']
        );

        $database1_table1_TablesEntries_Mock = \Mockery::mock('\\PDOQueryMock')->makePartial();
        $database1_table1_TablesEntries_Mock->shouldReceive('fetch')->andReturn($database1_table1_TablesEntries);

        $database1_table2_TablesEntries_Mock = \Mockery::mock('\\PDOQueryMock')->makePartial();
        $database1_table2_TablesEntries_Mock->shouldReceive('fetch')->andReturn($database1_table2_TablesEntries);

        $database2_table3_TablesEntries_Mock = \Mockery::mock('\\PDOQueryMock')->makePartial();
        $database2_table3_TablesEntries_Mock->shouldReceive('fetch')->andReturn($database2_table3_TablesEntries);

        $this->PDOMock->shouldReceive('query')->with('SHOW CREATE TABLE `table1`')->andReturn($database1_table1_TablesEntries_Mock);
        $this->PDOMock->shouldReceive('query')->with('SHOW CREATE TABLE `table2`')->andReturn($database1_table2_TablesEntries_Mock);
        $this->PDOMock->shouldReceive('query')->with('SHOW CREATE TABLE `table3`')->andReturn($database2_table3_TablesEntries_Mock);

        $systemMock = \Mockery::mock('\\MySQLExtractor\\SystemMock')->makePartial();
        $systemMock->shouldReceive('getPDO')->with($this->mysqlCredentials)->andReturn($this->PDOMock);
        $systemMock->shouldReceive('flush');

        $systemMockHelper = new \PHPUnitProtectedHelper(new System());
        $systemMockHelper->setValue('mock', $systemMock);

        $this->object = new Server($this->mysqlCredentials);
        $this->objectHelper = new \PHPUnitProtectedHelper($this->object);
    }

    /**
     * calling the method will fill the entries if found
     */
    public function testCallingTheMethodWillFillTheEntriesIfFound()
    {
        $this->object->execute();

        $expected = array(
            'database1.table1.sql' => $this->table['table_1'],
            'database1.table2.sql' => $this->table['table_2']
        );

        $entries = $this->objectHelper->getValue('entries');
        $this->assertEquals($expected, $entries->toArray());
    }

	/**
	 * calling from application will trigger execution
	 */
	public function testCallingFromApplicationWillTriggerExecution()
	{
		$application = new \MySQLExtractor\Application();
        $application->processServer($this->mysqlCredentials);

        $helper = new \PHPUnitProtectedHelper($application);
        $extractor = $helper->getValue('extractor');

        $databases = $extractor->databases();
        $this->assertEquals(1, count($databases)); // will filter based on the mysqlCredentials->dbname

        $this->assertInstanceOf('\\MySQLExtractor\\Presentation\\Database', $databases['database1']);
	}

    /**
     * calling the method will throw exception if no entries are found
     */
    public function testCallingTheMethodWillThrowExceptionIfNoEntriesAreFound()
    {
        $this->mysqlCredentials->dbname = 'none-existing-database';
        $object = new ServerUnderTest($this->mysqlCredentials);

        try {
            $object->execute();
            $this->fail();

        } catch (InvalidSourceException $e) {
            $this->assertEquals('There were no entries found.', $e->getMessage());
        }
    }
}

class ServerUnderTest extends Server
{
    public function extractRemoteDatabases()
    {
        // will not append any sources
    }
}