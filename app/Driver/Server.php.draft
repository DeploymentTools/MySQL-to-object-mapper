<?php
namespace MySQLExtractor\Driver;
use MySQLExtractor\Common\Collection;
use MySQLExtractor\Common\System;
use MySQLExtractor\Exceptions\InvalidPathException;
use MySQLExtractor\Extractor\Databases;

class Disk extends Driver
{
    protected $credentials;
    protected $entries;
    protected $databases = array();

    public function __construct($mysqlCredentials)
    {
        $this->credentials = $mysqlCredentials;
        $this->entries = new Collection();
        $this->databaseExtractor = new Databases();
    }

    public function execute()
    {
        $this->getRemoteDatabases();

        if ($this->entries->count() > 0) {
            $this->databases = $this->databaseExtractor->from($this->entries)->get();
        } else {
            throw new InvalidSourceException("There were no input files found.", 1);
        }
    }

    public function databases()
    {
        return $this->databases;
    }
}
