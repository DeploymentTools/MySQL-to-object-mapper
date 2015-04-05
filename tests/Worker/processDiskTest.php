<?php
namespace tests\Worker;

class processDiskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when the input is a file then process contents
     */
    public function testWhenTheInputIsAFileThenProcessContents()
    {
        $inputPath = '/valid/input_db_1.sql';
        $contents = "
        CREATE TABLE `companii` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nume` varchar(255) DEFAULT NULL,
          `url` varchar(100) DEFAULT NULL,
          `domeniu` varchar(100) DEFAULT NULL,
          `email` varchar(255) DEFAULT NULL,
          `telefon` varchar(100) DEFAULT NULL,
          `judet` varchar(100) DEFAULT NULL,
          `localitate` varchar(100) DEFAULT NULL,
          `strada` varchar(255) DEFAULT NULL,
          `nr` varchar(20) DEFAULT NULL,
          `reprezentant` varchar(255) DEFAULT NULL,
          `username` varchar(255) DEFAULT NULL,
          `parola` varchar(60) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
        ";

        $systemMock = \Mockery::mock('\\MySQLExtractor\\Common\\SystemMock')->makePartial();
        $systemMock->shouldReceive('file_exists')->with($inputPath)->andReturn(true);
        $systemMock->shouldReceive('is_dir')->with($inputPath)->andReturn(false);
        $systemMock->shouldReceive('file_get_contents')->with($inputPath)->andReturn($contents);

        $system = new \MySQLExtractor\Common\System();

        $refObject = new \ReflectionObject($system);
        $refProperty = $refObject->getProperty('mock');
        $refProperty->setAccessible(true);
        $refProperty->setValue($system, $systemMock);

        $worker = new \MySQLExtractor\Worker();
        $worker->processDisk($inputPath);

        $extractor = \PHPUnit_Framework_Assert::readAttribute($worker, 'extractor');
        $databases = $extractor->databases();

        $this->assertEquals(1, count($databases));
        $this->assertEquals('input_db_1', $databases['input_db_1']->Name);
        $this->assertEquals(1, count($databases['input_db_1']->Tables));
    }
}