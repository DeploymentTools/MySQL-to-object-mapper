<?php
date_default_timezone_set('UTC');
set_time_limit(0);

include_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

$app = new \Symfony\Component\Console\Application('MySQL Extractor', '1.0.0');

// reset
$app->setDefinition(
    new \Symfony\Component\Console\Input\InputDefinition([
        new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
    ])
);

// commands
$app->addCommands([
    new MySQLExtractor\Console\Command\SnapshotCompareCommand(),
    new MySQLExtractor\Console\Command\SnapshotCreateCommand(),
    new MySQLExtractor\Console\Command\SyncCompareCommand(),
]);

try {
    $app->run();

} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL . 'File: ' . $e->getFile() . ' (' . $e->getLine() . ')' . PHP_EOL;
}
