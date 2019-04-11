<?php

namespace Weikit\Wechat\Sdk;

/**
 * 基础Cache类
 * @package Weikit\Wechat\Sdk
 */
abstract class BaseCache extends BaseComponent
{
    /**
     * @var string 缓存前缀
     */
    public $keyPrefix;

    /**
     * 保存缓存数据
     *
     * @param $key
     * @param $value
     * @param int $duration
     *
     * @return bool
     */
    abstract public function set($key, $value, $duration = 7200);

    /**
     * 获取缓存数据
     *
     * @param $key
     *
     * @return mixed|bool
     */
    abstract public function get($key);

    /**
     * 创建带key前缀的缓存键
     *
     * @param mixed $key
     *
     * @return string
     */
    public function buildKey($key)
    {
        if ( ! is_string($key)) {
            throw new \InvalidArgumentException('The cache key must be an string.');
        }
        $key = ctype_alnum($key) && mb_strlen($key, '8bit') <= 32 ? $key : md5($key);

        return $this->keyPrefix . $key;
    }
}