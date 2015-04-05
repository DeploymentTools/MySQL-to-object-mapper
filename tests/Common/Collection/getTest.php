<?php
namespace tests\Common\Collection;

class getTest extends \PHPUnit_Framework_TestCase
{
    /**
     * getting an element will return the value under the given key index
     */
	public function testGettingAnElementWillReturnTheValueUnderTheGivenKeyIndex()
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

        $elementSearch = $Collection->get('index2');
        $this->assertSame($element2, $elementSearch);
    }
    /**
     * getting an element that is not set for the given index key will return null
     */
    public function testGettingAnElementThatIsNotSetForTheGivenIndexKeyWillReturnNull()
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

        $elementSearch = $Collection->get('index3');
        $this->assertNull($elementSearch);
    }
}
