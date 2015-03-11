<?php

$folder = "/projects/mysql-schema-graph/temp/sql_dump_testing/";
$databases = array();

foreach (new DirectoryIterator($folder) as $fileInfo) {
    if($fileInfo->isDot() || $fileInfo->isDir()) {
        continue;
    }

    $file = $fileInfo->getFilename();
    $file_analyze = preg_match('/([\w]+).([\w]+).sql/', $file, $matches);
    if ($matches) {
        $database = $matches[1];
        $table = $matches[2];
        $databases[$database][$table] = extractFromFile($folder . '/' . $fileInfo->getFilename());
    }
    // echo $fileInfo->getFilename() . " --  " . json_encode($matches) . " \n";
}

function extractFromFile($path)
{
    $contents = file_get_contents($path);
    $contents_lines = explode("\n", $contents . "\n;"); // appended delimiter, as safety

    $tables = array();
    $listen_for_tables = false;
    $in_single_quote = false;
    $in_double_quote = false;
    $in_line_comment = false;

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

                // if ($char == "'" && !$in_double_quote) {
                //     $in_single_quote = !$in_single_quote;
                // }

                // if ($char == '"' && !$in_single_quote) {
                //     $in_double_quote = !$in_double_quote;
                // }

                if (!$in_line_comment && !$in_double_quote && !$in_single_quote) {
                    if ($char == ';') {
                        // end of table
                        if (trim(str_replace("\n", "", $current_table)) != "") {
                            $tables[] = $current_table;
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
        $previous_char = false;
    }

    return $tables;
}

function extract_table($contents)
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

                if (($char == ',') && !$in_single_quote && !$in_double_quote) {
                    // field separator
                    $extracted_field = field_extractor($fields_string);
                    if ($extracted_field) {
                        $fields_array[] = $extracted_field;
                    }
                    $fields_string = "";
                }

                if (($char == ')') && ($paranthesis_level == 0)) {
                    $listen = false;
                    if (trim($fields_string) != "") {
                        $extracted_field = field_extractor($fields_string);
                        if ($extracted_field) {
                            $fields_array[] = $extracted_field;
                        }
                    }
                    // eject fields
                    // print_r($fields_array);
                    // die();
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

function field_extractor($field_string)
{
    $field_string = trim($field_string);

    preg_match('/`([\w]+)`\s?(([\w]+)\(?([\d]+)?\)?)?\s(NOT NULL|NULL)?\s(DEFAULT\s\'(.*)\')?(.*)?\s?(COMMENT\s(.*))?/', $field_string, $matches);
    if ($matches) {
        $field = new Field;
        $field->Id = $matches[1];
        $field->Type = $matches[3];
        $field->Length = $matches[4];

        if (isset($matches[5])) {
            $field->NotNull = !(strtoupper($matches[5]) == 'NULL');
        }

        if (isset($matches[7])) {
            $field->Default = trim($matches[7]);
        }

        // if (count($matches) > 7 && trim($matches[8]) != "") {
        //     print_r($matches);
        //     die();
        // }

        return $field;
    }

    return false;
}

// print_r($databases);
////////////////////////////////

$extractedDatabases = array();
foreach ($databases as $databaseName => $tables) {
    $extractedDatabases[$databaseName] = array();
    foreach ($tables as $tableName => $table) {
        foreach ($table as $entry) {
            $extracted_entry = extract_table($entry);
            if ($extracted_entry) {
                $extractedDatabases[$databaseName][$extracted_entry->Name] = $extracted_entry;
            }
        }
    }
    ksort($extractedDatabases[$databaseName]);
}



$html_start = '<!DOCTYPE html>
<html lang="en" class="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Language" content="en">
        <script src="js/facade.min.js"></script>
        <script>
            window.onload = function () {
                var stage = new Facade(document.querySelector("canvas"));
                // var rect = new Facade.Rect({ width: 100, height: 1, fillStyle: "red" });
                // stage.addToStage(rect);
                ';

$html_middle = "";


$html_end = '}
        </script>
    </head>
    <body>
        <canvas width="6000" height="6000" id="canvas"></canvas>
    </body>
</html>';


$cursorX = 0;
$cursorY = 0;
foreach ($extractedDatabases['database_1'] as $key => $table) {
    $table_fields = array();

    $table_fields[] = "** " . $table->Name . " **";

    foreach ($table->Fields as $Field) {
        $table_fields[] = $Field->Id;
    }

    $html_middle .= '
        var text = new Facade.Text("'.implode("\\n", $table_fields).'", {
            x: '.($cursorX*300).',
            y: '.($cursorY*350).',
            fontSize: 12
        });

        stage.addToStage(text);
        ';

    $cursorX++;

    if ($cursorX > 15) {
        $cursorX = 0;
        $cursorY++;
    }
}

$html = $html_start . $html_middle . $html_end;

file_put_contents('public/index.html', $html);

// print_r($extractedDatabases);
////////////////////////////////


class Table {
    public $Name;
    public $Fields = array();
    // public $Keys = array();
}

class Field {
    public $Id;
    public $Type;
    public $Length;
    public $Default;
    // public $Comment = '';
    // public $Autoincrement = false;
    public $NotNull = false;
    // public $PK = false;
}