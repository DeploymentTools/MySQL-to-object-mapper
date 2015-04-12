<?php
namespace tests\Extractor\Databases;

use MySQLExtractor\Extractor\Databases;

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

    /**
     * when input filename matches parameters then return matched db block
     * @dataProvider validMatchingDatabaseFilenames
     */
    public function testWhenInputFilenameMatchesParametersThenReturnMatchedDbBlock($filename, $expectedDB)
    {
        $DatabaseExtractor = new Databases();

        $class = new \ReflectionClass(get_class($DatabaseExtractor));
        $method = $class->getMethod('getDatabaseName');
        $method->setAccessible(true);
        $return = $method->invokeArgs($DatabaseExtractor, array($filename));

        $this->assertEquals($expectedDB, $return);
    }

    public function validMatchingDatabaseFilenames()
    {
        return array(
            array('/path/to/backup/database1.table1.sql', 'database1'),
            array('/path/to/backup/database_2.sql', 'database_2'),
            array('/path/fooling/database_fool1.sql/backup/database_3.sql', 'database_3'),
            array('/path/fooling/database_fool2.sql/backup/database_4.table_2.sql', 'database_4')
        );
    }
}
