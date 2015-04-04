<?php
namespace MySQLExtractor;
use \MySQLExtractor\Helper\System;
use \MySQLExtractor\Exceptions\InvalidPathException;

class Worker
{
    protected $extractor;

    public function processDisk($path)
    {
        $this->extractor = new DiskExtractor\Main($path);
        $this->extractor->run();
    }

    public function output($path)
    {
        $fileExists = System::file_exists($path);
        $isDir = System::is_dir($path);

        if (!$fileExists || ($fileExists && !$isDir)) {
            throw new InvalidPathException($path);
        }

        foreach ($this->extractor->databases() as $database) {
            file_put_contents($path . DIRECTORY_SEPARATOR . $database->Name . '.json', json_encode($database, JSON_PRETTY_PRINT));
        }
    }

    public function statistics()
    {
        $databases = $this->extractor->databases();
        $results = array();
        foreach ($databases as $database) {
            $results[] = "- database [" . $database->Name . "] with [" . count($database->Tables) . "] tables.";
        }
        return $results;
    }
}
