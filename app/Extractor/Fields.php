<?php
namespace MySQLExtractor\Extractor;
use MySQLExtractor\Presentation\Field;
use MySQLExtractor\Presentation\Key;
use MySQLExtractor\Presentation\PrimaryKey;

class Fields {
    protected $tableObject; // target
    protected $stringContents;
    protected $elementFragments = array();
    protected static $patterns = array(
        'table' => '/CREATE\sTABLE\s(IF NOT EXISTS)?\s?`?([\w]+)`?/',
        'primaryKey' => '/PRIMARY\sKEY\s\(`([\w]+)`\)/',
        'key' => '/KEY\s`([\w\_]+)`\s?\((.*)\)/',
        'defaultValue' => '/DEFAULT\s\'(.*)\'/',
        'lengthValue' => '/^`([\w]+)`\s([\w]+)\(?([0-9]+)?\)?/',
        'fieldName' => '/^`([\w]+)`\s?(([\w]+)\(?([\d]+)?\)?)?/'
    );

    protected $currentChar = null;
    protected $parenthesisLevel = 0;
    protected $inSingleQuote = false;
    protected $inDoubleQuote = false;
    protected $listenForChars = false;

    /**
     * Resets the object and stores the input multi-line string.
     *
     * @param $stringContents multi-line string
     * @return $this
     */
    public function from($stringContents)
    {
        $this->reset();
        $this->stringContents = trim(str_replace("\n", " ", $stringContents));
        return $this;
    }

    protected function detectTableName()
    {
        preg_match(self::$patterns['table'], $this->stringContents, $matches);
        if ($matches) {
            $this->tableObject->Name = $matches[2];
            return true;
        }
        return false;
    }

    protected function extractElementFragments()
    {
        $elements = array();
        $currentCursor = 0;

        for ($i=0; $i < strlen($this->stringContents); $i++) {
            $this->currentChar = $this->stringContents[$i];

            $isOpeningParenthesis = ($this->currentChar == '(');
            $isClosingParenthesis = ($this->currentChar == ')');
            $isComma = ($this->currentChar == ',');

            // if is comma but in a KEY then ignore
            if ($isComma && $this->parenthesisLevel > 1) {
                $isComma = false;
            }

            if ($this->listenForChars) {
                if ($isOpeningParenthesis || $isClosingParenthesis) {
                    $this->parenthesisLevel += ($isOpeningParenthesis) ? 1 : -1;

                    // after all fields are extracted, quit
                    if ($isClosingParenthesis && $this->parenthesisLevel == 0) {
                        $this->listenForChars = false;
                        continue;
                    }
                }

                // field separator
                if ($isComma) {
                    $currentCursor++;
                    continue;
                }

                if (!isset($elements[$currentCursor])) {
                    $elements[$currentCursor] = "";
                }

                $elements[$currentCursor] .= $this->currentChar;

            } else if ($isOpeningParenthesis){
                $this->listenForChars = true;
                $this->parenthesisLevel++;
            }
        }

        return $elements;
    }

    protected function inQuote()
    {
        return ($this->inDoubleQuote || $this->inSingleQuote);
    }

    public function getTable()
    {
        if (!$this->detectTableName()) {
            return false;
        }

        $fieldsFragments = $this->extractElementFragments();
        if (empty($fieldsFragments)) {
            return false;
        }

        foreach ($fieldsFragments as $fieldFragmentString) {
            $this->fieldExtractor($fieldFragmentString);
        }

        return $this->tableObject;
    }

    protected function fieldExtractor($fieldString)
    {
        $fieldString = trim($fieldString);

        if ($primaryKey = self::extractPrimaryKeyFromString($fieldString)) {
            $this->tableObject->Keys[] = $primaryKey;
            return true;
        }

        if ($key = self::extractKeyFromString($fieldString)) {
            $this->tableObject->Keys[] = $key;
            return true;
        }

        if ($field = self::extractFieldFromString($fieldString)) {
            $this->tableObject->Fields[] = $field;
            return true;
        }

        return false;
    }

    /**
     * @param $fieldString
     * @return PrimaryKey
     */
    public static function extractPrimaryKeyFromString($fieldString)
    {
        $pattern = self::$patterns['primaryKey'];
        preg_match($pattern, $fieldString, $matches);
        if ($matches) {
            $key = new PrimaryKey;
            $key->Column = $matches[1];
            return $key;
        }
    }

    /**
     * @param $fieldString
     * @return Key
     */
    public static function extractKeyFromString($fieldString)
    {
        $pattern = self::$patterns['key'];
        preg_match($pattern, $fieldString, $matches);
        if ($matches) {
            $rawColumns = explode('`', $matches[2]);

            $Columns = array();
            foreach ($rawColumns as $rawColumn) {
                $rawColumn = trim($rawColumn);
                if (!empty($rawColumn) && ($rawColumn != ',')) {
                    $Columns[] = $rawColumn;
                }
            }

            if (!empty($Columns)) {
                $key = new Key;
                $key->Label = $matches[1];
                $key->Columns = $Columns;
                return $key;
            }
        }
    }

    public static function extractFieldLength($fieldString, $field)
    {
        $lengthValuePattern = self::$patterns['lengthValue'];
        preg_match($lengthValuePattern, $fieldString, $matchesType);
        if ($matchesType) {
            $field->Type = strtoupper($matchesType[2]);
            if (isset($matchesType[3]) && !empty($matchesType[3])) {
                $field->Length = (int)$matchesType[3];
            }
        }
    }

    public static function extractFieldDefault($fieldString, $field)
    {
        $defaultValuePattern = self::$patterns['defaultValue'];
        preg_match($defaultValuePattern, $fieldString, $matchesDefault);
        if ($matchesDefault) {
            if (strpos($matchesDefault[1], '\'')) {
                $matchesDefault[1] = substr($matchesDefault[1], 0, strpos($matchesDefault[1], '\''));
            }
            $field->Default = empty($matchesDefault[1]) ? "" : $matchesDefault[1];
        }
    }

    public static function extractFieldAutoincrement($fieldString, $field)
    {
        if (strpos(strtoupper($fieldString), 'AUTO_INCREMENT')) {
            $field->Autoincrement = true;
        }
    }

    public static function extractFieldNull($fieldString, $field)
    {
        $field->Null = !(strpos(strtoupper($fieldString), 'NOT NULL') > 0);
    }

    public static function detectFieldName($fieldString)
    {
        $fieldNamePattern = self::$patterns['fieldName'];
        preg_match($fieldNamePattern, $fieldString, $matches);
        if ($matches) {
            $field = new Field;
            $field->Id = $matches[1];
            return $field;
        }
        return false;
    }

    public static function extractFieldFromString($fieldString)
    {
        $field = self::detectFieldName($fieldString);
        if ($field) {
            self::extractFieldLength($fieldString, $field);
            self::extractFieldDefault($fieldString, $field);
            self::extractFieldAutoincrement($fieldString, $field);
            self::extractFieldNull($fieldString, $field);
            return $field;
        }
        return false;
    }

    protected function reset()
    {
        $this->stringContents = null;
        $this->elementFragments = array();
        $this->tableObject = new \MySQLExtractor\Presentation\Table();
        $this->parenthesisLevel = 0;
        $this->listenForChars = false;
        $this->inSingleQuote = false;
        $this->inDoubleQuote = false;
    }
}
