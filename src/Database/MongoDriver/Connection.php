<?php

namespace Nexius\Database\MongoDriver;


use Nexius\Database\ConnectionAbstract;
use  Nexius\Database\MongoDriver\MongoDB;
class Connection extends ConnectionAbstract
{

    public function db(): \Nexius\Database\Connection
    {
       return MongoDB::connection()->getDatabase();
    }
}
