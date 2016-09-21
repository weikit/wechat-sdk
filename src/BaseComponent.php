<?php
namespace Weikit\Wechat\Sdk;

use Weikit\Wechat\Sdk\Base\Object;

/**
 * Class BaseComponent
 * @package Weikit\Wechat\Sdk
 */
abstract class BaseComponent extends Object
{
    /**
     * @var BaseWechat|Wechat $wechat
     */
    protected $wechat;

    public function __construct(BaseWechat $wechat, $config = array())
    {
        $this->wechat = $wechat;
        parent::__construct($config);
    }

    /**
     * @param bool $force
     * @return mixed
     */
    public function getAccessToken($force = false)
    {
        return $this->wechat->getAccessToken($force);
    }

    /**
     * 获取Request组件
     *
     * @return BaseRequest
     */
    public function getRequest()
    {
        return $this->wechat->getRequest();
    }

    /**
     * 获取Cache组件
     *
     * @return BaseCache
     */
    public function getCache()
    {
        return $this->wechat->getCache();
    }
}