<?php
namespace tests\DiskExtractor;

class extractPrimaryKeyFromString extends \PHPUnit_Framework_TestCase
{
    public function testWhenPrimaryKeyIsSetInStringThenExtractColumnValueAndReturnPrimaryKeyObject()
    {
        $DiskExtractor = \Mockery::mock('\\MySQLExtractor\\DiskExtractor')->makePartial();

        $fieldStringLine = "PRIMARY KEY (`AuthorID`)";
        $return = $DiskExtractor::extractPrimaryKeyFromString($fieldStringLine);

        $expectedObject = new \MySQLExtractor\assets\db\PrimaryKey();
        $expectedObject->Column = 'AuthorID';

        $this->assertEquals($expectedObject, $return);
    }

    public function testWhenPrimaryKeyIsNotSetThenReturnNull()
    {
        $DiskExtractor = \Mockery::mock('\\MySQLExtractor\\DiskExtractor')->makePartial();

        $fieldStringLine = "KEY (`AuthorID`)";
        $return = $DiskExtractor::extractPrimaryKeyFromString($fieldStringLine);

        $this->assertNull($return);
    }
}
