<?php

namespace Nexius\Database\MongoDriver;

use MongoDB\Client;
use Nexius\Database\Config;
use Nexius\Database\Connection;

class MongoDB implements Connection
{
    protected $connection;

    public function __construct()
    {
        $this->connection = new Client(env('MONGODB_URI'));
    }

    public function getName()
    {
        return 'mongodb';
    }

    public function db($name = null): \MongoDB\Database
    {
        $db_name = $name ?? app()->get(Config::class)->getDbName();
        return $this->connection->{$db_name};
    }

}
