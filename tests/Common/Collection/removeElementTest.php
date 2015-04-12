<?php
namespace tests\Common\Collection;

class removeElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * remove will return null if the element is not in array
     */
    public function testRemoveWillReturnNullIfTheElementIsNotInArray()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $element3 = new \stdClass();
        $element3->Name = 'EL 3';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertNull($Collection->removeElement($element3));
    }

    /**
     * removeElement will return true if the element is in array
     */
    public function testRemoveElementWillReturnTrueIfTheElementIsInArray()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertTrue($Collection->removeElement($element2));
    }
}
