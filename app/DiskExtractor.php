<?php
namespace MySQLExtractor;
use MySQLExtractor\assets\db\Database;
use MySQLExtractor\assets\db\Table;
use MySQLExtractor\assets\db\Field;
use MySQLExtractor\assets\db\Key;
use MySQLExtractor\assets\db\PrimaryKey;

class DiskExtractor
{
    protected $source;
    protected $files = array();
    protected $databases = array();

    protected static $conventions = array(
        'database_from_file_pattern' => array(
            '/([\w]+)\.([\w]+).sql/',
            '/([\w]+).sql/'
        ),
        'tablePattern' => '/CREATE\sTABLE\s(IF NOT EXISTS)?\s?`?([\w]+)`?/',
        'primaryKeyPattern' => '/PRIMARY\sKEY\s\(`([\w]+)`\)/',
        'keyPattern' => '/KEY\s`([\w]+)`\s?\((.*)\)/',
        'defaultValuePattern' => '/DEFAULT\s\'(.*)\'/',
        'lengthValuePattern' => '/^`([\w]+)`\s([\w]+)\(?([0-9]+)?\)?/',
        'fieldNamePattern' => '/^`([\w]+)`\s?(([\w]+)\(?([\d]+)?\)?)?/'
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

    /**
     * @param $fieldString
     * @return PrimaryKey
     */
    public static function extractPrimaryKeyFromString($fieldString)
    {
        $pattern = self::$conventions['primaryKeyPattern'];
        preg_match($pattern, $fieldString, $matches);
        if ($matches) {
            $key = new PrimaryKey;
            $key->Column = $matches[1];
            return $key;
        }
    }

    /**
     * @param $fieldString
     * @return Key
     */
    public static function extractKeyFromString($fieldString)
    {
        $pattern = self::$conventions['keyPattern'];
        preg_match($pattern, $fieldString, $matches);
        if ($matches) {
            $rawColumns = explode('`', $matches[2]);

            $Columns = array();
            foreach ($rawColumns as $rawColumn) {
                $rawColumn = trim($rawColumn);
                if (!empty($rawColumn)) {
                    $Columns[] = $rawColumn;
                }
            }

            if (!empty($Columns)) {
                $key = new Key;
                $key->Label = $matches[1];
                $key->Columns = $Columns;
                return $key;
            }
        }
    }

    public static function extractFieldFromString($fieldString)
    {
        $fieldNamePattern = self::$conventions['fieldNamePattern'];
        preg_match($fieldNamePattern, $fieldString, $matches);
        if ($matches) {
            $field = new Field;
            $field->Id = $matches[1];

            $lengthValuePattern = self::$conventions['lengthValuePattern'];
            preg_match($lengthValuePattern, $fieldString, $matchesType);
            if ($matchesType) {
                $field->Type = strtoupper($matchesType[2]);
                if (isset($matchesType[3]) && !empty($matchesType[3])) {
                    $field->Length = $matchesType[3];
                }
            }

            $defaultValuePattern = self::$conventions['defaultValuePattern'];
            preg_match($defaultValuePattern, $fieldString, $matchesDefault);
            if ($matchesDefault) {
                if (strpos($matchesDefault[1], '\'')) {
                    $matchesDefault[1] = substr($matchesDefault[1], 0, strpos($matchesDefault[1], '\''));
                }
                $field->Default = empty($matchesDefault[1]) ? "" : $matchesDefault[1];
            }

            if (strpos(strtoupper($fieldString), 'AUTO_INCREMENT')) {
                $field->Autoincrement = true;
            }

            $field->Null = !(strpos(strtoupper($fieldString), 'NOT NULL') > 0);
            return $field;
        }
        return false;
    }

    protected function fieldExtractor($fieldString, $table)
    {
        $fieldString = trim($fieldString);

        if ($primaryKey = self::extractPrimaryKeyFromString($fieldString)) {
            $table->Keys[] = $primaryKey;
            return true;
        }

        if ($key = self::extractKeyFromString($fieldString)) {
            $table->Keys[] = $key;
            return true;
        }

        if ($field = self::extractFieldFromString($fieldString)) {
            $table->Fields[] = $field;
            return true;
        }

        return false;
    }

    protected function extractTable($contents)
    {
        $table = new Table;
        $pattern = self::$conventions['tablePattern'];

        preg_match($pattern, $contents, $matches);
        if ($matches) {
            $table->Name = $matches[2];

            $listen = false;
            $parenthesisLevel = 0;
            $fieldsString = "";

            $inSingleQuote = false;
            $inDoubleQuote = false;

            $contentOneLiner = str_replace("\n", " ", $contents);
            for ($i=0; $i < strlen($contentOneLiner); $i++) {
                $char = $contentOneLiner[$i];

                if ($listen) {
                    if (($char == '(') && !$inSingleQuote && !$inDoubleQuote) {
                        $parenthesisLevel++;
                    }
                    if (($char == ')') && !$inSingleQuote && !$inDoubleQuote) {
                        $parenthesisLevel--;
                    }

                    if (($char == ',') && !$inSingleQuote && !$inDoubleQuote && ($parenthesisLevel == 1)) {
                        // field separator
                        $this->fieldExtractor($fieldsString, $table);
                        $fieldsString = "";
                    }

                    if (($char == ')') && ($parenthesisLevel == 0)) {
                        $listen = false;
                        if (trim($fieldsString) != "") {
                            $this->fieldExtractor($fieldsString, $table);
                        }
                    }

                    if ((($char != ',') && !$inSingleQuote && !$inDoubleQuote) || $inSingleQuote || $inDoubleQuote) {
                        $fieldsString .= $char;
                    }
                }

                if (!$listen && ($char == '(')) {
                    $listen = true;
                    $parenthesisLevel = 1;
                }
            }

            return $table;
        }

        return false;
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
