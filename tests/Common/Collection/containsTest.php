<?php
namespace tests\Common\Collection;

class containsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * contains will return true if an element is set
     */
    public function testContainsWillReturnTrueIfAnElementIsSet()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertTrue($Collection->contains($element2));
    }

    /**
     * contains will return false if an element is not set
     */
    public function testContainsWillReturnFalseIfAnElementIsNotSet()
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

        $this->assertFalse($Collection->contains($element3));
    }
}
