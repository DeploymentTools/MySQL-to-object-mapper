<?php
namespace tests\Extractor\Databases;

use MySQLExtractor\Extractor\Databases;

class getTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when calling the method using three files for two database then return an array of DatabaseItems with the two names and the recovered tables
     */
    public function testWhenCallingTheMethodUsingThreeFilesForTwoDatabaseThenReturnAnArrayOfDatabaseItemsWithTheTwoNamesAndTheRecoveredTables()
    {
        $DatabaseExtractor = new Databases();
        $filesCollection = new \MySQLExtractor\Common\Collection(array(
            '/path/to/db1.table1.sql' => 'CREATE TABLE `anunturi_angajare` (`id` int(11) NOT NULL AUTO_INCREMENT)',
            '/path/to/db1.table2.sql' => 'CREATE TABLE `anunturi` (`id` int(11) NOT NULL AUTO_INCREMENT)',
            '/path/to/db2.sql' => 'contents file 3',
        ));

        $extractor = $DatabaseExtractor->from($filesCollection);
        $response = $extractor->get();

        $this->assertTrue(is_array($response) && count($response) == 2);

        $entry1 = array_shift($response);
        $entry2 = array_shift($response);

        $this->assertInstanceOf('\\MySQLExtractor\\Presentation\\Database', $entry1);
        $this->assertInstanceOf('\\MySQLExtractor\\Presentation\\Database', $entry2);

        $this->assertEquals('db1', $entry1->Name);
        $this->assertEquals('db2', $entry2->Name);

        $this->assertEquals(2, count($entry1->Tables));
        $this->assertEquals(0, count($entry2->Tables));
    }
}
