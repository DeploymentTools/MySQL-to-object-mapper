<?php
namespace tests\Extractor\Fields;

use MySQLExtractor\Presentation\Field;

class extractFieldDefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * When field type is enum with string values and default is set then get the default value.
     * @dataProvider dataProviderDefaultValues
     */
    public function testWhenFieldTypeIsEnumWithStringValuesAndDefaultIsStringThenGetTheDefaultValue($defaultSQLString, $expected)
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "`ShiftType` enum('full-time','part-time') NOT NULL " . $defaultSQLString . " COMMENT 'Specifies the working hours type (full-time, part-time).',";

        $field = $fieldExtractor::getFieldFromString($fieldStringLine);
        $this->assertEquals($expected, $field->Default);
    }

    /**
     * When field type is int and default is set then get the default value.
     * @dataProvider dataProviderDefaultValues
     */
    public function testWhenFieldTypeIsIntAndDefaultIsSetThenGetTheDefaultValue($defaultSQLString, $expected)
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();
        $fieldStringLine = "`profil_facultate` int(1) " . $defaultSQLString . ",";

        $field = new Field();
        $fieldExtractor::extractFieldDefault($fieldStringLine, $field);

        $this->assertEquals($expected, $field->Default);
    }

    public function dataProviderDefaultValues()
    {
        return [
            ["DEFAULT 'part-time000'", 'part-time000'],
            ['DEFAULT "part-time000"', 'part-time000'],
            ['DEFAULT "0"', '0'],
            ['DEFAULT \'0\'', '0'],
        ];
    }
}
