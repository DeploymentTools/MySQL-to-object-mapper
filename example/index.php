<?php
$path = dirname(__FILE__);
require_once(realpath($path . '/../vendor/autoload.php'));

$worker = new \MySQLExtractor\Application;

$worker->processDisk($path . '/test_sql/'); // folder containing .sql files (format: dbname.sql)
$worker->output($path . '/output/'); // will output .json files for each database

echo "Finished. Check the output folder.\n\n" . implode("\n", $worker->statistics()) . "\n\n";
