<?php
namespace tests\Common\Collection;

class containsKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * containsKey will return true if an element is set for the input key
     */
	public function testContainsKeyWillReturnTrueIfAnElementIsSetForTheInputKey()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertTrue($Collection->containsKey('index2'));
    }

    /**
     * containsKey will return false if an element is not set for the input key
     */
	public function testContainsKeyWillReturnFalseIfAnElementIsNotSetForTheInputKey()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertFalse($Collection->containsKey('index3'));
    }
}
