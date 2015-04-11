<?php
namespace MySQLExtractor\Driver;
use MySQLExtractor\Common\Collection;
use MySQLExtractor\Common\System;
use MySQLExtractor\Exceptions\InvalidPathException;
use MySQLExtractor\Extractor\Databases;

class Disk extends Driver
{
    protected $source;
    protected $files;
    protected $databases = array();

    public function __construct($path)
    {
        if (!System::file_exists($path)) {
            throw new InvalidPathException($path);
        }

        $this->source = $path;
        $this->files = new Collection();
        $this->databaseExtractor = new Databases();
    }

    protected function appendSource($filename = null)
    {
        $filename = $this->source . ((is_null($filename)) ? '' : DIRECTORY_SEPARATOR . $filename);
        $this->files->set($filename, System::file_get_contents($filename));
    }

    public function execute()
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

        if ($this->files->count() > 0) {
            $this->databases = $this->databaseExtractor->from($this->files)->get();
        } else {
            throw new InvalidSourceException("There were no input files found.", 1);
        }
    }

    public function databases()
    {
        return $this->databases;
    }
}
