<?php
namespace Weikit\Wechat\Sdk;

use InvalidArgumentException;
use Weikit\Wechat\Sdk\Caches\FileCache;
use Weikit\Wechat\Sdk\Requests\CurlRequest;

/**
 * Class Wechat
 * @package Weikit\Wechat\Sdk
 */
class Wechat extends BaseWechat
{
    /**
     * 微信官网基本地址
     */
    const WECHAT_WEB_URL = 'https://mp.weixin.qq.com';
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://api.weixin.qq.com';
    /**
     * @var string 微信基本Url
     */
    public $baseUrl = self::WECHAT_BASE_URL;
    /**
     * @var string 公众号appId
     */
    public $appId;
    /**
     * @var string 公众号appSecret
     */
    public $appSecret;
    /**
     * @var string 公众号接口验证token
     */
    public $token;

    public function __construct($config = array())
    {
        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
        parent::__construct($config);
    }

    public function init()
    {
        if ($this->appId === null) {
            throw new InvalidArgumentException('The wechat property "appId" must be set.');
        } elseif ($this->appSecret === null) {
            throw new InvalidArgumentException('The wechat property "appSecret" must be set.');
        } elseif ($this->token === null) {
            throw new InvalidArgumentException('The wechat property "token" must be set.');
        }
    }

    /**
     * 核心组件
     *
     * @return array
     */
    public function coreComponents()
    {
        return array(
            'cache' => array('class' => 'Weikit\Wechat\Sdk\Caches\FileCache'), // 缓存组件
            'request' => array('class' => 'Weikit\Wechat\Sdk\Requests\CurlRequest'), // 接口HTTP请求组件

            'menu' => array('class' => 'Weikit\Wechat\Sdk\Components\Menu'), // 自定义菜单
            'message' => array('class' => 'Weikit\Wechat\Sdk\Components\Message') // 消息管理
        );
    }

    /**
     * @var BaseRequest
     */
    private $_request;

     /**
     * 获取Request组件
     *
     * @return Request|BaseRequest
     */
    public function getRequest()
    {
        if ($this->_request === null) {
            $this->setRequest($this->get('request', false));
        }
        return $this->_request;
    }

    /**
     * 设置Request组件
     *
     * @param BaseRequest $request
     */
    public function setRequest(BaseRequest $request)
    {
        if ($request->baseUrl === null) {
            $request->baseUrl = $this->baseUrl;
        }
        $this->_request = $request;
    }

    /**
     * @var BaseCache
     */
    private $_cache;

    /**
     * 获取Cache组件
     *
     * @return Cache|BaseCache
     */
    public function getCache()
    {
        if ($this->_cache === null) {
            $this->setCache($this->get('cache', false));
        }
        return $this->_cache;
    }

    /**
     * 设置Cache组件
     *
     * @param BaseCache $cache
     */
    public function setCache(BaseCache $cache)
    {
        if ($cache->keyPrefix === null) {
            $cache->keyPrefix = 'wechat_' . $this->appId . '_'; // 设置默认缓存前缀
        }
        $this->_cache = $cache;
    }

    /**
     * access_token API前缀
     */
    const WECHAT_ACCESS_TOKEN_PREFIX = 'cgi-bin/token';

    /**
     * 接口请求获取access_token
     *
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140183&token=&lang=zh_CN
     * @param string $grantType
     * @return array|bool
     */
    protected function requestAccessToken($grantType = 'client_credential')
    {
        $result = $this->getRequest()
            ->get(self::WECHAT_ACCESS_TOKEN_PREFIX, array(
                'appid' => $this->appId,
                'secret' => $this->appSecret,
                'grant_type' => $grantType
            ));
        return isset($result['access_token']) ? $result : false;
    }

    /**
     * 获取微信服务器IP地址
     */
    const WECHAT_IP_LIST_GET_PREFIX = 'cgi-bin/getcallbackip';
    /**
     * 获取微信服务器IP地址
     *
     * @return array|bool
     */
    public function getIpList()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_IP_LIST_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['ip_list']) ? $result['ip_list'] : false;
    }
    /* =================== 消息管理 =================== */

    /**
     * @see Weikit\Wechat\Sdk\Components\Menu 自定义菜单
     */

    /* =================== 消息管理 =================== */

    /**
     * @see Weikit\Wechat\Sdk\Components\Message 客服消息
     * @see Weikit\Wechat\Sdk\Components\MassMessage 高级群发接口
     */

}