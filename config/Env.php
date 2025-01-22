<?php

namespace Config;

class Env
{
    private static $isLoaded = false;
    private static $variables = [];

    public static function load($filePath)
    {
        if (self::$isLoaded) {
            return;
        }

        if (!file_exists($filePath)) {
            throw new \Exception(".env file not found at: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = preg_replace('/^["\'](.*)["\']$/', '$1', $value);
            self::$variables[$key] = $value;
        }

        self::$isLoaded = true;
    }
    
    public static function get($key, $default = null)
    {
        return self::$variables[$key] ?? $default;
    }
}