<?php
namespace MySQLExtractor;

class Worker
{
    protected $extractor;

    public function processDisk($path)
    {
        $this->extractor = new DiskExtractor($path);
        $this->extractor->run();
    }

    public function output($path)
    {
        if (!file_exists($path) || (file_exists($path) && !is_dir($path))) {
            throw new exceptions\InvalidPathException($path);
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
