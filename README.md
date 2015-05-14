[![Build Status](https://travis-ci.org/DeploymentTools/MySQL-to-object-mapper.svg)](https://travis-ci.org/DeploymentTools/MySQL-to-object-mapper) [![Code Climate](https://codeclimate.com/github/DeploymentTools/MySQL-to-object-mapper/badges/gpa.svg)](https://codeclimate.com/github/bogdananton/MySQL-to-object-mapper) [![Coverage Status](https://coveralls.io/repos/DeploymentTools/MySQL-to-object-mapper/badge.svg)](https://coveralls.io/r/DeploymentTools/MySQL-to-object-mapper)

Scans MySQL structure and converts it into PHP objects.
Using MySQL file dumps or a server as input.

Download the `phar` file from the [latest release page](https://github.com/DeploymentTools/MySQL-to-object-mapper/releases/latest).

```
Usage: php m2om.phar [-d|-s] [input-source] -o [output-path]

Options:
  -d | --disk     /input-path/to/mysql-dumps/         Set a folder of SQL dumps or a text file.
  -s | --server   USER:PASS@HOSTNAME/DATABASE         Set a MySQL server as input.
  -o | --output   /output-path/                       Output folder for dumping extracted structure.


Examples:
       php m2om.phar -d /db-dumps-path/ -o /output-path/
       php m2om.phar -s testuser:1234@localhost/db_main -o /output-path/


Example:
       php m2om.phar -d /db-dumps-path/ -o /output-path/
```
