<?php
namespace tests\Extractor\Fields;

use MySQLExtractor\Extractor\String;

class substrFromTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Will extract string starting from input pattern
     * @dataProvider dataProviderSamples
     */
    public function testWillExtractStringStartingFromInputPattern($input, $pattern, $expected)
    {
        static::assertEquals($expected, String::substringFrom($pattern, $input));
    }

    /**
     * If pattern not matched then return null
     */
    public function testIfPatternNotMatchedThenReturnNull()
    {
        static::assertNull(String::substringFrom('/DEFAULT\s/', 'test DEFAULTz 123 ab'));
    }

    /**
     * If pattern matched multiple times then return the remaining string
     */
    public function testIfPatternMatchedMultipleTimesThenReturnTheRemainingString()
    {
        $response = String::substringFrom('/DEFAULT\s/', 'test DEFAULT 123 DEFAULT ab');
        static::assertEquals('123 DEFAULT ab', $response);
    }

    public function dataProviderSamples()
    {
        return [
            ['test DEFAULT 123', '/DEFAULT\s/', '123'],
            ['test DEFAULT 123 ab', '/DEFAULT\s/', '123 ab'],
            ['test DEFAULT 123 ab', '/[\d]+/', ' ab'],
        ];
    }
}
