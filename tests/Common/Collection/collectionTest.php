<?php
namespace tests\Common\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when no input is sent to the constructor then initialize using an empty array
     */
    public function testWhenNoInputIsSentToTheConstructorThenInitializeUsingAnEmptyArray()
    {
        $Collection = new \MySQLExtractor\Common\Collection();
        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals(array(), $elements);
    }

    /**
     * when input is sent to the constructor then initialize using the input
     */
    public function testWhenInputIsSentToTheConstructorThenInitializeUsingTheInput()
    {
        $input = array(
            'a' => 'b',
            'c' => 'd'
        );

        $Collection = new \MySQLExtractor\Common\Collection($input);
        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals($input, $elements);
    }
}