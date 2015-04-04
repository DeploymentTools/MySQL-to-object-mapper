<?php
namespace tests\Presentation\Database;

use MySQLExtractor\Presentation\Database;

class appendTablesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when appending tables then update
     */
	public function testWhenAppendingTablesThenUpdate()
    {
        $tables = array(
            'table_1' => '...1',
            'table_2' => '...2'
        );

        $database = new Database('dbname', $tables);
        $database->appendTables(array(
            'table_3' => '...3',
            'table_4' => '...4'
        ));

        $expected = array(
            'table_1' => '...1',
            'table_2' => '...2',
            'table_3' => '...3',
            'table_4' => '...4'
        );

        $this->assertEquals($expected, $database->Tables);
    }
}
