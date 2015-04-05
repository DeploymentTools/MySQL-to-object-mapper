<?php
namespace tests\Common\Collection;

class removeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * remove will return null if the requested key is not set
     */
    public function testRemoveWillReturnNullIfTheRequestedKeyIsNotSet()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertNull($Collection->remove('index3'));
    }

    /**
     * remove will return removed element if the requested key is set
     */
	public function testRemoveWillReturnRemovedElementIfTheRequestedKeyIsSet()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertSame($element2, $Collection->remove('index2'));
    }
}
