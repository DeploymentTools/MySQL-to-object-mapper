<?php
namespace MySQLExtractor\Extractor;

use MySQLExtractor\Common\Collection;

class Tables
{
    protected $tableContentsLines;
    protected $elementFragments = array();
    protected static $patterns = array();

    protected $currentChar = null;
    protected $previousChar = null;
    protected $parenthesisLevel = 0;
    protected $inSingleQuote = false;
    protected $inDoubleQuote = false;
    protected $inLineComment = false;
    protected $inMultiLineComment = false;
    protected $listenForTables = false;
    protected $tables;

    public function __construct()
    {
        $this->fieldsExtractor = new Fields();
    }

    /**
     * Resets the object and stores the input multi-line string.
     *
     * @param $stringContents multi-line string
     * @return $this
     */
    public function from($stringContents)
    {
        $this->reset();
        $this->tables = new Collection();
        $this->tableContentsLines = explode("\n", addslashes($stringContents) . "\n;");
        return $this;
    }

    public function get()
    {
        $tableElements = $this->extractElementFragments();

        if (!empty($tableElements)) {
            foreach ($tableElements as $tableContents) {
                $table = $this->fieldsExtractor->from($tableContents)->getTable();
                if ($table) {
                    $this->tables->add($table);
                }
            }
        }

        return $this->tables->toArray();
    }

    protected function inQuote()
    {
        return ($this->inDoubleQuote || $this->inSingleQuote);
    }

    protected function inComment()
    {
        return ($this->inMultiLineComment || $this->inLineComment);
    }

    protected function checkInComment()
    {
        if (!$this->inQuote()) {
            if ($this->inMultiLineComment && ($this->currentChar == '/') && ($this->previousChar == '*')) {
                $this->inMultiLineComment = false;
            }
            if (!$this->inMultiLineComment && ($this->currentChar == '*') && ($this->previousChar == '/')) {
                $this->inMultiLineComment = true;
            }
            if (!$this->inLineComment && ($this->currentChar == '-') && ($this->previousChar == '-')) {
                $this->inLineComment = true;
            }
        }
    }

    protected function checkQuotes()
    {
        if (!$this->inComment() && ($this->previousChar != '\\')) {
            if ($this->currentChar == "'") {
                $this->inSingleQuote = !$this->inSingleQuote;

            } else if ($this->currentChar == '"') {
                $this->inDoubleQuote = !$this->inDoubleQuote;
            }
        }
    }

    protected function extractElementFragments()
    {
        $tablesRaw = array();
        $tablesRawCursor = 0;
        $this->previousChar = null;

        foreach ($this->tableContentsLines as $key => $line) {
            $this->inLineComment = false;

            if (!$this->listenForTables && (strpos(trim(strtoupper($line)), 'CREATE TABLE') === 0)) {
                $this->listenForTables = true;
            }

            if ($this->listenForTables) {
                // loop through line contents
                for ($i = 0; $i < strlen($line); $i++) {
                    $this->currentChar = $line[$i];

                    $this->checkInComment();
                    $this->checkQuotes();

                    if (!$this->inComment() && (($this->currentChar != ';') && !$this->inQuote())) {
                        if (!isset($tablesRaw[$tablesRawCursor])) {
                            $tablesRaw[$tablesRawCursor] = '';
                        }
                        $tablesRaw[$tablesRawCursor] .= $this->currentChar;

                    } else {
                        if ($this->currentChar == ';') {
                            $tablesRawCursor++;
                        }
                    }

                    $this->previousChar = $this->currentChar;
                }
            }
        }

        return $tablesRaw;
    }

    protected function reset()
    {
        $this->tableContentsLines = null;
        $this->elementFragments = array();
        $this->parenthesisLevel = 0;
        $this->listenForTables = false;
        $this->inSingleQuote = false;
        $this->inDoubleQuote = false;
        $this->currentChar = null;
        $this->previousChar = null;
        $this->inLineComment = false;
        $this->inMultiLineComment = false;
    }
}
