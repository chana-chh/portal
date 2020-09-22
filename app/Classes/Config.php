<?php

namespace App\Classes;

use \Exception;

final class Config
{
    private static $instance = null;
    private static $container;
    private static $config = [];

    public static function instance($container)
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($container);
        }
        return static::$instance;
    }

    private function __construct($container)
    {
        static::$container = $container;
        static::$config = $container['settings']['chasha_app_settings'];
    }

    private function __clone()
    {
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }

    public static function getContainer($instance_name = null)
    {
        if ($instance_name === null) {
            return static::$container;
        }
        if (isset(static::$container[$instance_name])) {
            return static::$container[$instance_name];
        }
        throw new Exception("U kontejneru ne postoji instanca {$instance_name}");
    }

    public static function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return static::$config;
        }
        if (!is_string($key) || empty($key)) {
            throw new Exception("Naziv konfiguracije nije ispravan");
        }
        $data = static::$config;
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            foreach ($keys as $k) {
                if (!isset($data[$k])) {
                    return $default;
                }
                if (!is_array($data)) {
                    return $default;
                }
                $data = $data[$k];
            }
        } else {
            return isset($data[$key]) ? $data[$key] : $default;
        }
        return $data === null ? $default : $data;
    }
}
