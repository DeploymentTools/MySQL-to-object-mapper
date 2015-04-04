<?php
namespace MySQLExtractor\Presentation;

class Database
{
    public $Name;
    public $Tables;

    public function __construct($Name = null, $Tables = array())
    {
        $this->Name = $Name;
        $this->Tables = $Tables;
    }

    public function appendTables($Tables = array())
    {
        $this->Tables = array_filter(array_merge($this->Tables, $Tables));
    }
}
