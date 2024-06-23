<?php

namespace Nexius\Database\MongoDriver;

class Aggregate
{
    protected $pipeline = [];
    protected $filter = [];

    public function __construct()
    {
    }

    public function match($filter)
    {
        $this->pipeline[] = ['$match' => $filter];
        return $this;
    }

    public function project($path, $cond)
    {
        $this->pipeline[] = [
            '$project' => [
                'imei' => '$imei',
                $path => [
                    '$filter' => [
                        'input' => "$$path",
                        'as' => 'item',
                        'cond' => [
                            '$and' => $cond
                        ],
                    ],
                ],
            ]];
        return $this;

    }

    public function getPipeline()
    {
        return $this->pipeline;
    }
}