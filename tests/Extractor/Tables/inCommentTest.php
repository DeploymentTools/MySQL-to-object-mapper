<?php
namespace tests\Extractor\Tables;

class inCommentTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        $target = \Mockery::mock('\\MySQLExtractor\\Extractor\\Tables')->makePartial();
        $this->helper = new \PHPUnitProtectedHelper($target);
    }

    /**
     * when in comment or in multiline comment flags will be true then return true, else will return false
     * @dataProvider commentFlags
     */
	public function testWhenInCommentOrInMultilineCommentFlagsWillBeTrueThenReturnTrueElseWillReturnFalse($inMultiLineComment, $inLineComment, $expected)
    {
        $this->helper->setValue('inMultiLineComment', $inMultiLineComment);
        $this->helper->setValue('inLineComment', $inLineComment);

        $return = $this->helper->makeCall('inComment');
        $this->assertSame($expected, $return);
    }

    public function commentFlags()
    {
        return array(
            array(true, true, true),
            array(true, false, true),
            array(false, true, true),
            array(false, false, false)
        );
    }
}
