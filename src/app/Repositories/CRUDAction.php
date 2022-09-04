<?php

namespace Gomee\Repositories;

use Gomee\Models\Model;
use Gomee\Models\MongoModel;
use Gomee\Models\SQLModel;
use Gomee\Validators\ExampleValidator;
use Gomee\Validators\Validator;
use ReflectionClass;

use Illuminate\Http\Request;


use Illuminate\Support\Str;
/**
 * Các phuong thức để crud
 * @method array beforeCreate(array $data) can thiep va tra ve mang du lieu truoc khi tao moi
 * @method array beforeUpdate(array $data, int $id) can thiep va tra ve mang du lieu truoc khi cap nhat
 * @method array beforeSave(array $data, int $id = null) can thiep va tra ve mang du lieu truoc khi cap nhat hoac tao moi
 * @method void afterCreate(Model $result) thuc hien hanh dong sau khi khoi tao
 * @method void afterUpdate(Model $result) thuc hien hanh dong sau khi khi cap nhat
 * @method void afterSave(Model $result) thuc hien hanh dong sau khi cap nhat hoac tao moi
 */
trait CRUDAction
{
    /**
     * @var string $validatorClass
     * full class name 
     */
    protected $validatorClass = 'ExampleValidator';

    /**
     *
     * @var array
     */
    protected $validateAttrs = [];
    /**
     * validator namespace
     *
     * @var string
     */
    protected $validatorNamespace = 'Gomee\Validators';
    /**
     * app validator namespace
     *
     * @var string
     */
    protected $appNamespace = 'App\Validators';


    protected $actor = null;


    protected $crudAction = null;

    protected $currentID = 0;

    public function setActor($actor = null)
    {
        if (is_string($actor) && in_array($a = strtolower($actor), ['admin', 'manager', 'client', 'private', 'public'])) {
            $this->actor = $a;
        }
        return $this;
    }

    public function getActor()
    {
        return $this->actor;
    }

    /**
     * dat validator class
     * @param string $validatorClass tên class
     * @return $this instance
     */
    public function setValidatorClass($validatorClass)
    {
        if (class_exists($validatorClass)) {
            $this->validatorClass = $validatorClass;
        } elseif (class_exists($validatorClass . 'Validator')) {
            $this->validatorClass = $validatorClass . 'Validator';
        } elseif (class_exists($this->appNamespace . "\\" . $validatorClass)) {
            $this->validatorClass = $this->appNamespace . "\\" . $validatorClass;
        } elseif (class_exists($this->appNamespace . "\\" . $validatorClass . 'Validator')) {
            $this->validatorClass = $this->appNamespace . "\\" . $validatorClass . 'Validator';
        } elseif (class_exists($this->validatorNamespace . "\\" . $validatorClass)) {
            $this->validatorClass = $this->validatorNamespace . "\\" . $validatorClass;
        } elseif (class_exists($this->validatorNamespace . "\\" . $validatorClass . 'Validator')) {
            $this->validatorClass = $this->validatorNamespace . "\\" . $validatorClass . 'Validator';
        }
        return $this;
    }




    /**
     * lay doi tuong validator
     * @param Request $request
     * @param string $validatorClass
     * @return ExampleValidator
     */
    public function getValidator(Request $request, $validatorClass = null)
    {
        if ($validatorClass) {
            $this->setValidatorClass($validatorClass);
        }
        $this->fire('beforegetvalidator', $this, $request);
        if ($this->validatorClass) {
            $c = null;

            if (class_exists($this->validatorClass)) {
                $c = $this->validatorClass;
            } elseif (class_exists($class = $this->validatorNamespace . '\\' . $this->validatorClass)) {
                $c = $class;
            } else {
                $c = 'Gomee\Validators\ExampleValidator';
            }
            $rc = new ReflectionClass($c);
            return $rc->newInstanceArgs([$request, $this]);
        }
        return new ExampleValidator($request, $this);
    }

