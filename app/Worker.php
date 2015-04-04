<?php
namespace MySQLExtractor;
use \MySQLExtractor\Common\System;
use \MySQLExtractor\Exceptions\InvalidPathException;

class Worker
{
    /**
     * @var DiskExtractor\Main
     */
    protected $extractor;

    /**
     * Initializes the extractor using the input path and runs the scan.
     *
     * @param $path Input source of SQL dumps that will be scanned.
     * @throws DiskExtractor\InvalidSourceException
     */
    public function processDisk($path)
    {
        $this->extractor = new DiskExtractor\Main($path);
        $this->extractor->run();
    }

    /**
     * For each extracted database, dump a JSON file with the detected structure.
     *
     * @param $path Output folder in which to dump the output
     * @throws InvalidPathException when file does not exist or is invalid
     * @return bool
     */
    public function output($path)
    {
        $fileExists = System::file_exists($path);

        if (!$fileExists || ($fileExists && !System::is_dir($path))) {
            throw new InvalidPathException($path);
        }

        foreach ($this->extractor->databases() as $database) {
            $filename = $path . DIRECTORY_SEPARATOR . $database->Name . '.json';
            System::file_put_contents($filename, json_encode($database, JSON_PRETTY_PRINT));
        }

        return true;
    }

    /**
     * Returns an array of messages showing the databases (and the number of tables) that were extracted.
     * @return array
     */
    public function statistics()
    {
        $databases = $this->extractor->databases();
        $results = array();

        if (!empty($databases)) {
            foreach ($databases as $database) {
                $results[] = "- database [" . $database->Name . "] with [" . count($database->Tables) . "] tables.";
            }
        }

        return $results;
    }
}
