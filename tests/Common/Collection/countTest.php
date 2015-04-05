<?php
namespace tests\Common\Collection;

class countTest extends \PHPUnit_Framework_TestCase
{
    /**
     * count will return zero if no elements have been added
     */
    public function testCountWillReturnZeroIfNoElementsHaveBeenAdded()
    {
        $Collection = new \MySQLExtractor\Common\Collection();
        $this->assertEquals(0, $Collection->count());
    }

    /**
     * count will return the number of elements that have been added
     */
    public function testCountWillReturnTheNumberOfElementsThatHaveBeenAdded()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertEquals(2, $Collection->count());
    }
}
