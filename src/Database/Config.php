<?php

namespace Nexius\Database;

class Config
{
    private $driver;
    private $db_name;

    public function getDriver()
    {
        return $this->driver ?? env('DB_DRIVER');
    }

    public function getDbName()
    {
        return $this->db_name ?? env('DB_NAME');
    }

    public function setDbName($db_name)
    {
        $this->db_name = $db_name;
    }
}