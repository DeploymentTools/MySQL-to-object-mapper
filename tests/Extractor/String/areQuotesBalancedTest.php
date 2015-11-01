<?php
namespace tests\Extractor\Fields;

use MySQLExtractor\Extractor\String;

class areQuotesBalancedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * When the input string contains balanced quotes then return true.
     * @dataProvider dataProviderBalancedQuotesStrings
     */
    public function testWhenTheInputStringContainsBalancedQuotesThenReturnTrue($input)
    {
        static::assertTrue(String::areQuotesBalanced($input));
    }

    public function dataProviderBalancedQuotesStrings()
    {
        return [
            ['`IdKey` VARCHAR(20)'],
            ['`IdKey` VARCHAR(20) DEFAULT "AB"'],
            ['`IdKey` VARCHAR(20) DEFAULT \'test123\''],
            ['`IdKey` VARCHAR(20) \"'],
            ['`IdKey` VARCHAR(20) DEFAULT "AB" \"'],
            ['`IdKey` VARCHAR(20) DEFAULT \'test123\' \"'],
            ['`IdKey` VARCHAR(20) \\\''],
            ['`IdKey` VARCHAR(20) DEFAULT "AB" \\\''],
            ['`IdKey` VARCHAR(20) DEFAULT \'test123\' \\\''],
        ];
    }

    /**
     * When the input string contains unbalanced quotes then return true.
     * @dataProvider dataProviderUnbalancedQuotesStrings
     */
    public function testWhenTheInputStringContainsUnbalancedQuotesThenReturnTrue($input)
    {
        static::assertFalse(String::areQuotesBalanced($input));
    }

    public function dataProviderUnbalancedQuotesStrings()
    {
        return [
            ['`IdKey` VARCHAR(20) "'],
            ['`IdKey` VARCHAR(20) DEFAULT "AB" "'],
            ['`IdKey` VARCHAR(20) DEFAULT \'test123\' "'],
            ['`IdKey` VARCHAR(20) \''],
            ['`IdKey` VARCHAR(20) DEFAULT "AB" \''],
            ['`IdKey` VARCHAR(20) DEFAULT \'test123\' \''],
        ];
    }
}
