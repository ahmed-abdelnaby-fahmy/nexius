<?php

namespace Nexius\Database;

use App\Lib\Hash;
use Carbon\Carbon;
use DateTime;
use JsonSerializable;

abstract class Model implements JsonSerializable
{

    protected $builder;

    protected static string $collection;

    protected array $find;

    protected array $operators;

    public string $path = '';

    private $connection;

    private $db;

    public $attributes = [];

    protected $hidden = [];
    protected $casts = [];
    protected $timestamp = true;


    public function __construct($attributes = [])
    {
        if (isset($attributes[0]) && is_array($attributes[0]) && count($attributes[0]) > 2)
            dd($attributes);
        $this->setAttributes($attributes);
    }

    public static function on($driver): static
    {
        $model = new static();
        $model->connection = $driver;
        return $model;
    }

    public static function db($name): static
    {
        $model = new static();
        $model->db = $name;
        return $model;
    }

    public function getDB()
    {
        return $this->db ?? app()->get(Config::class)->getDbName();
    }

    public function getConnection()
    {
        return $this->connection ?? env('DB_DRIVER', 'mongodb');
    }

    protected static function newInstance($attributes = []): Model
    {
        $model = new static();
        $model->builder = $model->newQueryBuilder();
        $model->setAttributes($attributes);
        return $model;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    protected function newQueryBuilder()
    {
        return $this->builder ?? (new QueryBuilder($this->getConnection()))->setModel($this);
    }

    public function collection()
    {
        return new static();
    }


    public function model()
    {
        $words = preg_split('/(?=[A-Z])/', class_basename(get_called_class()), -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) === 1) {
            $model = strtolower($words[0]);
        } else {
            $model = strtolower(implode('_', $words));
        }
        return $model;
    }

    public static function __callStatic($method, $arguments)
    {
        return static::newInstance()->builder->{$method}(...$arguments);
    }

    public function __call($method, $arguments)
    {
        $this->builder = $this->newQueryBuilder();
        return $this->builder->{$method}(...$arguments);
    }


    public function jsonSerialize($option = true): array
    {
        $data = [];
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->hidden) || !$option)
                $data[$key] = $value;
        }
        return $data;
    }

    public function toArray($option = true): array
    {
        return $this->jsonSerialize($option);
    }

    public function casts()
    {
        foreach ($this->attributes as $key => $value) {

            $cast = array_key_exists($key, $this->casts) ? $this->casts[$key] : $key;
            if ($this->getTimestamp() && in_array($key, ['created_at', 'updated_at']))
                $this->attributes[$key] = Carbon::parse($value)->format('Y-m-d H:m:s') ?? null;
            else
                $this->attributes[$key] = match ($cast) {
                    'datetime' => Carbon::parse($value)->format('Y-m-d H:i:s'),
                    'hashed' => Hash::make($value),
                    default => $this->$key,
                };

        }
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value ?? null;
    }

    public function __get(string $key)
    {
        return $this->attributes[$key];
    }


    public function setAttributes($attributes): void
    {
        $this->attributes = [];
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }
}