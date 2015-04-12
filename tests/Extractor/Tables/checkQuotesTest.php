<?php
namespace tests\Extractor\Tables;

class checkQuotesTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        $this->helper = new \PHPUnitProtectedHelper(new \MySQLExtractor\Extractor\Tables());
    }

    /**
     * when current char is quote and is not escaped then invert flag for quote
     * @dataProvider sampleQuotes
     */
    public function testWhenCurrentCharIsQuoteAndIsNotEscapedThenInvertFlagForQuote(
        $inSingleQuote,
        $inDoubleQuote,
        $char,
        $expectedSingleQuote,
        $expectedDoubleQuote
    ) {
        $this->helper->setValue('inSingleQuote', $inSingleQuote);
        $this->helper->setValue('inDoubleQuote', $inDoubleQuote);
        $this->helper->setValue('previousChar', ',');
        $this->helper->setValue('currentChar', $char);

        $this->helper->makeCall('checkQuotes');
        $this->assertSame($expectedSingleQuote, $this->helper->getValue('inSingleQuote'));
        $this->assertSame($expectedDoubleQuote, $this->helper->getValue('inDoubleQuote'));
    }

    /**
     * when current char is quote and is escaped then dont invert flag for quote
     * @dataProvider sampleQuotes
     */
    public function testWhenCurrentCharIsQuoteAndIsEscapedThenDontInvertFlagForQuote(
        $inSingleQuote,
        $inDoubleQuote,
        $char,
        $ignoreExpectedSingleQuote,
        $ignoreExpectedDoubleQuote
    ) {

        $this->helper->setValue('inSingleQuote', $inSingleQuote);
        $this->helper->setValue('inDoubleQuote', $inDoubleQuote);
        $this->helper->setValue('previousChar', '\\');
        $this->helper->setValue('currentChar', $char);

        $this->helper->makeCall('checkQuotes');
        $this->assertSame($inSingleQuote, $this->helper->getValue('inSingleQuote'));
        $this->assertSame($inDoubleQuote, $this->helper->getValue('inDoubleQuote'));
    }

    public function sampleQuotes()
    {
        return array(
            array(false, false, '"', false, true),
            array(false, false, "'", true, false),
            array(true, true, '"', true, false),
            array(true, true, "'", false, true)
        );
    }
}

