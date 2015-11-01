<?php
$path = dirname(__FILE__);
require_once(realpath($path . '/../vendor/autoload.php'));

$worker = new \MySQLExtractor\Application;

//$mysqlCredentials = new stdClass();
//$mysqlCredentials->host = '127.0.0.1';
//$mysqlCredentials->dbuser = 'root';
//$mysqlCredentials->dbpass = 'test';
//$mysqlCredentials->dbname = 'redmine';
//
//$worker->processServer($mysqlCredentials);
//
// or:
$worker->processDisk($path . '/test_sql/', 'project_mooc'); // folder containing .sql files (format: dbname.sql)

$worker->output($path . '/output/', $worker->getDatabases()); // will output .json files for each database

echo "Finished. Check the output folder.\n\n" . implode("\n", $worker->statistics()) . "\n\n";
