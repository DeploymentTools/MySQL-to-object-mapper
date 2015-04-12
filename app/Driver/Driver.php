<?php
namespace MySQLExtractor\Driver;

abstract class Driver {
    protected $entries;
    protected $databaseExtractor;
    protected $databases = array();
    abstract public function execute();

    public function databases()
    {
        return $this->databases;
    }
}