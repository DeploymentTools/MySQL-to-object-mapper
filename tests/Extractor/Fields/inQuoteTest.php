<?php
namespace tests\Extractor\Fields;

class inQuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when quote or double quote flag will be true then return true, else will return false
     * @dataProvider quoteFlags
     */
	public function testWhenQuoteAndDoubleQuoteFlagWillBeTrueThenReturnTrueElseWillReturnFalse($inSingleQuote, $inDoubleQuote, $expected)
    {
        $fieldExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();

        $refObject = new \ReflectionObject($fieldExtractor);

        $refProperty = $refObject->getProperty('inSingleQuote');
        $refProperty->setAccessible(true);
        $refProperty->setValue($fieldExtractor, $inSingleQuote);

        $refProperty = $refObject->getProperty('inDoubleQuote');
        $refProperty->setAccessible(true);
        $refProperty->setValue($fieldExtractor, $inDoubleQuote);

        $class = new \ReflectionClass(get_class($fieldExtractor));
        $method = $class->getMethod('inQuote');
        $method->setAccessible(true);
        $return = $method->invokeArgs($fieldExtractor, array());

        $this->assertSame($expected, $return);
    }

    public function quoteFlags()
    {
        return array(
            array(true, true, true),
            array(true, false, true),
            array(false, true, true),
            array(false, false, false)
        );
    }
}