    /**
     *
     * lay doi tuong validator
     * @return Validator
     */
    public function validator(Request $request, $validatorClass = null)
    {
        $this->fire('beforevalidator', $this, $request);
        $validator = $this->getValidator($request, is_string($validatorClass) ? $validatorClass : null);
        $validator->check(is_array($validatorClass) ? $validatorClass : []);
        return $validator;
    }

    /**
     * lay du lieu da duoc validate
     * @param Request $request
     * @param string|array $ruleOrvalidatorClass
     * @param array $messages
     * @return array
     */
    public function validate(Request $request, $ruleOrvalidatorClass = null, $messages = [])
    {
        $this->fire('beforevalidate', $this, $request);
        return $this->getValidator(
            $request,
            is_string($ruleOrvalidatorClass) ? $ruleOrvalidatorClass : null
        )->validate(
            is_array($ruleOrvalidatorClass) ? $ruleOrvalidatorClass : [],
            is_array($messages) ? $messages : []
        );
    }

    /**
     * lay du lieu da duoc validate
     * @param Request $Request
     * @param string|array $ruleOrvalidatorClass
     * @param array $messages
     * @return array
     */
    public function getValidateData(Request $request, $ruleOrvalidatorClass = null, $messages = [])
    {
        return $this->validate($request, $ruleOrvalidatorClass, $messages);
    }


    public function setValidatoAttrs(...$attrs)
    {
        if (is_array($attrs) && count($attrs)) {
            foreach ($attrs as $attr) {
                if (is_string($attr)) {
                    if ($attr == '*') {
                        $this->validateAttrs = '*';
                        return;
                    }
                    $this->validateAttrs[] = $attr;
                } elseif (is_array($attr)) {
                    $this->validateAttrs = array_merge($this->validateAttrs, $attr);
                }
            }
        }
    }

