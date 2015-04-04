<?php
namespace MySQLExtractor\DiskExtractor;
use \MySQLExtractor\Common\Collection;
use \MySQLExtractor\Presentation\Database as DatabaseItem;

class Databases {

    protected $files;
    protected $databases;
    protected $tablesExtractor;

    public function from(Collection $files)
    {
        $this->files = $files;
        $this->databases = new Collection();
        $this->tablesExtractor = new Tables();

        return $this;
    }

    public function get()
    {
        foreach ($this->files->toArray() as $key => $fileContents) {
            $databaseName = $this->getDatabaseName($key);

            if (!$this->databases->containsKey($databaseName)) {
                $database = new DatabaseItem($databaseName);
                $this->databases->set($databaseName, $database);
            }

            $this->databases->get($databaseName)->appendTables(
                $this->tablesExtractor->from($fileContents)->get()
            );
        }

        return $this->databases->toArray();
    }

    private function getDatabaseName($filename)
    {
        $filename = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $filename);
        $filenameTemp = end(explode(DIRECTORY_SEPARATOR, $filename));

        $patterns = array(
            '/([\w]+)\.([\w]+).sql/',
            '/([\w]+).sql/'
        );

        foreach ($patterns as $pattern) {
            preg_match($pattern, $filenameTemp, $matches);
            if ($matches) {
                return $matches[1];
            }
        }

        return '_no_name_';
    }
}
