<?php
namespace Weikit\Wechat\Sdk\Caches;

use Exception;
use InvalidArgumentException;
use Weikit\Wechat\Sdk\BaseCache;

/**
 * Class FileCache
 * @package Weikit\Wechat\Sdk
 */
class FileCache extends BaseCache
{
    /**
     * @var string 缓存文件后缀
     */
    public $cacheFileSuffix = '.cache.bin';

    /**
     * 存储缓存数据
     *
     * @param string $key
     * @param mixed $value
     * @param int $duration
     * @return bool
     * @throws Exception
     */
    public function set($key, $value, $duration = 7200)
    {
        $key = $this->buildKey($key);
        $cacheFile = $this->getCacheFile($key);

        if (@file_put_contents($cacheFile, serialize($value), LOCK_EX) === false) {
            $error = error_get_last();
            throw new Exception("Unable to write cache file '{$cacheFile}': {$error['message']}");
        }

        @chmod($cacheFile, 0775);
        if ($duration <= 0) {
            $duration = 31536000; // 1 year
        }

        return @touch($cacheFile, $duration + time());
    }

    /**
     * 获取缓存数据
     *
     * @param $key
     * @return bool|mixed
     */
    public function get($key)
    {
        $key = $this->buildKey($key);
        $cacheFile = $this->getCacheFile($key);

        if (@filemtime($cacheFile) > time()) {
            $fp = @fopen($cacheFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $cacheValue = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return unserialize($cacheValue);
            }
        }

        return false;
    }

    /**
     * @var string|null
     */
    private $_key;

    /**
     * 获取基本缓存键值前缀
     *
     * @param null $key 缓存键值, 如果不为空则返回加入基本缓存键值后的缓存键值
     * @return null|string
     */
    public function getKey($key = null)
    {
        return $this->_key . $key;
    }

    /**
     * 设置基本缓存键值前缀
     *
     * @param $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * @var string
     */
    private $_cachePath;

    /**
     * 获取缓存路劲
     *
     * @return string
     */
    public function getCachePath()
    {
        if ($this->_cachePath === null) {
            $this->_cachePath = sys_get_temp_dir();
        }
        return $this->_cachePath;
    }

    /**
     * 设置缓存路径
     *
     * @param string $cachePath
     * @throws \UnexpectedValueException
     */
    public function setCachePath($cachePath)
    {
        if (!is_dir($cachePath)) {
            throw new InvalidArgumentException("The cache path '{$cachePath}' must be an directory.");
        } elseif (!is_writable($cachePath)) {
            throw new InvalidArgumentException("The cache path '{$cachePath}' is not writable.");
        }
        $this->_cachePath = $cachePath;
    }

    /**
     * 获取缓存文件
     *
     * @param string $key 缓存键值
     * @return string
     */
    protected function getCacheFile($key)
    {
        return $this->getCachePath() . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
    }
}