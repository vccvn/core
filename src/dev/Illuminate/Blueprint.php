<?php

namespace Illuminate\Database\Schema;

class Blueprint
{
    public $data = [];

    public function __construct()
    {
        # code...
    }

    public function getData()
    {
        return $this->data;
    }

    public function getColumns()
    {
        return array_keys($this->data);
    }

    public function __call($name, $params)
    {
        if (isset($params[0]) && $params[0] && !in_array($name, ['increment', 'bigIncrements', 'foreign'])) {
            if ($name == 'decimal') $name = 'float';
            elseif ($name == 'json') $name = 'array';
            elseif ($name == 'text' || $name == 'longText' || $name == 'tinyText' || $name == 'uuid' || $name == 'timestamp' || $name == 'date' || $name == 'datetime' || $name == 'time') $name = 'string';
            elseif ($name == 'bigInteger' || $name == 'tinyInteger') $name = 'integer';
            
            $this->data[$params[0]] = $name;
        }

        return (new static());
    }
    public function __toString()
    {

        return "[\n    '" . implode("',\n    '", $this->data) . "'\n]";
    }
}
