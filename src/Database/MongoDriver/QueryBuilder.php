<?php

namespace Nexius\Database\MongoDriver;

use Carbon\Carbon;
use MongoDB\Collection;
use Nexius\Database\Builder;
use Nexius\Database\Config;
use Nexius\Database\DB;
use Nexius\Database\Exceptions\FilterNotSpecifiedException;
use Nexius\Database\Exceptions\InvalidOperatorException;
use Nexius\Validator\Validator;

class QueryBuilder
{
    protected array $op;
    public $db;
    protected $filter = [];
    protected $distinct;
    protected $model;
    protected $collection;

    public function __construct($model)
    {
        $this->op = [
            '=' => '$eq',                // Equality Operator
            '!=' => '$ne',               // Not Equal Operator
            '>' => '$gt',                // Greater Than Operator
            '>=' => '$gte',              // Greater Than or Equal Operator
            '<' => '$lt',                // Less Than Operator
            '<=' => '$lte',              // Less Than or Equal Operator
            'IN' => '$in',               // In Operator
            'NOT IN' => '$nin',          // Not In Operator
            'IS NOT NULL' => '$exists',  // Exists Operator
            '$type' => '$type',
            '$or' => '$or',
            '$and' => '$and',
            'LIKE' => '$regex',          // Regex Operator
        ];;
        $this->model = $model;
        $this->configure();
    }

    protected function configure()
    {
        $driver = $this->model ? $this->model->getConnection() : app()->get(Config::class)->getDriver();
        $connection = DB::on($driver)->connection();
        if ($this->model) {
            $this->collection = $this->model->collection()->model();
            $this->db = $connection->db($this->model->getDB());
            if (count($this->model->attributes)) {
                $this->filter['id'] = $this->model->attributes['id'];
                dd($this->filter);
            }
        }
    }

    public function onCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    public function rename($collection)
    {
        return $this->db->{$this->collection}->rename($collection);
    }

    public function create(array $attributes = [])
    {
        $data = $this->applyDataOperation($attributes);
        return !empty($this->model->path) ? $this->update($data) : $this->db->{$this->collection}->insertOne($data);
    }

    public function createMany($data)
    {
        $data = array_map(function ($attributes) {
            return $this->applyDataOperation($attributes);
        }, $data);
        return $this->db->{$this->collection}->insertMany($data);
    }

    public function update($data, $path = null)
    {
        return $this->applyUpdateOperation($data, '$set', $path);
    }

    public function push($data, $path = null)
    {
        return $this->applyUpdateOperation($data, '$push', $path);
    }

    protected function applyUpdateOperation(array $data, string $operation, ?string $path = null)
    {
        if (empty($this->filter)) {
            throw new FilterNotSpecifiedException('Filter must be specified');
        }
        $path = $path ? $this->model->path . '.' . $path : $this->model->path;
        return $this->db->{$this->collection}->updateMany($this->filter(), [$operation => [$path => $data]]);
    }

    protected function applyDataOperation($attributes)
    {
        $this->model->setAttributes($attributes);
        $this->model->casts();
        $data = $this->model->toArray(false);
        $data = $this->model->getTimestamp() ? array_merge($data, [
            'created_at' => Carbon::now()->toISOString(),
            'updated_at' => Carbon::now()->toISOString(),
        ]) : $data;
        $data['_id'] = ulid();
        return $data;
    }

    public function updateOne($data, $path = null)
    {
        if (!count($this->filter()))
            throw new \Exception('Filter must be specified');
        $path = !empty($path) ? $this->model->path . '.' . $path : $this->model->path;
        $operation = !$path ? ['$set' => $data] : ['$set' => [$path => $data]];
        return $this->db->{$this->collection}->updateOne($this->filter(), $operation);
    }

    public function save()
    {
        if (!count($this->filter()))
            throw new \Exception('Filter must be specified');
        return $this->db->{$this->collection}->updateOne($this->filter(), ['$set' => [$this->model->path => $this->model->attributes]]);
    }

