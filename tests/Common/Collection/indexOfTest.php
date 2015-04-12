<?php
namespace tests\Common\Collection;

class indexOfTest extends \PHPUnit_Framework_TestCase
{
    /**
     * indexOf will return the key name for the element
     */
    public function testIndexOfWillReturnTheKeyNameForTheElement()
    {
        $element1 = new \stdClass();
        $element1->Name = 'EL 1';

        $element2 = new \stdClass();
        $element2->Name = 'EL 2';

        $Collection = new \MySQLExtractor\Common\Collection();

        $Collection->set('index2', $element2);
        $Collection->set('index1', $element1);

        $this->assertEquals('index1', $Collection->indexOf($element1));
        $this->assertEquals('index2', $Collection->indexOf($element2));
    }
}
