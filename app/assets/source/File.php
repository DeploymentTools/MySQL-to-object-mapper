<?php
namespace MySQLExtractor\assets\source;

class File
{
    public $path;

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }
}
