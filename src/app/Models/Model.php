<?php

namespace Gomee\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @property bool $multilang chế độ đa ngôn ngữ
 * @property string $localeTitleColumn cột tiêu đề đa ngôn ngữ sẽ dc 
 */
class Model extends BaseModel
{
    //
    use ModelEventMethods, ModelFileMethods, CommonMethods, Uuid;
    const MODEL_TYPE = 'default';
    const UNTRASHED = 0;
    const TRASHED = 1;

    
    public $multilang = false;

    public $localeTitleColumn = null;


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
    
    /**
     * Get all of the allLanguageContents for the Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allLanguageContents(): HasMany
    {
        $ref = defined('static::REF_KEY') ? static::REF_KEY : ($this->table??'data');
        return $this->hasMany('App\\Models\\MultiLanguageContent', 'ref_id', 'id')->where('ref', $ref);
    }

    /**
     * Get the localeContents associated with the Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function localeContent(): HasOne
    {
        $ref = defined('static::REF_KEY') ? static::REF_KEY : ($this->table??'data');
        return $this->hasOne('App\\Models\\MultiLanguageContent', 'ref_id', 'id')->where('ref', $ref)->where('locale', config('app.locale'));
    }

    
    public function rewriteDataIfHasMLC()
    {
        if($this->multilang && ($localeContent = $this->localeContent)){
            if($slug = $localeContent->slug){
                $this->slug = $slug;
            }
            
        }
    }
}
