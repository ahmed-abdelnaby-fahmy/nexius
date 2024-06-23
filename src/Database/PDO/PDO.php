<?php

namespace Nexius\Database\PDO;

use Nexius\Database\Connection;

class PDO implements Connection
{

    private QueryBuilder $db;
    protected $connection;

    public function __construct()
    {
        $this->db = new QueryBuilder(['type' => 'mysql', 'host' => env('MYSQL_DB_HOST'), 'username' => env('MYSQL_DB_USERNAME'), 'password' => env('MYSQL_DB_PASSWORD'), 'dbname' => env('MYSQL_DB_NAME'), 'port' => env('MYSQL_DB_PORT'), 'prefix' => '', 'charset' => 'utf8']);
        if (!$this->db)
            throw new \Exception('Connection failed');
    }

    public function db($name = null): QueryBuilder
    {
        if (!$name)
            $this->db = new QueryBuilder(['type' => 'mysql', 'host' => env('MYSQL_DB_HOST'), 'username' => env('MYSQL_DB_USERNAME'), 'password' => env('MYSQL_DB_PASSWORD'), 'dbname' => $name, 'port' => env('MYSQL_DB_PORT'), 'prefix' => '', 'charset' => 'utf8']);
        return $this->db;
    }

    public function getName()
    {
        return 'PDO';
    }


}
