<?php
namespace tests\Common\Collection;

class addTest extends \PHPUnit_Framework_TestCase
{
    /**
     * adding elements will append to the elements list
     */
    public function testWhenNoInputIsSentToTheConstructorThenInitializeUsingAnEmptyArray()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals(array(), $elements);

        $Collection->add($element1);

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals(array($element1), $elements);

        $Collection->add($element2);
        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals(array($element1, $element2), $elements);
    }

    /**
     * adding an element will return true
     */
    public function testAddingAnElementWillReturnTrue()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $response = $Collection->add($element1);
        $this->assertTrue($response);

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals(array($element1), $elements);
    }
}
