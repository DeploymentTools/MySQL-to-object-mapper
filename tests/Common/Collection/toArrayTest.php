<?php
namespace tests\Common\Collection;

class toArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * toArray will return the stored elements with the corresponding keys
     */
	public function testToArrayWillReturnTheStoredElementsWithTheCorrespondingKeys()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals(array(), $elements);

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertEquals(array(
            'index2' => $element2,
            'index1' => $element1
        ), $Collection->toArray());
    }
    /**
     * toArray will return an empty array if no elements are set
     */
	public function testToArrayWillReturnAnEmptyArrayIfNoElementsAreSet()
    {
        $Collection = new \MySQLExtractor\Common\Collection();
        $this->assertEquals(array(), $Collection->toArray());
    }
}
