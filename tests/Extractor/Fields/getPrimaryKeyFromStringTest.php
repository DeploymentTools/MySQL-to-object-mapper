<?php
namespace tests\Extractor\Fields;

class getPrimaryKeyFromStringTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenPrimaryKeyIsSetInStringThenExtractColumnValueAndReturnPrimaryKeyObject()
    {
        $tableExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "PRIMARY KEY (`AuthorID`)";
        $return = $tableExtractor::getPrimaryKeyFromString($fieldStringLine);

        $expectedObject = new \MySQLExtractor\Presentation\PrimaryKey();
        $expectedObject->Column = 'AuthorID';

        $this->assertEquals($expectedObject, $return);
    }

    public function testWhenPrimaryKeyIsNotSetThenReturnNull()
    {
        $tableExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "KEY (`AuthorID`)";
        $return = $tableExtractor::getPrimaryKeyFromString($fieldStringLine);

        $this->assertNull($return);
    }
}
