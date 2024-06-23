<?php

namespace Nexius\Database;

use MongoDB\Client;

abstract class ConnectionAbstract
{
    abstract public function db(): Connection;

}
