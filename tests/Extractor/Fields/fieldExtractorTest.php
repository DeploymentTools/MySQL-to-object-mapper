<?php
namespace tests\Extractor\Fields;

class fieldExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when a key or a field is not found in the string then return false
     */
    public function testWhenAKeyOrAFieldIsNotFoundInTheStringThenReturnFalse()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();
        $fieldStringLine = "CREATE TABLE `anunturi` (";

        $class = new \ReflectionClass(get_class($fieldExtractor));
        $method = $class->getMethod('fieldExtractor');
        $method->setAccessible(true);
        $return = $method->invokeArgs($fieldExtractor, array($fieldStringLine));

        $this->assertFalse($return);
    }

    /**
     * when a field is found in the string then return true
     */
    public function testWhenAFieldIsFoundInTheStringThenReturnTrue()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();
        $fieldStringLine = "`ShiftType` enum('full-time','part-time') NOT NULL DEFAULT 'part-time' COMMENT 'Specifies the working hours type (full-time, part-time).',";

        $class = new \ReflectionClass(get_class($fieldExtractor));
        $method = $class->getMethod('fieldExtractor');
        $method->setAccessible(true);
        $return = $method->invokeArgs($fieldExtractor, array($fieldStringLine));

        $this->assertTrue($return);
    }

    /**
     * when a key is found in the string then return key
     */
    public function testWhenAKeyIsFoundInTheStringThenReturnTrue()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();
        $fieldStringLine = "KEY `ProfilEducatie` (`profil_scoala_primara`,`profil_scoala_profesionala`,`profil_liceu`,`profil_facultate`,`profil_postuniversitar`),";

        $class = new \ReflectionClass(get_class($fieldExtractor));
        $method = $class->getMethod('fieldExtractor');
        $method->setAccessible(true);
        $return = $method->invokeArgs($fieldExtractor, array($fieldStringLine));

        $this->assertTrue($return);
    }
}
