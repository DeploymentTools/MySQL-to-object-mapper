<?php
namespace tests\Extractor\Fields;

class inQuoteTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        $target = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();
        $this->helper = new \PHPUnitProtectedHelper($target);
    }

    /**
     * when quote or double quote flag will be true then return true, else will return false
     * @dataProvider quoteFlags
     */
	public function testWhenQuoteAndDoubleQuoteFlagWillBeTrueThenReturnTrueElseWillReturnFalse($inSingleQuote, $inDoubleQuote, $expected)
    {
        $this->helper->setValue('inSingleQuote', $inSingleQuote);
        $this->helper->setValue('inDoubleQuote', $inDoubleQuote);

        $return = $this->helper->makeCall('inQuote');
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

