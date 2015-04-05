<?php
namespace tests\Common\Collection;

class setTest extends \PHPUnit_Framework_TestCase
{
    /**
     * setting an element will store the element under the given key
     */
	public function testSettingAnElementWillStoreTheElementUnderTheGivenKey()
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

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');

        $this->assertEquals(array(
            'index2' => $element2,
            'index1' => $element1
        ), $elements);
    }

    /**
     * setting an element will update the key value if already set
     */
    public function testSettingAnElementWillUpdateTheKeyValueIfAlreadySet()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $element3 = new \stdClass();
        $element3->Name = 'EL 3';

        $Collection = new \MySQLExtractor\Common\Collection();

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');
        $this->assertEquals(array(), $elements);

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');

        $this->assertEquals(array(
            'index2' => $element2,
            'index1' => $element1
        ), $elements);

        // update
        $Collection->set('index2', $element3);

        $elements = \PHPUnit_Framework_Assert::readAttribute($Collection, 'elements');

        $this->assertEquals(array(
            'index2' => $element3,
            'index1' => $element1
        ), $elements);
    }
}
