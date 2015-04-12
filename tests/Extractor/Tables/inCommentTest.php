<?php
namespace tests\Extractor\Tables;

class inCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when in comment or in multiline comment flags will be true then return true, else will return false
     * @dataProvider commentFlags
     */
	public function testWhenInCommentOrInMultilineCommentFlagsWillBeTrueThenReturnTrueElseWillReturnFalse($inMultiLineComment, $inLineComment, $expected)
    {
        $tableExtractor = \Mockery::mock('\\MySQLExtractor\\Extractor\\Tables')->makePartial();

        $refObject = new \ReflectionObject($tableExtractor);

        $refProperty = $refObject->getProperty('inMultiLineComment');
        $refProperty->setAccessible(true);
        $refProperty->setValue($tableExtractor, $inMultiLineComment);

        $refProperty = $refObject->getProperty('inLineComment');
        $refProperty->setAccessible(true);
        $refProperty->setValue($tableExtractor, $inLineComment);

        $class = new \ReflectionClass(get_class($tableExtractor));
        $method = $class->getMethod('inComment');
        $method->setAccessible(true);
        $return = $method->invokeArgs($tableExtractor, array());

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

