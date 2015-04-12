<?php
namespace tests\Driver\Disk;
use \MySQLExtractor\Common\System;
use \MySQLExtractor\Driver\Disk;
use \MySQLExtractor\Exceptions\InvalidPathException;

/**
 * @runInSeparateProcess
 */
class __constructor extends \PHPUnit_Framework_TestCase
{
    /**
     * When the path sent to the constructor exists then set the source as dir if path is a directory
     */
    public function testWhenThePathSentToTheConstructorExistsThenSetTheSourceAsDirIfPathIsDirectory()
    {
        $path = '/projects/MySQL-to-object-mapper/demo/output/';

        $systemHelper = new System();
        $systemMock = \Mockery::mock('\\MySQLExtractor\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with($path)->andReturn(true);
        $systemMock->shouldReceive('is_dir')->with($path)->andReturn(true);

        $refObject = new \ReflectionObject($systemHelper);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($systemHelper, $systemMock);

        $DiskExtractor = new Disk($path);
        $sourcePath = \PHPUnit_Framework_Assert::readAttribute($DiskExtractor, 'source');
        $this->assertEquals($path, $sourcePath);
    }

    /**
     * When the path sent to the constructor doesn't exist then throw InvalidPathException
     */
    public function testWhenThePathSentToTheConstructorDoesntExistThenThrowInvalidPathException()
    {
        $path = '/projects/MySQL-to-object-mapper/demo/output/';

        $systemHelper = new System();
        $systemMock = \Mockery::mock('\\MySQLExtractor\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with($path)->andReturn(false);

        $refObject = new \ReflectionObject($systemHelper);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($systemHelper, $systemMock);

        try {
            new Disk($path);
            $this->fail();

        } catch (InvalidPathException $e) {
            $this->assertEquals("Path " . $path . " is invalid.", $e->getMessage());
            $this->assertEquals(1001, $e->getCode());
        }
    }
}
