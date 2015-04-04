<?php
namespace MySQLExtractor;
use MySQLExtractor\assets\db\Database;

class DiskExtractor
{
    protected $source;
    protected $files = array();
    protected $databases = array();

    protected static $conventions = array(
        'database_from_file_pattern' => array(
            '/([\w]+)\.([\w]+).sql/',
            '/([\w]+).sql/'
        )
    );

    public function __construct($path)
    {
        if (!System::file_exists($path)) {
            throw new exceptions\InvalidPathException($path);
        }

        if (System::is_dir($path)) {
            $this->source =  new assets\source\Folder;
        } else {
            $this->source =  new assets\source\File;
        }

        $this->source->setPath($path);

        $this->TableExtractor = new extractor\Table();
    }

    public function run()
    {
        $this->prepareInputFiles();
        $this->processFiles();
    }

    private function processFiles()
    {
        $this->databases = array();

        foreach ($this->files as $key => $value) {
            $databaseName = $this->getDatabaseName($key);

            if (!isset($this->databases[$databaseName])) {
                $database = new Database;
                $database->Name = $databaseName;
                $this->databases[$databaseName] = $database;
            }

            $rawExtracted = $this->extractTables($value);
            $this->databases[$databaseName]->Tables = array_merge($this->databases[$databaseName]->Tables, $rawExtracted);
            if (count($this->databases[$databaseName]->Tables) == 0) {
                unset($this->databases[$databaseName]);
            }
        }
    }

    private function extractTables($contents)
    {
        // appended delimiter, for safety
        $contents = $contents . "\n;";
        $contentsLines = explode("\n", $contents);

        $tables = array();
        $listenForTables = false;
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inLineComment = false;
        $inComment = false;

        $currentTable = "";
        $prevChar = false;

        foreach ($contentsLines as $key => $line) {
            if (strpos(trim(strtoupper($line)), 'CREATE TABLE') === 0) {
                $listenForTables = true;
            }

            if ($listenForTables) {
                for ($i = 0; $i < strlen($line); $i++) {
                    $char = $line[$i];

                    if (($prevChar == '-') && ($char == '-') && !$inSingleQuote && !$inDoubleQuote) {
                        $inLineComment = true;
                    }

                    if ($char == '*' && $prevChar == '/') {
                        $inComment = true;
                    }

                    if ($char == '/' && $prevChar == '*') {
                        $inComment = false;
                    }

                    // if ($char == "'" && !$in_double_quote) {
                    //     $in_single_quote = !$in_single_quote;
                    // }

                    // if ($char == '"' && !$in_single_quote) {
                    //     $in_double_quote = !$in_double_quote;
                    // }

                    if (!$inComment && !$inLineComment && !$inDoubleQuote && !$inSingleQuote) {
                        if ($char == ';') {
                            // end of table
                            if (trim(str_replace("\n", "", $currentTable)) != "") {
                                $extractedTable = $this->extractTable($currentTable);
                                if (!empty($extractedTable)) {
                                    $tables[] = $extractedTable;
                                }
                            }
                            $currentTable = "";
                        } else {
                            $currentTable .= $char;
                        }
                    }

                    $prevChar = $char;
                }
            }

            // $current_table .= " ";
            $currentTable .= "\n";
            $inLineComment = false;
            $inComment = false;
            $prevChar = false;
        }

        return $tables;
    }

    protected function extractTable($contents)
    {
        return $this->TableExtractor
            ->from($contents)
            ->getTable();
    }

    private function getDatabaseName($filename)
    {
        $filename = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $filename);

        if (self::$conventions['database_from_file_pattern']) {
            $filenameTemp = explode(DIRECTORY_SEPARATOR, $filename);
            $filenameTemp = end($filenameTemp);
            foreach (self::$conventions['database_from_file_pattern'] as $pattern) {
                preg_match($pattern, $filenameTemp, $matches);
                if ($matches) {
                    return $matches[1];
                }
            }
        }

        // fallback on folder
        $filenameTemp = array_filter(explode(DIRECTORY_SEPARATOR, $filename));
        if (is_array($filenameTemp) && count($filenameTemp) > 2) {
            $filenameTemp = $filenameTemp[count($filenameTemp) - 2];
        }

        if (!empty($filenameTemp)) {
            return $filenameTemp;
        }

        return '_no_name_';
    }

    private function prepareInputFiles()
    {
        $path = $this->source->getPath();
        $this->files = array();

        if ($this->source instanceof assets\source\Folder) {
            foreach (new \DirectoryIterator($path) as $fileInfo) {
                if($fileInfo->isDot() || $fileInfo->isDir()) {
                    continue;
                }
                $this->files[$path . '/' . $fileInfo->getFilename()] = null;
            }
        } else {
            $this->files[$path] = null;
        }

        if (empty($this->files)) {
            throw new exceptions\InvalidSourceException("There were no input files found.", 1);
        }

        // get contents
        foreach ($this->files as $filename => $value) {
            $this->files[$filename] = file_get_contents($filename);
        }
    }

    public function databases()
    {
        return $this->databases;
    }
}
