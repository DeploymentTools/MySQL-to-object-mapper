<?php
namespace tests\Extractor\Fields;

use MySQLExtractor\Extractor\String;

class getFirstChunkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * When first chunk is an integer then return integer value
     */
    public function testWhenFirstChunkIsAnIntegerThenReturnIntegerValue()
    {
        $response = String::getFirstChunk('1234 not \'here\' 9');
        static::assertEquals(1234, $response);
    }

    /**
     * When first chunk is a decimal number then return value
     */
    public function testWhenFirstChunkIsADecimalNumberThenReturnValue()
    {
        $response = String::getFirstChunk('123.4 not \'here\' 9');
        static::assertEquals(123.4, $response);
    }

    /**
     * When first chunk is a negative integer then return value
     */
    public function testWhenFirstChunkIsANegativeIntegerThenReturnValue()
    {
        $response = String::getFirstChunk('-1234 not \'here\' 9');
        static::assertEquals(-1234, $response);
    }

    /**
     * When first chunk is a negative decimal number then return value
     */
    public function testWhenFirstChunkIsANegativeDecimalNumberThenReturnValue()
    {
        $response = String::getFirstChunk('-123.4 not \'here\' 9');
        static::assertEquals(-123.4, $response);
    }

    /**
     * When first chunk is a string then return value
     */
    public function testWhenFirstChunkIsAStringThenReturnValue()
    {
        $response = String::getFirstChunk('\'aaaa \' not \'here\' 9');
        static::assertEquals('aaaa ', $response);
    }

    /**
     * When first chunk is a string containing an escaped quote then return value
     */
    public function testWhenFirstChunkIsAStringContainingAnEscapedQuoteThenReturnValue()
    {
        $response = String::getFirstChunk('\'aaaa\\\' \' not \'here\' 9');
        static::assertEquals('aaaa\\\' ', $response);
    }

    /**
     * When first chunk is a string wrapped in double quotes then return value
     */
    public function testWhenFirstChunkIsAStringWrappedInDoubleQuotesThenReturnValue()
    {
        $response = String::getFirstChunk('"aaaa " not "here" 9');
        static::assertEquals('aaaa ', $response);
    }

    /**
     * When first chunk is a string wrapped in double quotes and containing an escaped quote then return value
     */
    public function testWhenFirstChunkIsAStringWrappedInDoubleQuotesAndContainingAnEscapedQuoteThenReturnValue()
    {
        $response = String::getFirstChunk('"aa\\\"aa\\\' " not "here" 9');
        static::assertEquals('aa\\\"aa\\\' ', $response);
    }

    /**
     * When first chunk is a NULL string then return null value.
     */
    public function testWhenFirstChunkIsANULLStringThenReturnNullValue()
    {
        $response = String::getFirstChunk('NULL or something "fun"');
        static::assertNull($response);
    }
}
