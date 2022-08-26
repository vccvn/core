<?php

namespace App\Repositories\FOLDER;

use Gomee\Repositories\BaseRepository;
/**
 * validator 
 * 
 */
use App\Validators\FOLDER\NAMEValidator;
use App\Masks\FOLDER\MODELMask;
use App\Masks\FOLDER\MODELCollection;
class NAMERepository extends BaseRepository
{
    /**
     * class chứ các phương thức để validate dử liệu
     * @var string $validatorClass 
     */
    protected $validatorClass = NAMEValidator::class;
    /**
     * tên class mặt nạ. Thường có tiền tố [tên thư mục] + \ vá hậu tố Mask
     *
     * @var string
     */
    protected $maskClass = MODELMask::class;

    /**
     * tên collection mặt nạ
     *
     * @var string
     */
    protected $maskCollectionClass = MODELCollection::class;


    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\MODEL::class;
    }

}