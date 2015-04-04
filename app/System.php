<?php
namespace MySQLExtractor;

class System {
    /**
     * Helper for calling PHP's internal methods
     * @var \Mockery
     */
    protected static $mock;

    /**
     * @param $methodName
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($methodName, $args = array())
    {
        if (defined('TESTING')) {
            return call_user_func_array(array(self::$mock, $methodName), $args);
        }
        return call_user_func_array($methodName, $args);
    }
}
