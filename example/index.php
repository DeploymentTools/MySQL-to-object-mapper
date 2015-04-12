<?php
$path = dirname(__FILE__);
require_once(realpath($path . '/../vendor/autoload.php'));

$worker = new \MySQLExtractor\Application;

// $mysqlCredentials = new stdClass();
// $mysqlCredentials->host = 'localhost:3306';
// $mysqlCredentials->dbuser = 'user';
// $mysqlCredentials->dbpass = 'pass';
// $mysqlCredentials->dbname = 'database';
//
// $worker->processServer($mysqlCredentials);
//
// or:
$worker->processDisk($path . '/test_sql/'); // folder containing .sql files (format: dbname.sql)

$worker->output($path . '/output/'); // will output .json files for each database

echo "Finished. Check the output folder.\n\n" . implode("\n", $worker->statistics()) . "\n\n";
