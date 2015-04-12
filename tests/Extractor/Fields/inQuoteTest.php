<?php
namespace tests\Extractor\Fields;

class inQuoteTest extends \PHPUnit_Framework_TestCase
{
    protected $testingHelper;

    /**
     * when quote or double quote flag will be true then return true, else will return false
     * @dataProvider providerQuoteFlags
     */
	public function testWhenQuoteAndDoubleQuoteFlagWillBeTrueThenReturnTrueElseWillReturnFalse($inSingleQuote, $inDoubleQuote, $expected)
    {
        $targetClass = \Mockery::mock('\\MySQLExtractor\\Extractor\\Fields')->makePartial();
        $this->testingHelper = new \PHPUnitProtectedHelper($targetClass);

        $this->testingHelper->setValue('inSingleQuote', $inSingleQuote);
        $this->testingHelper->setValue('inDoubleQuote', $inDoubleQuote);

        $return = $this->testingHelper->makeCall('inQuote');
        $this->assertSame($expected, $return);
    }

    public function providerQuoteFlags()
    {
        return array(
            array(true, true, true, 'both quotes are on'),
            array(true, false, true, 'at least one quote is on'),
            array(false, true, true, 'at least one quote is on'),
            array(false, false, false, 'both quotes are off')
        );
    }
}

