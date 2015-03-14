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

        $results = $this->results();
        foreach ($results as $database) {
            file_put_contents($path . DIRECTORY_SEPARATOR . $database->Name . '.json', json_encode($database, JSON_PRETTY_PRINT));
        }
    }

    public function results()
    {
        return $this->extractor->results();
    }
}
