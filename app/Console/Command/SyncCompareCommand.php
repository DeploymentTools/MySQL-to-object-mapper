<?php
namespace MySQLExtractor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCompareCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notFound = false;
        $source = $input->getArgument('source');
        $destination = $input->getArgument('destination');

        $worker1 = new \MySQLExtractor\Application();
        $worker2 = new \MySQLExtractor\Application();

        preg_match('/(.*):(.*)@(.*)\/(.*)/', $source, $serverSourceDetails);
        preg_match('/(.*):(.*)@(.*)\/(.*)/', $destination, $serverDestinationDetails);

        if (is_array($serverSourceDetails) && count($serverSourceDetails) === 5) {
            $mysqlCredentials = new \stdClass();
            $mysqlCredentials->dbuser = $serverSourceDetails[1];
            $mysqlCredentials->dbpass = $serverSourceDetails[2];
            $mysqlCredentials->host = $serverSourceDetails[3];
            $mysqlCredentials->dbname = $serverSourceDetails[4];

            $worker1->processServer($mysqlCredentials);

        } else {
            // error, path not found
            $notFound = true;
        }

        if (is_array($serverDestinationDetails) && count($serverDestinationDetails) === 5) {
            $mysqlCredentials = new \stdClass();
            $mysqlCredentials->dbuser = $serverDestinationDetails[1];
            $mysqlCredentials->dbpass = $serverDestinationDetails[2];
            $mysqlCredentials->host = $serverDestinationDetails[3];
            $mysqlCredentials->dbname = $serverDestinationDetails[4];

            $worker2->processServer($mysqlCredentials);

        } else {
            // error, path not found
            $notFound = true;
        }

        if ($notFound) {
            $output->write('Error: invalid source format, check if the source path exists and the format is valid.');

        } else {
            $sourceDBs = json_decode(json_encode($worker1->getDatabases()), true);
            $destinationDBs = json_decode(json_encode($worker2->getDatabases()), true);

            $sourceDatabase = end($sourceDBs);
            $destinationDatabase = end($destinationDBs);

            $databaseDiffs = SnapshotCompareCommand::appendDifferencesDatabases($sourceDatabase, $destinationDatabase);
            $output->writeln('Done. ' . PHP_EOL . print_r(json_encode($databaseDiffs, JSON_PRETTY_PRINT), 2));
        }
    }

    protected function configure()
    {
        $this->setName('sync:compare')
            ->setDescription('Compare two server DB instances and outputs their differences.')
            ->addArgument('source', InputArgument::REQUIRED, 'Source server (main / trusted). Format: USER:PASS@HOSTNAME[:PORT]/DATABASE')
            ->addArgument('destination', InputArgument::REQUIRED, 'Destination server. Format: USER:PASS@HOSTNAME[:PORT]/DATABASE');
    }
}
