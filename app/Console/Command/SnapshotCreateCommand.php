<?php
namespace MySQLExtractor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotCreateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notFound = false;
        $source = $input->getArgument('source');
        $outputPath = $input->getArgument('output');

        $worker = new \MySQLExtractor\Application();

        $scanPathname = realpath($source);
        preg_match('/(.*):(.*)@(.*)\/(.*)/', $source, $serverDetails);

        if (is_array($serverDetails) && count($serverDetails) === 5) {
            $mysqlCredentials = new \stdClass();
            $mysqlCredentials->dbuser = $serverDetails[1];
            $mysqlCredentials->dbpass = $serverDetails[2];
            $mysqlCredentials->host = $serverDetails[3];
            $mysqlCredentials->dbname = $serverDetails[4];

            $worker->processServer($mysqlCredentials);

        } else if ($scanPathname) {
            $worker->processDisk($scanPathname);

        } else {
            // error, path not found
            $notFound = true;
        }

        if ($notFound) {
            $output->write('Error: invalid source format, check if the source path exists and the format is valid.');

        } else {
            $worker->output($outputPath, $results = $worker->getDatabases()); // will output .json files for each database
            $output->write('Finished. Check the output folder.' . PHP_EOL . implode(PHP_EOL, $worker->statistics()) . PHP_EOL);
        }
    }

    protected function configure()
    {
        $this->setName('snapshot:create')
            ->setDescription('Scans a DB and outputs the structure.')
            ->addArgument('source', InputArgument::REQUIRED, 'DB instance to be analysed. Format: USER:PASS@HOSTNAME[:PORT]/DATABASE or /PATH/TO/SQL/DUMPS/')
            ->addArgument('output', InputArgument::REQUIRED, 'Output folder for the results file(s), where the database structure will be dumped in JSON format. Data will be stored in [databaseName]-[date]-[timestamp].json format.');
    }
}
