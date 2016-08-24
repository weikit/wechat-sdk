<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 二维码管理
 * @package Weikit\Wechat\Sdk\Components
 */
class Qrcode extends BaseComponent
{
    /**
     * 生成带参数的二维码
     */
    const WECHAT_QRCODE_CREATE_PREFIX = 'cgi-bin/qrcode/create';
    /**
     * 生成带参数的二维码
     *
     * @param array $data
     * @return array|bool
     */
    public function create(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_QRCODE_CREATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 通过ticket换取二维码
     */
    const WECHAT_QRCODE_GET_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
    /**
     * 通过ticket换取二维码
     * ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。
     *
     * @param $ticket
     * @return string
     */
    public function getUrl($ticket)
    {
        return $this->getRequest()
            ->buildUrl(array(self::WECHAT_QRCODE_GET_URL, 'ticket' => $ticket));
    }
}