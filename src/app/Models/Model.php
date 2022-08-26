<?php

namespace Gomee\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    //
    use ModelEventMethods, ModelFileMethods, CommonMethods, Uuid;
    const MODEL_TYPE = 'default';
    const UNTRASHED = 0;
    const TRASHED = 1;


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
