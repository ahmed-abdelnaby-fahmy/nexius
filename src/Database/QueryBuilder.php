<?php

namespace Nexius\Database;


class QueryBuilder
{
    protected $builder;

    public function __construct(protected string $driver)
    {
    }

    public function setModel($model = null)
    {
        return $this->builder = match ($this->driver) {
            'mysql' =>  new \Nexius\Database\PDO\QueryBuilder(['type' => 'mysql', 'host' => env('MYSQL_DB_HOST'), 'username' => env('MYSQL_DB_USERNAME'), 'password' => env('MYSQL_DB_PASSWORD'), 'dbname' => env('MYSQL_DB_NAME'), 'port' => env('MYSQL_DB_PORT'), 'prefix' => '', 'charset' => 'utf8']),
            'mongodb' => new \Nexius\Database\MongoDriver\QueryBuilder($model),
        };

    }
}