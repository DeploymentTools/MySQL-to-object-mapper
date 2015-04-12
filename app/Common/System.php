<?php
namespace MySQLExtractor\Common;

class System
{
    /**
     * Helper for calling PHP's internal methods
     * @var \Mockery
     */
    protected static $mock;

    /**
     * Calls the internal method if the TESTING const is not defined or the mock is not set.
     *
     * @param $methodName
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($methodName, $args = array())
    {
        if (!is_null(self::$mock)) {
            return call_user_func_array(array(self::$mock, $methodName), $args);
        }
        return call_user_func_array($methodName, $args);
    }

    public static function getDirectoryIterator($source)
    {
        if (!is_null(self::$mock)) {
            return call_user_func_array(array(self::$mock, 'getDirectoryIterator'), array($source));
        }
        return new \DirectoryIterator($source);
    }
}
