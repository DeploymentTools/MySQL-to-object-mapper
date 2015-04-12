<?php
namespace tests\Driver\Disk;
use \MySQLExtractor\Common\System;
use \MySQLExtractor\Driver\Disk;
use \MySQLExtractor\Exceptions\InvalidSourceException;

/**
 * @runInSeparateProcess
 */
class executeTest extends \PHPUnit_Framework_TestCase
{
    protected $path;

    public function setUp()
    {
        $this->path = '/projects/MySQL-to-object-mapper/demo/output';
    }

    /**
     * when input is empty directory then throw exception
     */
    public function testWhenInputIsEmptyDirectoryThenThrowException()
    {
        $systemMock = $this->setSystemMock(true, true);
        $systemMock->shouldReceive('getDirectoryIterator')->with($this->path)->andReturn(array());

        try {
            $DiskExtractor = new Disk($this->path);
            $DiskExtractor->execute();

        } catch (InvalidSourceException $e) {
            $this->assertEquals('There were no input files found.', $e->getMessage());
        }
    }

    /**
     * when input is not directory then add source files
     */
    public function testWhenInputIsNotDirectoryThenAddSourceFiles()
    {
        $DirectoryIterator = array();
        $Item = \Mockery::mock('FileIteratorItem')->makePartial();
        $Item->shouldReceive('isDot')->andReturn(false);
        $Item->shouldReceive('isDir')->andReturn(false);
        $Item->shouldReceive('getFilename')->andReturn('file_1.sql');
        $DirectoryIterator[] = $Item;

        $systemMock = $this->setSystemMock(true, true);
        $systemMock->shouldReceive('getDirectoryIterator')->with($this->path)->andReturn($DirectoryIterator);
        $systemMock->shouldReceive('file_get_contents')->with($this->path . '/file_1.sql')->andReturn("...");

        $DiskExtractor = new Disk($this->path);
        $DiskExtractor->execute();

        $entries = \PHPUnit_Framework_Assert::readAttribute($DiskExtractor, 'entries');
        $this->assertEquals(1, $entries->count());
        $this->assertEquals(array($this->path . '/file_1.sql'), array_keys($entries->toArray()));
    }

    public function setSystemMock($file_exists, $is_dir)
    {
        $systemHelper = new System();
        $systemMock = \Mockery::mock('\\MySQLExtractor\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with($this->path)->andReturn($file_exists);
        $systemMock->shouldReceive('is_dir')->with($this->path)->andReturn($is_dir);

        $refObject = new \ReflectionObject($systemHelper);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($systemHelper, $systemMock);

        return $systemMock;
    }
}
