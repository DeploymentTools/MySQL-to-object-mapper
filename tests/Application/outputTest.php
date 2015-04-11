<?php
namespace tests\Application;

use MySQLExtractor\Common\System;
use MySQLExtractor\Exceptions\InvalidPathException;
//use MySQLExtractor\Presentation\Database;
use MySQLExtractor\Presentation\Database;
use MySQLExtractor\Application;

class outputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when output folder does not exist then throw InvalidPathException
     */
    public function testWhenOutputFolderDoesNotExistThenThrowInvalidPathException()
    {
        $systemMock = \Mockery::mock('\\MySQLExtractor\\Common\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with('/invalid-folder/')->andReturn(false);
        $systemMock->shouldReceive('is_dir')->with('/invalid-folder/')->andReturn(false);

        $system = new System();

        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, $systemMock);

        $worker = new Application();

        try {
            $worker->output('/invalid-folder/');
            $this->fail();

        } catch (InvalidPathException $e) {
            $this->assertEquals('Path /invalid-folder/ is invalid.', $e->getMessage());
        }
    }

    /**
     * when output folder exists but is not a folder then throw InvalidPathException
     */
    public function testWhenOutputFolderExistsButIsNotAFolderThenThrowInvalidPathException()
    {
        $systemMock = \Mockery::mock('\\MySQLExtractor\\Common\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with('/file-output')->andReturn(true);
        $systemMock->shouldReceive('is_dir')->with('/file-output')->andReturn(false);

        $system = new System();

        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, $systemMock);

        $worker = new Application();

        try {
            $worker->output('/file-output');
            $this->fail();

        } catch (InvalidPathException $e) {
            $this->assertEquals('Path /file-output is invalid.', $e->getMessage());
        }
    }

    /**
     * when output exists and is a folder then write content for all databases
     */
    public function testWhenOutputExistsAndIsAFolderThenWriteContentForAllDatabases()
    {
        $systemMock = \Mockery::mock('\\MySQLExtractor\\Common\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with('/valid-output')->andReturn(true);
        $systemMock->shouldReceive('is_dir')->with('/valid-output')->andReturn(true);

        $system = new System();

        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, $systemMock);

        $worker = new Application();

        $databases = array(
            new Database('db1'),
            new Database('db2')
        );

        $filename1 = '/valid-output' . DIRECTORY_SEPARATOR . 'db1.json';
        $filename2 = '/valid-output' . DIRECTORY_SEPARATOR . 'db2.json';

        $content1 = json_encode($databases[0], JSON_PRETTY_PRINT);
        $content2 = json_encode($databases[1], JSON_PRETTY_PRINT);

        $extractorMock = \Mockery::mock('\\MySQLExtractor\\Extractor\\Main')->makePartial();
        $extractorMock->shouldReceive('databases')->andReturn($databases);
        $systemMock->shouldReceive('file_put_contents')->once()->with($filename1, $content1);
        $systemMock->shouldReceive('file_put_contents')->once()->with($filename2, $content2);

        $refObject = new \ReflectionObject($worker);
        $refProperty = $refObject->getProperty('extractor');
        $refProperty->setAccessible(true);
        $refProperty->setValue($worker, $extractorMock);

        $response = $worker->output('/valid-output');
        $this->assertTrue($response); // asserts are made by mockery's shouldReceive
    }
}
