<?php

namespace Nexius\Validation\Rules;

use Nexius\Database\DB;
use Nexius\Validation\Rule;

class UniqueRule extends Rule
{
    protected $message = ":attribute :value has been used";

    protected $fillableParams = ['collection', 'field', 'except'];

    protected \MongoDB\Database $db;

    public function __construct()
    {
//        $query= (new QueryBuilder(app()->get(Config::class)->getDriver()))->setModel();
        $this->db = DB::connection()->db();
    }

    public function check($value): bool
    {
        // make sure required parameters exists
        $this->requireParameters(['collection', 'field']);

        // getting parameters
        $field = $this->parameter('field');
        $collection = $this->parameter('collection');
        $except = $this->parameter('except');

        if ($except and $except == $value) {
            return true;
        }
        // do query
        $data = $this->db->{$collection}->findOne([$field => $value]);
        // true for valid, false for invalid
        return !$data;
    }
}
