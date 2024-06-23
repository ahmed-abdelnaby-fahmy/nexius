<?php

namespace Nexius\Database\PDO;


use Nexius\Database\ConnectionAbstract;
use  Nexius\Database\MongoDriver\MongoDB;

class Connection extends ConnectionAbstract
{

    public function db(): \Nexius\Database\Connection
    {
        return PDO::connection();
    }
}
