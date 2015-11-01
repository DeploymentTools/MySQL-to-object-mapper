<?php
namespace tests\Extractor\Fields;

use MySQLExtractor\Extractor\String;

class isRegexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * If is not regex then return false.
     * @dataProvider dataProviderNotRealRegex
     */
    public function testIfIsNotRegexThenReturnFalse($input)
    {
        /** force that no padding is added, to test if the string is a real regex */
        static::assertFalse(String::isRegex($input, ''));
    }

    /**
     * If is regex then return true.
     * @dataProvider dataProviderRealRegex
     */
    public function testIfIsRegexThenReturnTrue($input)
    {
        static::assertTrue(String::isRegex($input));
    }

    /**
     * If is a partial regex then return true.
     * @dataProvider dataProviderPartialRegex
     */
    public function testIfIsAPartialRegexThenReturnTrue($input)
    {
        static::assertTrue(String::isRegex($input));
    }

    /**
     * If input is an empty string then return false.
     */
    public function testIfInputIsAnEmptyStringThenReturnFalse()
    {
        static::assertFalse(String::isRegex(''));
    }

    public function dataProviderNotRealRegex()
    {
        return [
            [123],
            ['123'],
            ['abc'],
            ['abc\'']
        ];
    }

    public function dataProviderRealRegex()
    {
        return [
            ['/^\/[\s\S]+\/$/'],
            ['/^[A-Za-z ]+$/'],
        ];
    }

    public function dataProviderPartialRegex()
    {
        return [
            ['[0-9]+'],
        ];
    }
}
