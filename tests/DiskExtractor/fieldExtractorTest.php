<?php
namespace tests\DiskExtractor;

class fieldExtractor extends \PHPUnit_Framework_TestCase
{
    public function testReturn()
    {
        $DiskExtractor = \Mockery::mock('\\MySQLExtractor\\DiskExtractor')->makePartial();

        $tableObject = new \stdClass();
        $tableObject->Keys = array();
        $tableObject->Fields = array();

        $fieldStringLine = "`ShiftType` enum('full-time','part-time') NOT NULL DEFAULT 'part-time' COMMENT 'Specifies the working hours type (full-time, part-time).',";

        $class = new \ReflectionClass(get_class($DiskExtractor));
        $method = $class->getMethod('fieldExtractor');
        $method->setAccessible(true);
        $return = $method->invokeArgs($DiskExtractor, array($fieldStringLine, $tableObject));

        $this->assertTrue($return);
        $this->assertEquals(1, count($tableObject->Fields));

        $field = $tableObject->Fields[0];
        $this->assertEquals('ShiftType', $field->Id);
    }
}

