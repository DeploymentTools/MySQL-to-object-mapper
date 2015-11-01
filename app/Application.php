<?php
namespace MySQLExtractor;
use \MySQLExtractor\Common\System;
use \MySQLExtractor\Exceptions\InvalidPathException;

class Application
{
    /**
     * @var \MySQLExtractor\Driver\Driver
     */
    protected $extractor;

    /**
     * Initializes the extractor using the input path and runs the scan.
     *
     * @param $path Input source of SQL dumps that will be scanned.
     * @param string $databaseName Set this to filter a single database from the extracted results.
     * @throws InvalidPathException
     */
    public function processDisk($path, $databaseName = '')
    {
        $this->extractor = new Driver\Disk($path);
        $this->extractor->execute($databaseName);
    }

    /**
     * Initializes the extractor using the input MySQL credentials and runs the scan.
     *
     * @param $mysqlCredentials object {host,dbuser,dbpass,dbname}
     * @throws InvalidPathException
     */
    public function processServer($mysqlCredentials)
    {
        $this->extractor = new Driver\Server($mysqlCredentials);
        $this->extractor->execute($mysqlCredentials->dbname);
    }

    /**
     * For each extracted database, dump a JSON file with the detected structure.
     *
     * @param $path Output folder in which to dump the output
     * @throws InvalidPathException when file does not exist or is invalid
     * @return bool
     */
    public function output($path, $databases = [])
    {
        $fileExists = System::file_exists($path);

        if (!$fileExists || ($fileExists && !System::is_dir($path))) {
            throw new InvalidPathException($path);
        }

        foreach ($databases as $database) {
            $filename = $path . DIRECTORY_SEPARATOR . $database->Name . '-' . date('Y-m-d') . '-' . time() . '.json';
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

    public function getDatabases()
    {
        return $this->extractor->databases();
    }
}
