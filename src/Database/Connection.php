<?php

namespace Nexius\Database;

interface Connection
{
    public function getName();

    public function db($name = null);
}
