<?php
namespace MySQLExtractor\Driver;
use MySQLExtractor\Common\Collection;
use MySQLExtractor\Common\System;
use MySQLExtractor\Extractor\Databases;
use MySQLExtractor\Exceptions\InvalidSourceException;

class Server extends Driver
{
    protected $PDO;

    public function __construct($mysqlCredentials)
    {
        $this->entries = new Collection();
        $this->databaseExtractor = new Databases();
        $this->PDO = System::getPDO($mysqlCredentials);
    }

    public function execute()
    {
        $this->extractRemoteDatabases();

        if ($this->entries->count() > 0) {
            $this->databases = $this->databaseExtractor->from($this->entries)->get();
        } else {
            throw new InvalidSourceException("There were no entries found.", 1);
        }
    }

    protected function extractRemoteDatabases()
    {
        $databases = $this->PDO->query('SHOW DATABASES');
        foreach ($databases as $database) {
            System::flush();
            $this->extractDatabaseDefinition($database[0]);
        }
    }

    protected function extractDatabaseDefinition($databaseName)
    {
        $this->PDO->query("use " . $databaseName);
        $tables = $this->PDO->query('SHOW TABLES');

        foreach ($tables as $table) {
            System::flush();
            $this->extractTableDefinition($table[0], $databaseName);
        }
    }

    protected function extractTableDefinition($tableName, $databaseName)
    {
        $tableContents = $this->PDO->query("SHOW CREATE TABLE `" . $tableName . "`")->fetch();

        if (is_array($tableContents) && isset($tableContents['Create Table'])) {
            $filename = $databaseName . '.' . $tableName . '.sql';
            $this->entries->set($filename, $tableContents['Create Table']);
        }
    }
}
