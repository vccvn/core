<?php

namespace Gomee\Models;

use Gomee\Constants\DbConnectionConstant;
// use Jenssegers\Mongodb\Eloquent\Model as BaseModel;
// use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class MongoModel extends Model
{
    use ModelEventMethods, ModelFileMethods, CommonMethods, Uuid;

    const MODEL_TYPE = 'mongo'; 
    const UNTRASHED = 0;
    const TRASHED = 1;

    protected $connection = DbConnectionConstant::NOSQL;

    protected $dates = ['deleted_at'];

    protected $fillable = ['*'];

    protected $appends = ['id'];

    protected $guarded = [];

    public function __getModelType__()
    {
        return static::MODEL_TYPE;
    }

    /**
     * các giá trị mặc định
     *
     * @var array
     */
    protected $defaultValues = [];

    /**
     * lấy về giá trị mặc định khi muốn fill để create data
     *
     * @return array<string, mixed>
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }
}
