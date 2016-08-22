<?php
namespace Weikit\Wechat\Sdk;

use Weikit\Wechat\Sdk\Base\ServiceLocator;

/**
 * Class BaseWechat
 * @package Weikit\Wechat\Sdk
 */
abstract class BaseWechat extends ServiceLocator
{
    /**
     * @var string API请求基本url
     */
    public $baseUrl = '';
    /**
     * @var array
     */
    public $_accessToken;

    /**
     * 获取access_token
     *
     * @param bool $force
     * @return string
     */
    public function getAccessToken($force = false)
    {
        $time = time(); // 为了更精确控制.取当前时间计算

        if ($this->_accessToken === null || $this->_accessToken['expire'] < $time || $force) {
            $result = $this->_accessToken === null && !$force ? $this->getCache()->get('access_token') : false;
            if ($result === false) {
                $result = $this->requestAccessToken();
                if (!isset($result['access_token']) && !isset($result['expires_in'])) {
                    throw new \UnexpectedValueException('Fail to get access_token from wechat server.');
                }
                $result['expires_in'] -= 15; // 15秒误差
                $result['expire'] = $time + $result['expires_in'];
                $this->getCache()->set('access_token', $result, $result['expires_in']);
            }
            $this->setAccessToken($result);
        }
        return $this->_accessToken['access_token'];
    }

    /**
     * 设置access_token
     *
     * @param array $accessToken array('access_token' => 'xx', 'expire' => '时间戳')
     */
    public function setAccessToken(array $accessToken)
    {
        if (!isset($accessToken['access_token'])) {
            throw new \InvalidArgumentException('The access_token must be set.');
        } elseif(!isset($accessToken['expire'])) {
            throw new \InvalidArgumentException('Wechat access_token expire time must be set.');
        }
        $this->_accessToken = $accessToken;
    }

    /**
     * 请求微信服务器获取AccessToken
     * 必须返回以下格式内容失败则返回false
     * ```php
     * array(
     *     'access_token => 'xxx',
     *     'expirs_in' => 7200
     * )
     * ```
     *
     * @return array|bool
     */
    abstract protected function requestAccessToken();

    /**
     * 获取Request组件
     *
     * @return BaseRequest
     */
    abstract public function getRequest();

    /**
     * 设置Request组件
     *
     * @param BaseRequest $request
     */
    abstract public function setRequest(BaseRequest $request);

    /**
     * 获取Cache组件
     *
     * @return BaseCache
     */
    abstract public function getCache();

    /**
     * 设置Cache组件
     *
     * @param BaseCache $cache
     */
    abstract public function setCache(BaseCache $cache);
}