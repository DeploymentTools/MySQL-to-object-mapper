<?php
namespace MySQLExtractor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotCompareCommand extends Command
{
    const ERROR_MESSAGE_INVALID_PATH = 'Error: invalid %s path, check if the path exists.';
    const ERROR_MESSAGE_INVALID_CONTENTS = 'Error: invalid %s contents, could not get details. Check if the file is a valid snapshot output.';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourcePath = $input->getArgument('source');
        $destinationPath = $input->getArgument('destination');

        // fetch
        $sourcePath = realpath($sourcePath);
        $destinationPath = realpath($destinationPath);

        $errors = [];
        $errors[] = (!$sourcePath) ? sprintf(self::ERROR_MESSAGE_INVALID_PATH, 'source') : null;
        $errors[] = (!$destinationPath) ? sprintf(self::ERROR_MESSAGE_INVALID_PATH, 'destination') : null;

        // load
        $sourceDatabase = json_decode(file_get_contents($sourcePath), true);
        $destinationDatabase = json_decode(file_get_contents($destinationPath), true);

        $errors[] = (!$sourceDatabase) ? sprintf(self::ERROR_MESSAGE_INVALID_CONTENTS, 'source') : null;
        $errors[] = (!$destinationDatabase) ? sprintf(self::ERROR_MESSAGE_INVALID_CONTENTS, 'destination') : null;

        // report errors
        $errors = array_filter($errors);

        if (count($errors) > 0) {
            $output->writeln(implode(PHP_EOL, $errors));

        } else {
            $databaseDiffs = self::appendDifferencesDatabases($sourceDatabase, $destinationDatabase);
            $output->writeln('Done. ' . PHP_EOL . print_r(json_encode($databaseDiffs, JSON_PRETTY_PRINT), 2));
        }
    }

    public static function appendDifferencesDatabases($db1, $db2)
    {
        $matchedTables = [];

        $databaseDiffs = [
            'table-diffs' => [],
            'table-to-import' => [],
            'table-to-delete' => [],
        ];

        foreach ($db1['Tables'] as $table1) {
            foreach ($db2['Tables'] as $table2) {
                if ($table1['Name'] === $table2['Name']) {
                    $matchedTables[] = $table1['Name'];
                    $tableDifferences = self::appendDifferencesTables($table1, $table2);

                    if (count($tableDifferences) > 0) {
                        $databaseDiffs['table-diffs'][$table1['Name']] = $tableDifferences;
                    }
                }
            }
        }

        foreach ($db1['Tables'] as $table) {
            if (!in_array($table['Name'], $matchedTables)) {
                $databaseDiffs['table-to-import'][] = $table;
            }
        }

        foreach ($db2['Tables'] as $table) {
            if (!in_array($table['Name'], $matchedTables)) {
                $databaseDiffs['table-to-delete'][] = $table;
            }
        }

        return $databaseDiffs;
    }

    protected static function appendDifferencesTables($table1, $table2)
    {
        $matchedFields = [];
        $differences = [
            'field-diffs' => [],
            'field-to-import' => [],
            'field-to-delete' => [],
        ];

        foreach ($table1['Fields'] as $field1) {
            foreach ($table2['Fields'] as $field2) {
                if ($field1['Id'] === $field2['Id']) {
                    $matchedFields[] = $field1['Id'];
                    $fieldDifferences = self::appendDifferencesFields($field1, $field2);

                    if (count($fieldDifferences) > 0) {
                        $differences['field-diffs'][$field1['Id']] = $fieldDifferences;
                    }
                }
            }
        }

        foreach ($table1['Fields'] as $field) {
            if (!in_array($field['Id'], $matchedFields)) {
                $differences['field-to-import'][] = $field;
            }
        }

        foreach ($table2['Fields'] as $field) {
            if (!in_array($field['Id'], $matchedFields)) {
                $differences['field-to-delete'][] = $field;
            }
        }

        $differences = array_filter($differences);
        return $differences;
    }

    protected static function appendDifferencesFields($field1, $field2)
    {
        $differences = [];

        if ($field1 !== $field2) {
            foreach ($field1 as $k => $v) {
                if ($field1[$k] !== $field2[$k]) {
                    $differences[$k] = ['from' => $field1[$k], 'to' => $field2[$k]];
                }
            }
        }

        return $differences;
    }

    protected function configure()
    {
        $this->setName('snapshot:compare')
            ->setDescription('Compares two snapshots and outputs the differences.')
            ->addArgument('source', InputArgument::REQUIRED, 'Path to the source DB snapshot (JSON file).')
            ->addArgument('destination', InputArgument::REQUIRED, 'Path to the destination DB snapshot (JSON file)');
    }
}
