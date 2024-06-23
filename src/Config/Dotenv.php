<?php

namespace Nexius\Config;

use Exception;

class Dotenv
{
    protected array $data = [];
    protected array $requiredKeys = ['MONGODB_URI', 'APP_DEBUG'];

    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('.env file not found: ' . $filePath);
        }
        $this->load($filePath);
        $this->checkRequiredKeys();
    }

    private function load($filePath)
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, ';') === 0) {
                continue; // Skip comments
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $value = trim($value, " \t\n\r\v\"'"); // Trim whitespace and quotes
                $this->data[trim($key)] = $value;
            }
        }
    }


    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    private function checkRequiredKeys()
    {
        $missingKeys = [];
        foreach ($this->requiredKeys as $param) {
            if (!$this->has($param)) {
                $missingKeys[] = $param;
            }
        }

        if (!empty($missingKeys)) {
            throw new Exception('Required parameters not found in .env file: ' . implode(', ', $missingKeys));
        }
    }
}
