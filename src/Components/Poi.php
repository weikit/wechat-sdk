<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 门店
 * @package Weikit\Wechat\Sdk\Components
 */
class Poi extends BaseComponent
{
    /**
     * 上传图片
     *
     * @param $path
     * @return bool
     * @see \Weikit\Wechat\Sdk\Components\Material::uploadImage
     */
    public function uploadImage($path)
    {
        return $this->wechat->material->uploadImage($path, 'buffer');
    }
}