    public function getValidateAttrs()
    {
        if (is_array($this->validateAttrs) && count($this->validateAttrs)) {
            return $this->validateAttrs;
        }
        return null;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi tạo mới
     * @param  array  $data mang du lieu
     * @return array
     */
    public function beforeCreate(array $data)
    {
        return $data;
    }
    /**
     * Chuẩn hóa dữ liệu trước khi Cập nhật
     * @param  array  $data mang du lieu
     * @return array
     */
    public function beforeUpdate(array $data, $id = null)
    {
        return $data;
    }


    /**
     * luu du lieu
     * @param  array  $data mang du lieu
     * @param  integer $id        id cua ban ghi
     * @return Model
     */
    public function save(array $data, $id = null)
    {
        if ($id && $m = $this->_model->find($id)) {
            $model = $m;
            $this->crudAction = 'update';
            $this->currentID = $id;
            $data = $this->beforeUpdate($data, $id);
            $this->fire('beforeupdate', $this, $data, $id, $m);
            $this->fire('updating', $this, $data, $id, $m);
        } else {
            $this->fire('beforecreate', $this, $data);
            $this->fire('creating', $this, $data);
            $model = $this->model();
            if ($this->defaultValues) {
                $data = array_merge($this->defaultValues, $data);
            }
            $data = $this->beforeCreate($data);
        }
        $this->fire('beforesave', $this, $data, $id);
        $this->fire('saving', $this, $data, $id);

        if (is_array($d = $this->beforeSave($data))) {
            $data = $d;
        }


        if (!$data && !$id) return false;
        $data = $this->parseData($data);
        $model->fill($data);
        $this->checkModelUuid($model);
        // dd($model);
        $model->save();
        if ($id && $id == $model->{$this->_primaryKeyName}) {
            $this->afterUpdate($model);
            $this->fire('afterupdate', $this, $model);
            $this->fire('updated', $this, $model);
        } else {
            $this->afterCreate($model);
            $this->fire('aftercreate', $this, $model);
            $this->fire('created', $this, $model);
        }
        $this->afterSave($model);
        $this->fire('aftersave', $this, $model);
        $this->fire('saved', $this, $model);
        $this->crudAction = null;
        $this->currentID = 0;

        return $model;
    }


    protected function checkModelUuid($model)
    {
        if (!$model->useUuid || $model->useUuid === 'no') return;
        $uuidName = $model->useUuid === true ? 'uuid' : ($model->useUuid === 'primary' ? $model->getKeyName() : $model->useUuid);
        $uuidValue = $model->{$uuidName};
        // Check if the primary key doesn't have a value
        if (!$uuidValue) {
            // Dynamically set the primary key
            $model->setAttribute($uuidName, Str::uuid()->toString());
        }
    }

    /**
     * chuẩn hóa data trước khi lưu
     */
    public function parseData($data = [])
    {
        $escape = [];
        if (count($data)) {
            foreach ($data as $key => $value) {
                if ((is_array($value) || is_object($value)) && (!$this->_model->casts || !array_key_exists($key, $this->_model->casts))) {
                    $escape[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
                } else {
                    $escape[$key] = $value;
                }
            }
        }
        return $escape;
    }

    /**
     * xử lý data trước khi luu
     * @param array $data dữ liệu truyền vào
     * 
     * @return array trả về mảng dữ liệu
     */
    public function beforeSave($data)
    {
        return $data;
    }

    /**
     * tao bản ghi mới
     * @param array
     * 
     * @return false|Model|MongoModel|SQLModel
     */
    public function create(array $data = [])
    {
        if ($model = $this->save($data)) {
            // do something

            // $this->afterCreate($model);
            return $model;
        }
        return false;
    }

    /**
     * cập nhật dử liệu bản ghi
     * @param int|string $id
     * @param array $data
     * @return Model|MongoModel|SQLModel|false
     */
    public function update($id, array $data = [])
    {
        if (!$this->find($id)) return false;
        if ($model = $this->save($data, $id)) {
            // do something

            // $this->afterUpdate($model);
            return $model;
        }
        return false;
    }

    /**
     * tạo bản ghi nếu chưa tồn tại
     *
     * @param array $data
     * @return Model
     */
    public function createIfNotExists(array $data = [], array $columns = [])
    {
        $params = $data;
        if ($columns) {
            $params = array_copy($data, $columns);
        }
        if (!$params) {
            if ($data) return $this->create($data);
            return null;
        }
        if (!($d = $this->first($params))) {
            $d = $this->create($data);
        }
        return $d;
    }
    /**
     * tạo bản ghi nếu tồn tại thì update
     *
     * @param array $data
     * @param array $columns
     * @return \Gomee\Models\Model
     */
    public function createOrUpdate(array $data = [], array $columns = [])
    {
        $params = $data;
        if ($columns) {
            $params = array_copy($data, $columns);
        }
        if ($params && $d = $this->first($params)) {
            return $this->update($d->{$this->_primaryKeyName}, $data);
        }
        return $this->create($data);
    }


    /**
     * Delete
     *
     * @param int|int[] $id
     * @return bool
     */
    public function delete($id = null)
    {
        if (!$id) {
            // 
            if (count($this->params) || count($this->actions)) {
                $this->fire('beforedelete', $this, $id);
                $this->fire('deleting', $this, $id);
                $rs = $this->query()->delete();
                $this->fire('afterdelete', $this, $id);
                $this->fire('deleting', $this, $id);
                return $rs;
            }
            return false;
        }
        // nếu xóa nhiều
        if (is_array($id)) {
            $ids = [];
            $list = $this->get([$this->_primaryKeyName => $id]);
            if (count($list)) {
                $this->fire('beforedelete', $this, $id);
                $this->fire('deleting', $this, $id);
                foreach ($list as $item) {
                    if (!$item->canDelete()) continue;
                    
                    $ids[] = $item->{$this->_primaryKeyName};
                    $item->delete();
                    $this->fire('deleted', $this, $item);
                }
                
                $this->fire('deleted', $this, $ids);
                $this->fire('afterdelete', $this, $ids);
            }
            return $ids;
        }
        $result = $this->find($id);
        if ($result) {
            if ($result->canDelete()) {
                $this->fire('befordelete', $this, $id, $result);
                $this->fire('deleting', $this, $id, $result);
                $result->delete();
                $this->fire('deleted', $this, $id, $result);
                $this->fire('afterdelete', $this, $id, $result);
                return true;
            }
        }

        return false;
    }


    /**
     * Delete
     *
     * @param int|int[] $id
     * @return bool
     */
    public function forceDelete($id = null)
    {
        if (!$id) {
            $ids = [];
            $list = $this->get();
            if (count($list)) {
                $this->fire('beforeForceDelete', $this, $id, $list);
                foreach ($list as $item) {
                    if (!$item->canForceDelete()) continue;
                    $ids[] = $item->{$this->_primaryKeyName};
                    $item->forceDelete();
                }
                $this->fire('afterForceDelete', $this, $ids, $list);
            }
            return $ids;
        }
        // nếu xóa nhiều
        if (is_array($id)) {
            $ids = [];
            $list = $this->get([$this->_primaryKeyName => $id]);
            if (count($list)) {
                $this->fire('beforeForceDelete', $this, $id, $list);
                foreach ($list as $item) {
                    if (!$item->canForceDelete()) continue;
                    $ids[] = $item->{$this->_primaryKeyName};
                    $item->forceDelete();
                }
                $this->fire('afterForceDelete', $this, $ids, $list);
            }
            return $ids;
        }
        $result = $this->find($id);
        if ($result) {

            if ($result->canForceDelete()) {
                $this->fire('beforeForceDelete', $this, $id, $result);
                $result->forceDelete();
                $this->fire('afterForceDelete', $this, $id, $result);
                return true;
            }
        }

        return false;
    }



    /**
     * trash
     *
     * @param $id
     * @return bool
     */
    public function moveToTrash($id)
    {
        $result = $this->find($id);
        if ($result && $result->canMoveToTrash()) {
            $this->fire('beforeMoveToTrash', $this, $id, $result);
            $rs = $result->moveToTrash();
            $this->fire('afterMoveToTrash', $this, $id, $result);
            return $rs;
        }

        return false;
    }


    /**
     * trash
     *
     * @param $id
     * @return bool
     */
    public function softDelete($id)
    {
        $result = $this->find($id);
        if ($result && $result->canMoveToTrash()) {
            $this->fire('beforeMoveToTrash', $this, $id, $result);
            $rs = $result->moveToTrash();
            $this->fire('afterMoveToTrash', $this, $id, $result);
            return $rs;
        }

        return false;
    }

    /**
     * khôi phục bản ghi
     * @param int $id
     */
    public function restore($id)
    {
        $result = $this->find($id);
        if ($result) {
            $this->fire('beforerestore', $this, $id, $result);
            $rs = $result->restore();
            $this->fire('afterrestore', $this, $id, $result);
            return $rs;
        }

        return false;
    }

    /**
     * xóa vĩnh viễn bản ghi
     * @param int $id
     */
    public function erase($id)
    {
        $result = $this->find($id);
        if ($result && $result->canErase()) {
            return $result->erase();
        }

        return false;
    }

    /**
     * kiểm tra cho cho phep chuyen vao thung ra hay ko
     * @param int $id
     */
    public function canMoveToTrash($id = null)
    {
        if ($id && $model = $this->find($id)) return $model->canMoveToTrash();
        return false;
    }

    /**
     * kiểm tra cho cho phep chuyen vao thung ra hay ko
     * @param int $id
     */
    public function canDelete($id = null)
    {
        if ($id && $model = $this->find($id)) return $model->canDelete();
        return false;
    }
}
