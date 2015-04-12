<?php
namespace MySQLExtractor\Presentation;

class Field {
    public $Id;
    public $Type;
    public $Length;
    public $Null;
    public $Default;
    public $Comment;
    public $Autoincrement = false;

    /**
     * used for ENUM
     * @var string[]
     */
    public $Values = array();
}
