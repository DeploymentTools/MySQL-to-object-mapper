<?php
namespace tests\extractor\Table;

class fieldExtractor extends \PHPUnit_Framework_TestCase
{
    public function testReturn()
    {
        $tableExtractor = \Mockery::mock('\\MySQLExtractor\\extractor\\Table')->makePartial();

        $tableObject = new \stdClass();
        $tableObject->Keys = array();
        $tableObject->Fields = array();

        $fieldStringLine = "`ShiftType` enum('full-time','part-time') NOT NULL DEFAULT 'part-time' COMMENT 'Specifies the working hours type (full-time, part-time).',";

        $field = $tableExtractor::extractFieldFromString($fieldStringLine);
        $this->assertEquals('ShiftType', $field->Id);
    }
}

