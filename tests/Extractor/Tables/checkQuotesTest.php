<?php
namespace tests\Extractor\Tables;

class checkQuotesTest extends \PHPUnit_Framework_TestCase
{
    protected $tableExtractor;
    protected $refObject;

    public function setUp()
    {
        $this->tableExtractor = new \MySQLExtractor\Extractor\Tables();
        $this->refObject = new \ReflectionObject($this->tableExtractor);
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

        $this->setValue('inSingleQuote', $inSingleQuote);
        $this->setValue('inDoubleQuote', $inDoubleQuote);
        $this->setValue('previousChar', ',');
        $this->setValue('currentChar', $char);

        $this->makeCall();
        $this->assertSame($expectedSingleQuote, $this->getValue('inSingleQuote'));
        $this->assertSame($expectedDoubleQuote, $this->getValue('inDoubleQuote'));
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

        $this->setValue('inSingleQuote', $inSingleQuote);
        $this->setValue('inDoubleQuote', $inDoubleQuote);
        $this->setValue('previousChar', '\\');
        $this->setValue('currentChar', $char);

        $this->makeCall();
        $this->assertSame($inSingleQuote, $this->getValue('inSingleQuote'));
        $this->assertSame($inDoubleQuote, $this->getValue('inDoubleQuote'));
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

    protected function makeCall()
    {
        $class = new \ReflectionClass(get_class($this->tableExtractor));
        $method = $class->getMethod('checkQuotes');
        $method->setAccessible(true);
        return $method->invokeArgs($this->tableExtractor, array());
    }

    protected function getValue($attribute)
    {
        return \PHPUnit_Framework_Assert::readAttribute($this->tableExtractor, $attribute);
    }

    protected function setValue($attribute, $value)
    {
        $refProperty = $this->refObject->getProperty($attribute);
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->tableExtractor, $value);
    }
}

