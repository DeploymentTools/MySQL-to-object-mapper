<?php
namespace MySQLExtractor;
use MySQLExtractor\assets\db\Database;
use MySQLExtractor\assets\db\Table;
use MySQLExtractor\assets\db\Field;
use MySQLExtractor\assets\db\Key;

class DiskExtractor
{
    protected $source;
    protected $files = array();
    protected $databases = array();

    protected $conventions = array(
        'database_from_file_pattern' => '/([\w]+).([\w]+).sql/'
    );

    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new exceptions\InvalidPathException($path);
        }

        if (is_dir($path)) {
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
        }
    }

    private function extractTables($contents)
    {
        $contents_lines = explode("\n", $contents . "\n;"); // appended delimiter, as safety

        $tables = array();
        $listen_for_tables = false;
        $in_single_quote = false;
        $in_double_quote = false;
        $in_line_comment = false;
        $in_comment = false;

        $current_table = "";
        $previous_char = false;

        foreach ($contents_lines as $key => $line) {
            if (strpos(trim(strtoupper($line)), 'CREATE TABLE') === 0) {
                $listen_for_tables = true;
            }

            if ($listen_for_tables) {
                for ($i = 0; $i < strlen($line); $i++) {
                    $char = $line[$i];

                    if (($previous_char == '-') && ($char == '-') && !$in_single_quote && !$in_double_quote) {
                        $in_line_comment = true;
                    }

                    if ($char == '*' && $previous_char == '/') {
                        $in_comment = true;
                    }

                    if ($char == '/' && $previous_char == '*') {
                        $in_comment = false;
                    }

                    // if ($char == "'" && !$in_double_quote) {
                    //     $in_single_quote = !$in_single_quote;
                    // }

                    // if ($char == '"' && !$in_single_quote) {
                    //     $in_double_quote = !$in_double_quote;
                    // }

                    if (!$in_comment && !$in_line_comment && !$in_double_quote && !$in_single_quote) {
                        if ($char == ';') {
                            // end of table
                            if (trim(str_replace("\n", "", $current_table)) != "") {
                                $extract_table = $this->extract_table($current_table);
                                if (!empty($extract_table)) {
                                    $tables[] = $extract_table;
                                }
                            }
                            $current_table = "";
                        } else {
                            $current_table .= $char;
                        }
                    }

                    $previous_char = $char;
                }
            }

            // $current_table .= " ";
            $current_table .= "\n";
            $in_line_comment = false;
            $in_comment = false;
            $previous_char = false;
        }

        return $tables;
    }

    private function field_extractor($field_string, $table)
    {
        $field_string = trim($field_string);

        $primary = preg_match('/PRIMARY\sKEY\s\(`([\w]+)`\)/', $field_string, $matches);
        if ($matches) {
            $key = new Key;
            $key->Id = $matches[1];
            $key->Label = $matches[1];
            $key->Primary = true;
            $table->Keys[] = $key;
            return false;
        }

        $primary = preg_match('/KEY\s`([\w]+)`\s?\((.*)\)/', $field_string, $matches);
        if ($matches) {
            $fields_raw = explode('`', $matches[2]);

            $ids = array();
            foreach ($fields_raw as $field_raw) {
                $field_raw = trim($field_raw);
                if (!empty($field_raw)) {
                   $ids[] = $field_raw;
                }
            }

            if (!empty($ids)) {
                $key = new Key;
                $key->Id = (count($ids) > 1) ? $ids : $ids[0];
                $key->Label = $matches[1];
                $key->Primary = false;
                $table->Keys[] = $key;
            }
            return false;
        }

        // preg_match('/`([\w]+)`\s?(([\w]+)\(?([\d]+)?\)?)?\s(NOT NULL|NULL)?\s(DEFAULT\s\'(.*)\')?(.*)?\s?(COMMENT\s(.*))?/', $field_string, $matches);
        preg_match('/^`([\w]+)`\s?(([\w]+)\(?([\d]+)?\)?)?/', $field_string, $matches);
        if ($matches) {
            $field = new Field;
            $field->Id = $matches[1];

            preg_match('/^`([\w]+)`\s([\w]+)\(?([0-9]+)?\)?/', $field_string, $matches_type);
            if ($matches_type) {
                $field->Type = $matches_type[2];
                if (isset($matches_type[3]) && !empty($matches_type[3])) {
                    $field->Length = $matches_type[3];
                }
            }

            preg_match('/DEFAULT\s\'(.*)\'/i', $field_string, $matches_default);
            if ($matches_default) {
                $field->Default = $matches_default[1];
            }

            if (strpos(strtoupper($field_string), 'AUTO_INCREMENT')) {
                $field->Autoincrement = true;
            }

            if (strpos(strtoupper($field_string), 'NOT NULL')) {
                $field->Null = false;
            } else if (strpos(strtoupper($field_string), 'NULL')) {
                $field->Null = true;
            }

            // $field->Raw = $field_string;
            return $field;
        }

        return false;
    }

    private function extract_table($contents)
    {
        $table = new Table;

        preg_match('/CREATE\sTABLE\s`?([\w]+)`?/', $contents, $matches);
        if ($matches) {
            $table->Name = $matches[1];

            $listen = false;
            $paranthesis_level = 0;
            $fields_string = "";
            $fields_array = array();

            $in_single_quote = false;
            $in_double_quote = false;

            $content_one_liner = str_replace("\n", " ", $contents);
            for ($i=0; $i < strlen($content_one_liner); $i++) { 
                $char = $content_one_liner[$i];

                if ($listen) {
                    if (($char == '(') && !$in_single_quote && !$in_double_quote) {
                        $paranthesis_level++;
                    }
                    if (($char == ')') && !$in_single_quote && !$in_double_quote) {
                        $paranthesis_level--;
                    }

                    if (($char == ',') && !$in_single_quote && !$in_double_quote && ($paranthesis_level == 1)) {
                        // field separator
                        $extracted_field = $this->field_extractor($fields_string, $table);
                        if ($extracted_field) {
                            $fields_array[] = $extracted_field;
                        }
                        $fields_string = "";
                    }

                    if (($char == ')') && ($paranthesis_level == 0)) {
                        $listen = false;
                        if (trim($fields_string) != "") {
                            $extracted_field = $this->field_extractor($fields_string, $table);
                            if ($extracted_field) {
                                $fields_array[] = $extracted_field;
                            }
                        }
                    }

                    if ((($char != ',') && !$in_single_quote && !$in_double_quote) || $in_single_quote || $in_double_quote) {
                        $fields_string .= $char;
                    }
                }

                if (!$listen && ($char == '(')) {
                    $listen = true;
                    $paranthesis_level = 1;
                }
            }

            $table->Fields = $fields_array;

            return $table;
        }

        return false;
    }

    private function getDatabaseName($filename)
    {
        $filename = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $filename);

        if ($this->conventions['database_from_file_pattern']) {
            $filenameTemp = explode(DIRECTORY_SEPARATOR, $filename);
            $filenameTemp = end($filenameTemp);
            $file_analyze = preg_match($this->conventions['database_from_file_pattern'], $filenameTemp, $matches);
            if ($matches) {
                return $matches[1];
            }
        }

        // fallback on folder
        $filenameTemp = explode(DIRECTORY_SEPARATOR, $filename);
        $filenameTemp = $filenameTemp[count($filenameTemp) - 2];
        if (!empty($filenameTemp)) {
            return $filenameTemp;
        }

        return '__no-name__';
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

    public function results()
    {
        return $this->databases;
    }
}