    public function where($key, $operator, $value = null)
    {
        if (isset($value) && !isset($this->op[$operator])) {
            throw new InvalidOperatorException("Invalid operator: $operator");
        }
        $v = empty($value) ? $operator : $value;
        $operator = empty($value) ? '=' : $operator;
        $operator = $this->op[$operator];
        $this->filter[$key] = [$operator => $v];
        return $this;
    }

    public function whereCollection($key, $operator, $value = null)
    {
        $v = empty($value) ? $operator : $value;
        $operator = empty($value) ? '=' : $operator;
        $this->filter[$key] = [$operator => $v];
        return $this;
    }

    public function whereNotIn($key, $value)
    {
        $this->filter[$key] = ['$nin' => $value];
        return $this;
    }

    public function whereIn($key, $value)
    {
        $this->filter[$key] = ['$in' => $value];
        return $this;
    }

    public function whereBetween($key, array $dates)
    {
        $this->filter[$key] =
            [
                $this->op['>='] => $dates[0],
                $this->op['<='] => $dates[1],
            ];
        return $this;
    }

    public function distinct($field)
    {
        $this->distinct = $field;
        return $this;
    }

    public function get($data = [])
    {
        if (!empty($this->distinct))
            $cursor = $this->db->{$this->collection}->distinct($this->distinct, $this->filter(), $data);
        else
            $cursor = $this->db->{$this->collection}->find($this->filter(), $data)->toArray();
        $results = [];
        foreach ($cursor as $document) {
            // Clone the model to not overwrite attributes on each iteration
            $modelClone = clone $this->model;
            $modelClone->setAttributes(json_decode(json_encode($document), true));
            $results[] = $modelClone;
        }
        return $results;
    }

    public function count()
    {
        return $this->db->{$this->collection}->countDocuments($this->filter());
    }

    public function first()
    {
        $document = $this->db->{$this->collection}->findOne($this->filter());
        if ($document) {
            $this->model->setAttributes(json_decode(json_encode($document), true));
            return $this->model;
        }
        return null;
    }

    public function find($value, $field = '_id')
    {
        $this->where($field, $value);
        $document = $this->db->{$this->collection}->findOne($this->filter());
        if ($document) {
            $this->model->setAttributes(json_decode(json_encode($document), true));
            return $this->model;
        }
        return null;
    }

    public function deleteOne()
    {
        return $this->db->{$this->collection}->deleteOne($this->filter());
    }

    public function deleteMany()
    {
        return $this->db->{$this->collection}->deleteMany($this->filter());
    }

    public function raw(callable $callback)
    {
        return $callback($this->db);
    }

    public function drop()
    {
        return $this->db->{$this->collection}->drop();
    }

    public function createIndex($data)
    {
        return $this->db->{$this->collection}->createIndex($data);
    }

    public function dropIndex($index = '*')
    {
        return $this->db->{$this->collection}->dropIndex($index);
    }

    public function getCollectionNames()
    {
        return $this->db->listCollections();
    }

    public function aggregate()
    {
        $path = $this->model->path;
        if (empty($path))
            throw new \Exception('No path provided for model ' . $this->model->model());
        $pipeline = (new Aggregate())->match($this->filter())->project($path, $this->condition())->getPipeline();
        return $this->db->{$this->collection}->aggregate($pipeline)->toArray();
    }

    private function filter()
    {
        $filter = [];
        foreach ($this->filter['col'] ?? [] as $item) {
            $op = in_array($item[1], $this->op) ? $this->op[$item[1]] : '$eq';
            $filter[$item[0]] = [$op => $item[2]];
        }
        foreach ($this->filter ?? [] as $key => $item) {
            $path = $this->model->path;
            $key = (!empty($path) ? $path . '.' : '') . $key;
            $filter[$key] = $item;
        }
        return $filter;
    }

    private function condition()
    {
        $condition = [];
        foreach ($this->filter['in'] as $item) {
            $op = in_array($item[1], $this->op) ? $this->op[$item[1]] : '$eq';
            $condition[] = [$op => ['$$item.' . $item[0], $item[2]]];
        }
        return $condition;
    }
}