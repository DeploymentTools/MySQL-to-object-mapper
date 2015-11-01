<?php
namespace MySQLExtractor\Extractor;

class String
{
    protected $originalString;

    public function __construct($originalString)
    {
        $this->originalString = $originalString;
    }

    /**
     * @todo add scientific format (and other formats)
     *
     * @param $string
     * @return string
     */
    public static function getFirstChunk($string)
    {
        $firstChar = substr($string, 0, 1);
        $secondChar = substr($string, 1, 1);

        $match = function ($pattern, $string) {
            $result = null;
            preg_match($pattern, $string, $matches);
            if (is_array($matches) && array_key_exists(1, $matches)) {
                $result = $matches[1];
            }
            return $result;
        };

        $trimmedString = trim($string);

        $doesStringStartWithSequence = function ($string, $sequence) {
            $length = strlen($sequence);
            return strlen($string) >= $length && strtoupper(substr($string, 0, $length)) === $sequence;
        };

        switch (true) {
            case $doesStringStartWithSequence($trimmedString, 'NULL'):
                return null;
                break;

            case $doesStringStartWithSequence($trimmedString, 'CURRENT_TIMESTAMP'):
                return 'CURRENT_TIMESTAMP';
                break;

            case ($firstChar === '\''):
            case ($firstChar === '"'):
                $quote = $firstChar;
                $strlen = strlen($string);
                $buildString = '';

                for ($i = 1; $i < $strlen; $i++) {
//                    if ($string[$i] === $quote && $string[$i-1] !== '\\') {
//                        return $buildString;
//                    }

                    $check = $quote . $buildString . $string[$i];

                    if (self::areQuotesBalanced($check)) {
                        return $buildString;
                    }

                    $buildString .= $string[$i];
                }
                break;

            case ($firstChar === '-' && is_numeric($secondChar)):
            case (is_numeric($firstChar)):
                $patterns = [
                    'decimals' => '/([\d]+\.[\d]+)\s/',
                    'integers' => '/([\d]+)\s/',
                ];

                foreach ($patterns as $pattern) {
                    if ($result = $match($pattern, $string)) {
                        return ($firstChar === '-') ? '-' . $result : $result;
                    }
                }

                break;
        }
    }

    /**
     * @param string $startFrom
     * @param bool|false $isAllowedInQuotes
     *
     * @return null|string
     */
    public function substr($startFrom, $isAllowedInQuotes = false)
    {
        $targetString = $this->originalString;

        $remaining = self::substringFrom($startFrom, $targetString);
        $firstPart = substr($targetString, 0, strpos($targetString, $remaining));

        if ($isAllowedInQuotes || (!$isAllowedInQuotes && self::areQuotesBalanced($firstPart))) {
            return $remaining;
        }

        return null;
    }

    public static function areQuotesBalanced($string)
    {
        $singleQuoteLevel = 0;
        $doubleQuoteLevel = 0;
        $stringLength = strlen($string);

        for ($i = 0; $i < $stringLength; $i++) {
            $prevChar = ($i > 0) ? $string[$i-1] : null;
            $currentChar = $string[$i];

            if ($prevChar !== '\\') {
                $singleQuoteLevel += ($currentChar === '\'');
                $doubleQuoteLevel += ($currentChar === '"');
            }
        }

        return ($singleQuoteLevel % 2 === 0 && $doubleQuoteLevel % 2 === 0);
    }

    public static function substringFrom($startingDelimiter, $targetString)
    {
        $details = preg_split($startingDelimiter, $targetString, 0);

        if (is_array($details) && array_key_exists(1, $details)) {
            preg_match($startingDelimiter, $targetString, $matches);
            return substr($targetString, strlen($details[0] . $matches[0]));
        }
    }

    /**
     * @param string $pattern String under test.
     * @param string $paddingSeparator This is an optional padding separator, used as fallback.
     *
     * @return bool
     */
    public static function isRegex($pattern, $paddingSeparator = '/')
    {
        if (!is_string($pattern) && !is_numeric($pattern)) {
            throw new \InvalidArgumentException('isRegex should be a string or a number.');
        }

        if ($pattern === '') {
            return false;
        }

        if (@preg_match($pattern, null) !== false) {
            return true;
        }

        // fallback
        $pattern = $paddingSeparator . $pattern . $paddingSeparator;
        return (@preg_match($pattern, null) !== false);
    }
}
