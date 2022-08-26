<?php

/**
 * @author DoanLN
 * @copyright 2018-2019
 */

namespace Gomee\Repositories;

use BadMethodCallException;
use Gomee\Models\Model;
use Gomee\Models\MongoModel;
use Gomee\Models\SQLModel;
use Gomee\Services\Traits\Events;
use Gomee\Services\Traits\MagicMethods;

/**
 * danh sách method
 * @method static select(...$columns) thêm các cột cần select
 * @method static selectRaw($string) select dạng nguyen bản
 * @method static from($table) 
 * @method static fromRaw($string)
 * @method static join(string $table, string $tableColumn, string $operator = '=', string $leftTableColumn) join vs 1 bang khac
 * @method static leftJoin($table, $tableColumn, $operator, $leftTableColumn)
 * @method static crossJoin($_ = null)
 * @method static where($_ = null)
 * @method static whereRaw($_ = null)
 * @method static whereIn($column, $values = [])
 * @method static whereNotIn($column, $values = [])
 * @method static whereBetween($column, $values = [])
 * @method static whereNotBetween($column, $values = [])
 * @method static whereDay($_ = null)
 * @method static whereMonth($_ = null)
 * @method static whereYear($_ = null)
 * @method static whereDate($_ = null)
 * @method static whereTime($_ = null)
 * @method static whereColumn($_ = null)
 * @method static whereNull($column)
 * @method static whereNotNull($column)
 * @method static orWhere($_ = null)
 * @method static orWhereRaw($_ = null)
 * @method static orWhereIn($column, $values = [])
 * @method static orWhereNotIn($column, $values = [])
 * @method static orWhereBetween($column, $values = [])
 * @method static orWhereNotBetween($column, $values = [])
 * @method static orWhereDay($_ = null)
 * @method static orWhereMonth($_ = null)
 * @method static orWhereYear($_ = null)
 * @method static orWhereDate($_ = null)
 * @method static orWhereTime($_ = null)
 * @method static orWhereColumn($leftColumn, $operator = '=', $rightColumn)
 * @method static orWhereNull($column)
 * @method static orWhereNotNull($column)
 * @method static groupBy($column)
 * @method static having($_ = null)
 * @method static havingRaw($_ = null)
 * @method static orderBy($_ = null)
 * @method static orderByRaw($_ = null)
 * @method static skip($_ = null)
 * @method static take($_ = null)
 * @method static with($_ = null)
 * @method static withCount($_ = null)
 * @method static load($_ = null)
 * @method static distinct($_ = null)
 */

abstract class BaseRepository
{
    use BaseQuery, GettingAction, CRUDAction, FilterAction, OwnerAction, FileAction, DataAction, CacheAction, Events, MagicMethods;

    // tự động kiểm tra owner
    protected $checkOwner = true;

    protected $_primaryKeyName = MODEL_PRIMARY_KEY;
    /**
     * @var Model|SQLModel|MongoModel
     */
    protected $_model;

    /**
     * @var Model|SQLModel|MongoModel
     */
    static $__Model__;

    protected $modelType = 'default';

    /**
     * EloquentRepository constructor.
     */
    public function __construct()
    {
        $this->setModel();
        $this->_primaryKeyName = $this->_model->getKeyName();
        // $this->ownerInit();
        if ($this->required == MODEL_PRIMARY_KEY && $this->_primaryKeyName) {
            $this->required = $this->_primaryKeyName;
        }
        $this->modelType = $this->_model->__getModelType__();
        $this->init();
        if (!$this->defaultValues) {
            $this->defaultValues = $this->_model->getDefaultValues();
        }
    }

    public function getKeyName()
    {
        return $this->_primaryKeyName;
    }




    /**
     * get model
     * @return string
     */
    abstract public function getModel();


    /**
     * chạy các lệnh thiết lập
     */
    protected function init()
    {
    }
    /**
     * Get one
     * @param int $id
     * @return \Gomee\Models\Model
     */
    public function find($id)
    {
        $result = $this->_model->find($id);
        return $result;
    }

    /**
     * tạo một repository mới
     *
     * @return BaseRepository
     */
    public function mewRepo()
    {
        return new static();
    }

    /**
     * kiểm tra tồn tại
     *
     * @param string|int|float ...$args
     * @return bool
     */
    public function exists(...$args)
    {
        $t = count($args);
        if ($t >= 2) {
            return $this->countBy(...$args) ? true : false;
        } elseif ($t == 1) {
            return $this->countBy($this->_primaryKeyName, $args[0]) ? true : false;
        }
        return false;
    }
    public static function checkExists($id)
    {
        return (new static())->exists($id);
    }



    /**
     * gọi hàm không dược khai báo từ trước
     *
     * @param string $method
     * @param array $params
     * @return static
     */
    public function __call($method, $params)
    {
        $f = array_key_exists($key = strtolower($method), $this->sqlclause) ? $this->sqlclause[$key] : null;
        if ($f) {
            if (!isset($this->actions) || !is_array($this->actions)) {
                $this->actions = [];
            }
            if ($f == 'groupby') {
                if (count($params) == 1 && is_string($params[0])) {
                    $params = array_map('trim', explode(',', $params[0]));
                }
                foreach ($params as $column) {
                    $this->actions[] = [
                        'method' => $method,
                        'params' => [$column]
                    ];
                }
            } else {
                $this->actions[] = compact('method', 'params');
            }

        } elseif (count($params)) {
            $value = $params[0];
            $fields = array_merge([$this->required], $this->getFields());

            // lấy theo tham số request (set where)
            if ($this->whereable && is_array($this->whereable) && (isset($this->whereable[$key]) || in_array($key, $this->whereable))) {
                if (isset($this->whereable[$key])) {
                    $this->where($this->whereable[$key], $value);
                } else {
                    $this->where($key, $value);
                }
            }
            // elseif($this->searchable && is_array($this->searchable) && (isset($this->searchable[$f]) || in_array($f, $this->searchable))){
            //     if(isset($this->searchable[$f])){
            //         $this->where($this->searchable[$f], $value);
            //     }else{
            //         $this->where($f, $value);
            //     }
            // }
            elseif (in_array($key, $fields)) {
                $this->where($key, $value);
                
            }
            elseif($this->_funcExists($method)){
                $this->_nonStaticCall($method, $params);
            }
            elseif (substr($method, 0, 2) == 'on' && strlen($event = substr($method, 2)) > 0 && ctype_upper(substr($event, 0, 1)) && count($params) && is_callable($params[0])) {
    
                $this->addEvent($event, $params[0]);
            }
        }elseif($this->_funcExists($method)){
            $this->_nonStaticCall($method, $params);
        }
        elseif (substr($method, 0, 2) == 'on' && strlen($event = substr($method, 2)) > 0 && ctype_upper(substr($event, 0, 1)) && count($params) && is_callable($params[0])) {

            $this->addEvent($event, $params[0]);
        }
        return $this;
    }
    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        return static::_staticCall($method, $parameters);
    }


}

BaseRepository::globalStaticFunc('on', '_on');
BaseRepository::globalFunc('on', 'addEvent');

