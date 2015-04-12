<?php
namespace tests\Extractor\Fields;

class getTableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * when table is found but fragments are not found then return false
     */
    public function testWhenTableIsFoundButFragmentsAreNotFoundThenReturnFalse()
    {
        $fieldExtractor = new \MySQLExtractor\Extractor\Fields();
        $inputString = 'CREATE TABLE `companii` () ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;';

        $response = $fieldExtractor->from($inputString)->getTable();
        $this->assertFalse($response);
    }

    /**
     * when table is found with a primary key then return object
     */
    public function testWhenTableIsFoundWithAPrimaryKeyThenReturnObject()
    {
        $fieldExtractor = new \MySQLExtractor\Extractor\Fields();
        $inputString = 'CREATE TABLE `companii` (PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;';

        $response = $fieldExtractor->from($inputString)->getTable();
        $this->assertInstanceOf('\\MySQLExtractor\\Presentation\\Table', $response);

        $pk = new \MySQLExtractor\Presentation\PrimaryKey();
        $pk->Column = 'id';

        $this->assertEquals('companii', $response->Name);
        $this->assertEquals(0, count($response->Fields));
        $this->assertEquals(1, count($response->Keys));

        $this->assertEquals($pk, $response->Keys[0]);
    }

    /**
     * when table is found with a field and primary key then return object
     */
    public function testWhenTableIsFoundWithAFieldAndAPrimaryKeyThenReturnObject()
    {
        $fieldExtractor = new \MySQLExtractor\Extractor\Fields();
        $inputString = '
        CREATE TABLE `companii` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (`id`),
            KEY `adresa` (`judet`,`localitate`,`strada`,`nr`)
        )
        ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;';

        $response = $fieldExtractor->from($inputString)->getTable();
        $this->assertInstanceOf('\\MySQLExtractor\\Presentation\\Table', $response);

        $pk = new \MySQLExtractor\Presentation\PrimaryKey();
        $pk->Column = 'id';

        $field = new \MySQLExtractor\Presentation\Field();
        $field->Id = 'id';
        $field->Type = 'INT';
        $field->Length = 11;
        $field->Null = false;
        $field->Autoincrement = true;

        $key = new \MySQLExtractor\Presentation\Key();
        $key->Label = 'adresa';
        $key->Columns = array(
            'judet',
            'localitate',
            'strada',
            'nr'
        );

        $this->assertEquals('companii', $response->Name);
        $this->assertEquals(1, count($response->Fields));
        $this->assertEquals(2, count($response->Keys));

        $this->assertEquals($field, $response->Fields[0]);
        $this->assertEquals($pk, $response->Keys[0]);
        $this->assertEquals($key, $response->Keys[1]);
    }
}
