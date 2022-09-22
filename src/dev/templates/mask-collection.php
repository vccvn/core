<?php
namespace App\MasksSUB;

use Gomee\Masks\MaskCollection;

/**
 * @property MASKMask[] $items
 */
class NAMECollection extends MaskCollection
{
    /**
     * lấy tên class mask tương ứng
     *
     * @return string
     */
    public function getMask()
    {
        return MASKMask::class;
    }
    // xem Collection mẫu ExampleCollection
}
