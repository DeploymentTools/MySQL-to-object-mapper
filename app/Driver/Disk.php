<?php
namespace MySQLExtractor\Driver;
use MySQLExtractor\Common\Collection;
use MySQLExtractor\Common\System;
use MySQLExtractor\Exceptions\InvalidPathException;
use MySQLExtractor\Extractor\Databases;

class Disk extends Driver
{
    protected $source;
    protected $entries;
    protected $databases = array();

    public function __construct($path)
    {
        if (!System::file_exists($path)) {
            throw new InvalidPathException($path);
        }

        $this->source = $path;
        $this->entries = new Collection();
        $this->databaseExtractor = new Databases();
    }

    protected function appendSource($filename = null)
    {
        $filename = $this->source . ((is_null($filename)) ? '' : DIRECTORY_SEPARATOR . $filename);
        $this->entries->set($filename, System::file_get_contents($filename));
    }

    private function prepareSourceEntries()
    {
        if (System::is_dir($this->source)) {
            foreach (new \DirectoryIterator($this->source) as $fileInfo) {
                if(!$fileInfo->isDot() && !$fileInfo->isDir()) {
                    $this->appendSource($fileInfo->getFilename());
                }
            }
        } else {
            $this->appendSource();
        }
    }

    public function execute()
    {
        $this->prepareSourceEntries();

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
