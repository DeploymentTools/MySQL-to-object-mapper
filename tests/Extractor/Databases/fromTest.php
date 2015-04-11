<?php
namespace tests\Extractor\Databases;

use MySQLExtractor\Extractor\Databases;

class fromTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when calling the method then reinitialize the files using the input collection
     */
	public function testWhenCallingTheMethodThenReinitializeTheFilesUsingTheInputCollection()
    {
        $DatabaseExtractor = new Databases();
        $filesCollection = new \MySQLExtractor\Common\Collection(array(
            'file_1' => 'contents file 1',
            'file_2' => 'contents file 2'
        ));

        $DatabaseExtractor->from($filesCollection);

        $updatedFiles = \PHPUnit_Framework_Assert::readAttribute($DatabaseExtractor, 'files');
        $this->assertEquals($filesCollection, $updatedFiles);
    }

    /**
     * when calling the method then init the databases using an empty collection
     */
    public function testWhenCallingTheMethodThenInitTheDatabasesUsingAnEmptyCollection()
    {
        $DatabaseExtractor = new Databases();
        $filesCollection = new \MySQLExtractor\Common\Collection(array(
            'file_1' => 'contents file 1',
            'file_2' => 'contents file 2'
        ));

        $DatabaseExtractor->from($filesCollection);

        $databasesCollection = \PHPUnit_Framework_Assert::readAttribute($DatabaseExtractor, 'databases');
        $this->assertInstanceOf('\\MySQLExtractor\\Common\\Collection', $databasesCollection);
        $this->assertEquals(0, $databasesCollection->count());
    }

    /**
     * when calling the method then init the tableExtractor using an empty tables processor
     */
    public function testWhenCallingTheMethodThenInitTheTableExtractorUsingAnEmptyTablesProcessor()
    {
        $DatabaseExtractor = new Databases();
        $filesCollection = new \MySQLExtractor\Common\Collection(array(
            'file_1' => 'contents file 1',
            'file_2' => 'contents file 2'
        ));

        $DatabaseExtractor->from($filesCollection);

        $tablesExtractor = \PHPUnit_Framework_Assert::readAttribute($DatabaseExtractor, 'tablesExtractor');
        $this->assertInstanceOf('\\MySQLExtractor\\Extractor\\Tables', $tablesExtractor);
    }

    /**
     * when calling the method then return self
     */
	public function testWhenCallingTheMethodThenReturnSelf()
    {
        $DatabaseExtractor = new Databases();
        $filesCollection = new \MySQLExtractor\Common\Collection(array(
            'file_1' => 'contents file 1',
            'file_2' => 'contents file 2'
        ));

        $response = $DatabaseExtractor->from($filesCollection);

        $this->assertSame($DatabaseExtractor, $response);
    }
}
