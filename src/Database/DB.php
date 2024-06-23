<?php

namespace Nexius\Database;

use Nexius\Database\MongoDriver\MongoDB;
use Nexius\Database\PDO\PDO;
use Nexius\Database\PDO\QueryBuilder;

class DB
{
    static private $instance = null;
    protected $connection;
    protected $builder;

    private final function __construct()
    {

    }

    public static function connection(): Connection
    {
        return self::$instance->connection;
    }

    public static function on($driver): DB
    {
        if (!self::$instance) {
            $db = new DB();
            $db->connection = match ($driver) {
                'pdo' => new PDO(),
                'mongodb' => new MongoDB(),
            };

            self::$instance = $db;
        }
        return self::$instance;
    }

}