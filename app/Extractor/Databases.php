<?php
namespace MySQLExtractor\Extractor;
use \MySQLExtractor\Common\Collection;
use \MySQLExtractor\Presentation\Database as DatabaseItem;

class Databases {

    protected $files;
    protected $databases;
    protected $tablesExtractor;
    protected $patterns = array(
        '/([\w]+)\.([\w]+).sql$/',
        '/([\w]+).sql$/'
    );

    public function from(Collection $files)
    {
        $this->reset();
        $this->files = $files;
        return $this;
    }

    protected function reset()
    {
        $this->databases = new Collection();
        $this->tablesExtractor = new Tables();
    }

    public function get()
    {
        foreach ($this->files->toArray() as $key => $fileContents) {
            $databaseName = $this->getDatabaseName($key);
            $this->initDatabaseEntry($databaseName);
            $this->appendDatabaseTables($databaseName, $fileContents);
        }
        return $this->databases->toArray();
    }

    protected function getDatabaseName($filename)
    {
        foreach ($this->patterns as $pattern) {
            preg_match($pattern, $filename, $matches);
            if ($matches) {
                return $matches[1];
            }
        }
        return '_no_name_';
    }

    protected function initDatabaseEntry($databaseName)
    {
        if (!$this->databases->containsKey($databaseName)) {
            $this->databases->set($databaseName, new DatabaseItem($databaseName));
        }
    }

    protected function appendDatabaseTables($databaseName, $fileContents)
    {
        $this->databases->get($databaseName)->appendTables(
            $this->tablesExtractor->from($fileContents)->get()
        );
    }
}
