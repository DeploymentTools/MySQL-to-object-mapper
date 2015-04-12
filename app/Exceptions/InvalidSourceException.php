<?php
namespace MySQLExtractor\Exceptions;

class InvalidSourceException extends \Exception
{
    public function __construct($message)
    {
        return parent::__construct($message, 1002);
    }
}
