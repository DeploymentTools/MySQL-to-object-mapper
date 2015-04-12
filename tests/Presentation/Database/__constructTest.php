<?php
namespace tests\Presentation\Database;

use MySQLExtractor\Presentation\Database;

class __constructTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when initializing with no parameters then use null and empty array as database names and tables
     */
    public function testWhenInitializingWithNoParametersThenUseNullAndEmptyArrayAsDatabaseNamesAndTables()
    {
        $database = new Database();
        $this->assertNull($database->Name);
        $this->assertEquals(array(), $database->Tables);
    }

    /**
     * when initializing with parameters then use them as name and table array
     */
    public function testWhenInitializingWithParametersThenUseThemAsNameAndTableArray()
    {
        $tables = array(
            'table_1' => '...1',
            'table_2' => '...2'
        );

        $database = new Database('dbname', $tables);
        $this->assertEquals('dbname', $database->Name);
        $this->assertEquals($tables, $database->Tables);
    }
}