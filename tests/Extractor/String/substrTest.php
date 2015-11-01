<?php
namespace tests\Extractor\Fields;

use MySQLExtractor\Extractor\String;

class substrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * If pattern matched multiple times then return the remaining string
     */
    public function testIfPatternMatchedMultipleTimesThenReturnTheRemainingString()
    {
        $worker = new String('test DEFAULT 123 DEFAULT ab');

        $response = $worker->substr('/DEFAULT\s/');
        static::assertEquals('123 DEFAULT ab', $response);
    }

    /**
     * If pattern matched after a single or double quote pair then return the remaining string
     */
    public function testIfPatternMatchedAfterASingleOrDoubleQuotePairThenReturnTheRemainingString()
    {
        $worker = new String('ignore `ticks` \'single\' "double" test DEFAULT 123');

        $response = $worker->substr('/DEFAULT\s/');
        static::assertEquals('123', $response);
    }

    /**
     * If pattern matched only inside single quotes then ignore and return null.
     * @dataProvider dataProviderStringWithQuotes
     */
    public function testIfPatternMatchedOnlyInsideSingleQuotesThenIgnoreAndReturnNull($input)
    {
        $worker = new String($input);

        $response = $worker->substr('/DEFAULT\s/');
        static::assertNull($response);
    }

    /**
     * If pattern matched only inside single quotes and flag is set as true then return remaining.
     * @dataProvider dataProviderStringWithQuotes
     */
    public function testIfPatternMatchedOnlyInsideSingleQuotesAndFlagIsSetAsTrueThenReturnRemaining($input, $expected)
    {
        $worker = new String($input);
        static::assertEquals($expected, $worker->substr('/DEFAULT\s/', true));
    }

    public function dataProviderStringWithQuotes()
    {
        return [
            ['test "DEFAULT " 123333', '" 123333'],
            ["test 'DEFAULT ' 1235551111", '\' 1235551111'],
            ['test \'DEFAULT \' 12', '\' 12'],
        ];
    }
}
