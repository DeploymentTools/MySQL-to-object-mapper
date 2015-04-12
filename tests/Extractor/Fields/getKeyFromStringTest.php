<?php
namespace tests\Extractor\Fields;

class getKeyFromStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when field is not a key then return null
     */
    public function testWhenFieldIsNotAKeyThenReturnNull()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "`ShiftType` enum('full-time','part-time') NOT NULL DEFAULT 'part-time' COMMENT 'Specifies the working hours type (full-time, part-time).',";

        $return = $fieldExtractor::getKeyFromString($fieldStringLine);
        $this->assertNull($return);
    }

    /**
     * when primary key if found then return null
     */
    public function testWhenPrimaryKeyIsNotFoundThenReturnNull()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "PRIMARY KEY (`AuthorID`)";

        $return = $fieldExtractor::getKeyFromString($fieldStringLine);
        $this->assertNull($return);
    }

    /**
     * when key if found then return object
     */
    public function testWhenKeyIsFoundThenReturnObject()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "KEY `companie_ID` (`companieID`),";

        $return = $fieldExtractor::getKeyFromString($fieldStringLine);
        $this->assertInstanceOf('\\MySQLExtractor\\Presentation\\Key', $return);

        $this->assertEquals('companie_ID', $return->Label);
        $this->assertEquals(array('companieID'), $return->Columns);
    }

    /**
     * when key with multiple columns if found then return object
     */
    public function testWhenKeyWithMultipleColumnsIsFoundThenReturnObject()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "KEY `ProfilEducatie` (`profil_scoala_primara`,`profil_scoala_profesionala`,`profil_liceu`,`profil_facultate`,`profil_postuniversitar`),";

        $return = $fieldExtractor::getKeyFromString($fieldStringLine);
        $this->assertInstanceOf('\\MySQLExtractor\\Presentation\\Key', $return);

        $expected = array(
            'profil_scoala_primara',
            'profil_scoala_profesionala',
            'profil_liceu',
            'profil_facultate',
            'profil_postuniversitar'
        );

        $this->assertEquals('ProfilEducatie', $return->Label);
        $this->assertEquals($expected, $return->Columns);
    }

    /**
     * when key is found with no columns then return null
     */
    public function testWhenKeyIsFoundWithNoColumnsThenReturnNull()
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $fieldStringLine = "KEY `ProfilEducatie` (),";

        $return = $fieldExtractor::getKeyFromString($fieldStringLine);
        $this->assertNull($return);
    }
}
