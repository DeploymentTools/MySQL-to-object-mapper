<?php
$path = dirname(__FILE__);
require_once(realpath($path . '/../vendor/autoload.php'));

$worker = new \MySQLExtractor\Worker;
$worker->processDisk($path . '/test_sql');
$worker->output($path . '/output');

// echo '<pre>'; print_r($worker->results()); echo '</pre>';
echo 'Finished. Check the output folder.';
