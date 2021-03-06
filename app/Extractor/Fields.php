<?php
namespace MySQLExtractor\Extractor;
use MySQLExtractor\Presentation\Field;
use MySQLExtractor\Presentation\Key;
use MySQLExtractor\Presentation\PrimaryKey;

class Fields
{
    protected $tableObject; // target
    protected $stringContents;
    protected $elementFragments = array();
    protected static $patterns = array(
        'table' => '/CREATE\sTABLE\s(IF NOT EXISTS)?\s?`?([\w]+)`?/',
        'primaryKey' => '/PRIMARY\sKEY\s\(`([\w]+)`\)/',
        'key' => '/KEY\s`([\w\_]+)`\s?\((.*)\)/',
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
        if (!empty($fieldsFragments)) {
            foreach ($fieldsFragments as $fieldFragmentString) {
                $this->fieldExtractor($fieldFragmentString);
            }
            return $this->tableObject;
        }
        return false;
    }

    protected function fieldExtractor($fieldString)
    {
        $fieldString = trim($fieldString);

        if ($primaryKey = self::getPrimaryKeyFromString($fieldString)) {
            $this->tableObject->Keys[] = $primaryKey;
            return true;
        }

        if ($key = self::getKeyFromString($fieldString)) {
            $this->tableObject->Keys[] = $key;
            return true;
        }

        if ($field = self::getFieldFromString($fieldString)) {
            $this->tableObject->Fields[] = $field;
            return true;
        }

        return false;
    }

    public static function getPrimaryKeyFromString($fieldString)
    {
        $pattern = self::$patterns['primaryKey'];
        preg_match($pattern, $fieldString, $matches);
        if ($matches) {
            $key = new PrimaryKey;
            $key->Column = $matches[1];
            return $key;
        }
    }

    public static function getKeyFromString($fieldString)
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

    public static function extractFieldType($fieldString, $field)
    {
        $lengthValuePattern = self::$patterns['lengthValue'];
        preg_match($lengthValuePattern, $fieldString, $matchesType);
        if ($matchesType) {
            $field->Type = strtoupper($matchesType[2]);
            if (isset($matchesType[3]) && !empty($matchesType[3])) {
                $field->Length = (int)$matchesType[3];
            }

            if ($field->Type == 'ENUM') {
                self::extractFieldEnumValues($fieldString, $field);
            }
        }
    }

    public static function extractFieldEnumValues($fieldString, $field)
    {
        preg_match('/^`([\w\_]+)`\sENUM\(/i', $fieldString, $matches);
        if ($matches) {
            $followingString = trim(substr($fieldString, strlen($matches[0])));
            $sep = substr($followingString, 0, 1);
            $sepQuote = preg_quote($sep);

            $patternSeparators = '/' . $sepQuote . '([\w\_\-\,\s\'\"]+)' . $sepQuote . '\)/';
            preg_match($patternSeparators, $followingString, $matches);

            if ($matches) {
                $pattern = '/' . $sepQuote . '([\s\,]+)' . $sepQuote .'/';
                $separator = $sep.','.$sep;
                $matchingRaw = preg_replace($pattern, $separator, $matches[1]);
                $field->Values = explode($separator, $matchingRaw);
            }
        }
    }

    public static function extractFieldDefault($fieldString, $field)
    {
        $stringUtils = new String(stripslashes($fieldString));
        $field->Default = String::getFirstChunk($stringUtils->substr('/DEFAULT\s/'));

        if ($field->Type === 'INT') {
            $field->Default = (int)$field->Default;
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

    public static function initField($fieldString)
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

    public static function getFieldFromString($fieldString)
    {
        $field = self::initField($fieldString);
        if ($field) {
            self::extractFieldType($fieldString, $field);
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
