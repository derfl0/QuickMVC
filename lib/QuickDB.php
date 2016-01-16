<?php

/**
 * QuickDB
 */
class QuickDB extends PDO
{
    private static $instance;

    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new self('mysql:host=' . QuickConfig::DB_HOST
                . ';dbname=' . QuickConfig::DB_NAME
                . ';charset=utf8',
                QuickConfig::DB_NAME,
                QuickConfig::DB_PASSWORD);
        }
        return self::$instance;
    }
}