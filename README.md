[![Build Status](https://travis-ci.org/DeploymentTools/MySQL-to-object-mapper.svg)](https://travis-ci.org/DeploymentTools/MySQL-to-object-mapper) [![Code Climate](https://codeclimate.com/github/DeploymentTools/MySQL-to-object-mapper/badges/gpa.svg)](https://codeclimate.com/github/DeploymentTools/MySQL-to-object-mapper) [![Coverage Status](https://coveralls.io/repos/DeploymentTools/MySQL-to-object-mapper/badge.svg)](https://coveralls.io/r/DeploymentTools/MySQL-to-object-mapper)

Scans MySQL structure and converts it into PHP objects.
Using MySQL file dumps or a server as input.

Download the `phar` file from the [latest release page](https://github.com/DeploymentTools/MySQL-to-object-mapper/releases/latest).

### Commands:

```bash
MySQL Extractor version 1.0.0

Usage:
  command [options] [arguments]

Available commands:
 snapshot
  snapshot:compare  Compares two snapshots and outputs the differences.
  snapshot:create   Scans a DB and outputs the structure.
 sync
  sync:compare      Compare two server DB instances and outputs their differences.
``` 

#### Snapshot create command:

```bash
Usage:
  snapshot:create <source> <output>

Arguments:
  source  DB instance to be analysed. Format: USER:PASS@HOSTNAME[:PORT]/DATABASE or /PATH/TO/SQL/DUMPS/
  output  Output folder for the results file(s), where the database structure will be dumped in JSON format. Data will be stored in [databaseName]-[date]-[timestamp].json format.


Help:
 Scans a DB and outputs the structure.
```

Sample output:

```json
{
    "Name": "redmine",
    "Tables": [
        {
            "Name": "attachments",
            "Fields": [
                {
                    "Id": "id",
                    "Type": "INT",
                    "Length": 11,
                    "Null": false,
                    "Default": 0,
                    "Comment": null,
                    "Autoincrement": true,
                    "Values": []
                },
                {
                    ...
                }
            ],
            "Keys": [
                {
                    "Column": "id"
                },
                {
                    ...
                },
                {
                    "Label": "index_attachments_on_container_id_and_container_type",
                    "Columns": [
                        "container_id",
                        "container_type"
                    ]
                }
            ]
        },
        {
            "Name": "auth_sources",
            "Fields": [
                ...
            ]
        }
    ]
}
```


#### Snapshot compare command:

```bash
Usage:
  snapshot:compare <source> <destination>

Arguments:
  source       Path to the source DB snapshot (JSON file).
  destination  Path to the destination DB snapshot (JSON file)


Help:
 Compares two snapshots and outputs the differences.
```

Sample output:

```json
{
    "table-diffs": {
        "attachments": {
            "field-diffs": {
                "container_type": {
                    "Type": {
                        "from": "VARCHAR",
                        "to": "INT"
                    }
                },
                "disk_filename": {
                    "Length": {
                        "from": 255,
                        "to": 500
                    },
                    "Null": {
                        "from": false,
                        "to": true
                    }
                }
            },
            "field-to-import": [
                {
                    "Id": "container_id",
                    "Type": "INT",
                    "Length": 11,
                    "Null": true,
                    "Default": 0,
                    "Comment": null,
                    "Autoincrement": false,
                    "Values": []
                }
            ],
            "field-to-delete": [
            ]
        }
    },
    "table-to-import": [],
    "table-to-delete": []
}
```


#### Sync compare command:

```bash
Usage:
  sync:compare <source> <destination>

Arguments:
  source       Source server (main / trusted). Format: USER:PASS@HOSTNAME[:PORT]/DATABASE
  destination  Destination server. Format: USER:PASS@HOSTNAME[:PORT]/DATABASE


Help:
 Compare two server DB instances and outputs their differences.
```

The output is the same as with the Snapshot compare command. 