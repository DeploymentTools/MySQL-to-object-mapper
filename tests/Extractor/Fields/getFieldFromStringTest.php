<?php
namespace tests\Extractor\Fields;

class getFieldFromStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when field is ENUM then catch ENUM type
     */
    public function testWhenFieldIsEnumThenCatchEnumType()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "`ShiftType` enum('full-time','part-time') NOT NULL DEFAULT 'part-time' COMMENT 'Specifies the working hours type (full-time, part-time).',";

        $field = $fieldExtractor::getFieldFromString($fieldStringLine);
        $this->assertEquals('ShiftType', $field->Id);
        $this->assertEquals('ENUM', $field->Type);
    }

    /**
     * When field default in string zero then Default will be int or string zero.
     */
    public function testWhenFieldDefaultInStringZeroThenDefaultWillBeIntOrStringZero()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "`comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',";
        $field = $fieldExtractor::getFieldFromString($fieldStringLine);

        $this->assertEquals('0', $field->Default);
    }

    /**
     * when field is ENUM then catch ENUM values as field values
     */
    public function testWhenFieldIsEnumThenCatchEnumValuesAsFieldValues()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $expected = array(
            'full-time',
            'part-time'
        );

        $fieldStringLine = "`ShiftType` enum('full-time', 'part-time') NOT NULL DEFAULT 'part-time' COMMENT 'Specifies the working hours type (full-time, part-time).',";

        $field = $fieldExtractor::getFieldFromString($fieldStringLine);
        $this->assertEquals($expected, $field->Values);
    }

    /**
     * when field is not found then return false
     */
    public function testWhenFieldIsNotFoundThenReturnFalse()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "PRIMARY KEY (`AuthorID`)";

        $response = $fieldExtractor::getFieldFromString($fieldStringLine);
        $this->assertFalse($response);
    }
}
