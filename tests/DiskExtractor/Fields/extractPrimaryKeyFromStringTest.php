<?php
namespace tests\DiskExtractor\Table;

class extractPrimaryKeyFromString extends \PHPUnit_Framework_TestCase
{
    public function testWhenPrimaryKeyIsSetInStringThenExtractColumnValueAndReturnPrimaryKeyObject()
    {
        $tableExtractor = \Mockery::mock('\\MySQLExtractor\\DiskExtractor\\Fields')->makePartial();

        $fieldStringLine = "PRIMARY KEY (`AuthorID`)";
        $return = $tableExtractor::extractPrimaryKeyFromString($fieldStringLine);

        $expectedObject = new \MySQLExtractor\Presentation\PrimaryKey();
        $expectedObject->Column = 'AuthorID';

        $this->assertEquals($expectedObject, $return);
    }

    public function testWhenPrimaryKeyIsNotSetThenReturnNull()
    {
        $tableExtractor = \Mockery::mock('\\MySQLExtractor\\DiskExtractor\\Fields')->makePartial();

        $fieldStringLine = "KEY (`AuthorID`)";
        $return = $tableExtractor::extractPrimaryKeyFromString($fieldStringLine);

        $this->assertNull($return);
    }
}
