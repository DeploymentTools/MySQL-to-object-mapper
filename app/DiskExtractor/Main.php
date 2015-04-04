<?php
namespace MySQLExtractor\DiskExtractor;
use MySQLExtractor\Presentation\Database;
use MySQLExtractor\Exceptions\InvalidPathException;
use MySQLExtractor\Helper\System;

class Main
{
    protected $source;
    protected $files = array();
    protected $databases = array();

    public function __construct($path)
    {
        if (!System::file_exists($path)) {
            throw new InvalidPathException($path);
        }

        $this->source = $path;
        $this->TableExtractor = new Table();
    }

    public function run()
    {
        if (System::is_dir($this->source)) {
            foreach (new \DirectoryIterator($this->source) as $fileInfo) {
                if(!$fileInfo->isDot() && !$fileInfo->isDir()) {
                    $filename = $this->source . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
                    $this->files[$filename] = System::file_get_contents($filename);
                }
            }
        } else {
            $this->files[$this->source] = System::file_get_contents($filename);
        }

        if (empty($this->files)) {
            throw new InvalidSourceException("There were no input files found.", 1);
        }

        $this->databases = array();

        foreach ($this->files as $key => $value) {
            $databaseName = $this->getDatabaseName($key);

            if (!isset($this->databases[$databaseName])) {
                $database = new Database;
                $database->Name = $databaseName;
                $this->databases[$databaseName] = $database;
            }

            $this->databases[$databaseName]->Tables = array_merge(
                $this->databases[$databaseName]->Tables,
                $this->extractTables($value)
            );

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

    public function databases()
    {
        return $this->databases;
    }
}
