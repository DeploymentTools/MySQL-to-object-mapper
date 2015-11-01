<?php
namespace tests\Extractor\Fields;

use MySQLExtractor\Presentation\Table;

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

    /**
     * When table has fields with default values then return them.
     */
    public function testWhenTableHasFieldsWithDefaultValuesThenReturnThem()
    {
        $fieldExtractor = new \MySQLExtractor\Extractor\Fields();
        $inputString = "
              CREATE TABLE `anunturi_angajare` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `companieID` int(11) DEFAULT NULL,
                  `pozitii` int(3) DEFAULT '1',
                  `program_lucru` enum('part-time','full-time') DEFAULT NULL,
                  `durata_contract` enum('nedeterminata','determinata') DEFAULT 'determinata',
                  `profil_scoala_primara` int(1) DEFAULT '0',
                  `persoanacontact_adresa_localitate` varchar(255) DEFAULT \"Bucharest\",
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'This column has 0 as default',
                  PRIMARY KEY (`id`),
                  KEY `companieID` (`companieID`),
                  KEY `ProfilEducatie` (`profil_scoala_primara`,`profil_scoala_profesionala`,`profil_liceu`,`profil_facultate`,`profil_postuniversitar`),
                  KEY `pozitii` (`pozitii`)
                ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
            ";

        /** @var Table $response */
        $response = $fieldExtractor->from($inputString)->getTable();
        static::assertEquals(9, count($response->Fields));

        $resultingValues = [];

        foreach ($response->Fields as $field) {
            $resultingValues[$field->Id] = $field->Default;
        }

        static::assertSame(null, $resultingValues['id']);
        static::assertSame(null, $resultingValues['companieID']);
        static::assertSame('1', $resultingValues['pozitii']);
        static::assertSame(null, $resultingValues['program_lucru']);
        static::assertSame('determinata', $resultingValues['durata_contract']);
        static::assertSame('0', $resultingValues['profil_scoala_primara']);
        static::assertSame('Bucharest', $resultingValues['persoanacontact_adresa_localitate']);
        static::assertSame('CURRENT_TIMESTAMP', $resultingValues['created_at']);
        static::assertSame('0000-00-00 00:00:00', $resultingValues['updated_at']);
    }
}
