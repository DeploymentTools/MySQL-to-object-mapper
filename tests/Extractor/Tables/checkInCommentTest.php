<?php
namespace tests\Extractor\Tables;

class checkInCommentTest extends \PHPUnit_Framework_TestCase
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
    public function testWhenInCommentOrInMultilineCommentFlagsWillBeTrueThenReturnTrueElseWillReturnFalse(
        $inMultiLineComment,
        $inLineComment,
        $previousChar,
        $currentChar,
        $expectedMultiLineComment,
        $expectedLineComment
    ) {
        $this->helper->setValue('inMultiLineComment', $inMultiLineComment);
        $this->helper->setValue('inLineComment', $inLineComment);

        $this->helper->setValue('currentChar', $currentChar);
        $this->helper->setValue('previousChar', $previousChar);

        $this->helper->makeCall('checkInComment');
        $this->assertSame($expectedMultiLineComment, $this->helper->getValue('inMultiLineComment'));
        $this->assertSame($expectedLineComment, $this->helper->getValue('inLineComment'));
    }

    public function commentFlags()
    {
        return array(
            array(false, false, '-', '-', false, true),
            array(false, false, '/', '*', true, false),
            array(true, false, '*', '/', false, false)
        );
    }
}
