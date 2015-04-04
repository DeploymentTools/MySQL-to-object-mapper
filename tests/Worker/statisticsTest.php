<?php
namespace tests\Worker;

use MySQLExtractor\Presentation\Database;
use MySQLExtractor\Presentation\Table;
use MySQLExtractor\Worker;

class statisticsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * when entries were found then return array of messages
	 */
	public function testWhenEntriesWereFoundThenReturnArrayOfMessages()
	{
        $expected = array(
            '- database [db1] with [3] tables.',
            '- database [db2] with [2] tables.'
        );

        $worker = new Worker();

        $databases = array();

        $database = new Database();
        $database->Name = 'db1';
        $database->Tables = array(
            'table1' => new Table(),
            'table2' => new Table(),
            'table3' => new Table()
        );
        $databases[] = $database;

        $database = new Database();
        $database->Name = 'db2';
        $database->Tables = array(
            'table1' => new Table(),
            'table2' => new Table()
        );
        $databases[] = $database;

        $extractor = \Mockery::mock('\\MySQLExtractor\\DiskExtractor\\Main')->makePartial();
        $extractor->shouldReceive('databases')->andReturn($databases);

        $refObject = new \ReflectionObject($worker);
        $refProperty = $refObject->getProperty('extractor');
        $refProperty->setAccessible(true);
        $refProperty->setValue($worker, $extractor);

        $statistics = $worker->statistics();

        $this->assertEquals($expected, $statistics);
	}
}