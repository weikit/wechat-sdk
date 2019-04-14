<?php

namespace Weikit\Wechat\Sdk;

use InvalidArgumentException;
use Weikit\Wechat\Sdk\Exceptions\ComponentNotFoundExceptions;

/**
 * @property \Weikit\Wechat\Sdk\Components\Menu $menu 自定义菜单
 * @property \Weikit\Wechat\Sdk\Components\Message $message 客服消息
 * @property \Weikit\Wechat\Sdk\Components\MassMessage $massMessage 高级群发接口
 * @property \Weikit\Wechat\Sdk\Components\Template $template 模板消息接口
 * @property \Weikit\Wechat\Sdk\Components\Oauth $oauth 网页授权
 * @property \Weikit\Wechat\Sdk\Components\Material $material 素材管理
 * @property \Weikit\Wechat\Sdk\Components\User $user 用户管理
 * @property \Weikit\Wechat\Sdk\Components\Qrcode $qrcode 二维码管理
 * @property \Weikit\Wechat\Sdk\Components\Stats $stats 数据统计
 * @property \Weikit\Wechat\Sdk\Components\CustomerService $customerService 新版客服功能
 * @property \Weikit\Wechat\Sdk\Components\Card $card 微信卡券
 * @property \Weikit\Wechat\Sdk\Components\Poi $poi 门店
 *
 * @property \Weikit\Wechat\Sdk\Components\Authorization $authorization  第三方平台授权
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
    /**
     * @var string 加密AES Key
     */
    public $encodingAESKey;

    public function init()
    {
        if ($this->appId === null) {
            throw new InvalidArgumentException('The wechat property "appId" must be set');
        } elseif ($this->appSecret === null) {
            throw new InvalidArgumentException('The wechat property "appSecret" must be set');
        } elseif ($this->token === null) {
            throw new InvalidArgumentException('The wechat property "token" must be set');
        }
    }

    /**
     * @param array $components
     */
    public function setComponents(array $components)
    {
        foreach ($components as $id => $component) {
            $this->setComponent($component);
        }
    }

    private $_components = [
        'cache'   => ['class' => 'Weikit\Wechat\Sdk\Caches\FileCache'], // 缓存组件
        'request' => ['class' => 'Weikit\Wechat\Sdk\Requests\CurlRequest'], // 接口HTTP请求组件

        'menu'            => ['class' => 'Weikit\Wechat\Sdk\Components\Menu'], // 自定义菜单
        'message'         => ['class' => 'Weikit\Wechat\Sdk\Components\Message'], // 消息管理
        'massMessage'     => ['class' => 'Weikit\Wechat\Sdk\Components\MassMessage'], // 高级群发接口
        'template'        => ['class' => 'Weikit\Wechat\Sdk\Components\Template'], // 模板消息接口
        'oauth'           => ['class' => 'Weikit\Wechat\Sdk\Components\Oauth'], // 网页授权
        'material'        => ['class' => 'Weikit\Wechat\Sdk\Components\Material'], // 素材管理
        'qrcode'          => ['class' => 'Weikit\Wechat\Sdk\Components\Qrcode'], // 二维码管理
        'stats'           => ['class' => 'Weikit\Wechat\Sdk\Components\Stats'], // 数据统计
        'customerService' => ['class' => 'Weikit\Wechat\Sdk\Components\CustomerService'], // 新版客服功能
        'card'            => ['class' => 'Weikit\Wechat\Sdk\Components\Card'], // 微信卡券
        'poi'             => ['class' => 'Weikit\Wechat\Sdk\Components\Poi'], // 门店

        'authorization' => ['class' => 'Weikit\Wechat\Sdk\Component\Authorization'], // 第三方平台授权
    ];

    /**
     * @param string $id
     * @param array|BaseComponent $config
     */
    public function setComponent($id, $config)
    {
        if (is_object($config) && ! is_subclass_of($config, BaseObject::class)) {
            throw new InvalidArgumentException("The component '{$id}' of wechat must be subclass of '" . BaseObject::class . "''");
        } elseif ( ! is_array($config)) {
            throw new InvalidArgumentException("The component config is not array");
        } elseif (empty($config['class'])) {
            throw new InvalidArgumentException("The property 'class' of object config must be set");
        }
        $this->_components[$id] = $config;
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getComponent($id)
    {
        if ( ! array_key_exists($id, $this->_components)) {
            throw new ComponentNotFoundExceptions("The component '{$id}' of wechat is not exists");
        } elseif (is_array($this->_components[$id])) {
            $this->setComponent($id, $this->createComponent($this->_components[$id]));
        }

        return $this->_components[$id];
    }

    /**
     * @param array $config
     *
     * @return mixed
     */
    protected function createComponent(array $config)
    {
        $class = $config['class'];
        unset($config['class']);
        return new $class($this, $config);
    }

    /**
     * @var BaseRequest
     */
    private $_request;

    /**
     * 获取Request组件
     *
     * @return BaseRequest
     */
    public function getRequest()
    {
        if ($this->_request === null) {
            $this->setRequest($this->getComponent('request'));
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
     * @return BaseCache
     */
    public function getCache()
    {
        if ($this->_cache === null) {
            $this->setCache($this->getComponent('cache', false));
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
     * 微信服务器请求签名验证
     *
     * @param string $signature 微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
     * @param string $timestamp 时间戳
     * @param string $nonce 随机数
     *
     * @return bool
     */
    public function verifySignature($signature = null, $timestamp = null, $nonce = null)
    {
        $signature === null && isset($_GET['signature']) && $signature = $_GET['signature'];
        $timestamp === null && isset($_GET['timestamp']) && $timestamp = $_GET['timestamp'];
        $nonce === null && isset($_GET['nonce']) && $nonce = $_GET['nonce'];
        $tmpArr = [$this->token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);

        return sha1($tmpStr) == $signature;
    }

    /**
     * 解析微信请求消息
     *
     * @param null $message
     * @param null $encryptType
     *
     * @return array|mixed
     */
    public function parseMessage($message = null, $encryptType = null)
    {
        $message === null && $message = file_get_contents('php://input');
        $encryptType === null && isset($_GET['encrypt_type']) && $encryptType = $_GET['encrypt_type'];
        $return = [];
        if ( ! empty($message)) {
            if ($encryptType === 'aes') {
                $messageSignature = isset($_GET['msg_signature']) ? $_GET['msg_signature'] : null;
                $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : null;
                $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : null;
                $message = $this->decryptMessage($message, $timestamp, $nonce, $messageSignature);
            }
            if ( ! empty($message)) {
                $return = $this->parseXml($message);
            }
        }

        return $return;
    }

    /**
     * 解密微信请求的XML消息
     *
     * @param string $message xml消息
     * @param $timestamp
     * @param $nonce
     * @param $messageSignature
     *
     * @return false|string
     */
    public function decryptMessage($message, $timestamp, $nonce, $messageSignature)
    {
        if ($this->getMessageCrypter()->decryptMsg($messageSignature, $timestamp, $nonce, $message, $return) != 0) {
            return false;
        }

        return $return;
    }

    /**
     * 加密微信请求的XML消息
     *
     * @param string $message xml消息
     * @param $timeStamp
     * @param $nonce
     *
     * @return false|string
     */
    public function encryptMessage($message, $timeStamp, $nonce)
    {
        if ($this->getMessageCrypter()->encryptMsg($message, $timeStamp, $nonce, $return) != 0) {
            return false;
        }

        return $return;
    }

    /**
     * access_token API前缀
     */
    const WECHAT_ACCESS_TOKEN_PREFIX = 'cgi-bin/token';

    /**
     * 接口请求获取access_token
     *
     * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140183&token=&lang=zh_CN
     *
     * @param string $grantType
     *
     * @return array|bool
     */
    protected function requestAccessToken($grantType = 'client_credential')
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_ACCESS_TOKEN_PREFIX,
                           'appid'      => $this->appId,
                           'secret'     => $this->appSecret,
                           'grant_type' => $grantType,
                       ]);

        return isset($result['access_token']) ? $result : false;
    }

    /**
     * 请求获取api ticket
     */
    const TYPE_API_TICKET_GET_PREFIX = 'cgi-bin/ticket/getticket';

    /**
     * 请求获取api ticket
     *
     * @param string $type api ticket 类型
     *
     * @return bool|mixed
     */
    public function requestApiTicket($type)
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_ACCESS_TOKEN_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'type'         => $type,
                       ]);

        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
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
                       ->get([
                           self::WECHAT_IP_LIST_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ]);

        return isset($result['ip_list']) ? $result['ip_list'] : false;
    }
    /* =================== 消息管理 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Menu 自定义菜单
     */

    /* =================== 消息管理 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Message 客服消息
     * @see \Weikit\Wechat\Sdk\Components\MassMessage 高级群发接口
     * @see \Weikit\Wechat\Sdk\Components\Template 模板消息接口
     */

    /**
     * 获取公众号的自动回复规则
     */
    const WECHAT_AUTO_REPLY_INFO_GET_PREFIX = 'cgi-bin/get_current_autoreply_info';

    /**
     * 获取公众号的自动回复规则
     *
     * @return array|bool|mixed
     */
    public function getAutoReplyInfo()
    {
        $result = $this
            ->getRequest()
            ->get([
                self::WECHAT_AUTO_REPLY_INFO_GET_PREFIX,
                'access_token' => $this->getAccessToken(),
            ]);

        return ! array_key_exists('errcode', $result) ? $result : false;
    }

    /* =================== 微信网页开发 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Oauth 模板消息接口
     */

    /* =================== 素材管理 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Material 素材管理
     */

    /* =================== 用户管理 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\User 用户管理
     */

    /* =================== 账号管理 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Qrcode 二维码管理
     */
    /**
     * 长链接转短链接接口
     */
    const WECHAT_SHORT_URL_GET_PREFIX = 'cgi-bin/shorturl';

    /**
     * 长链接转短链接接口
     *
     * @param $url
     *
     * @return bool|mixed
     */
    public function getShortUrl($url)
    {
        return $this->getRequest()
                    ->raw([
                        self::WECHAT_SHORT_URL_GET_PREFIX,
                        'access_token' => $this->getAccessToken(),
                    ], [
                        'action'   => 'long2short',
                        'long_url' => $url,
                    ]);

        return isset($result['short_url']) ? $result['short_url'] : false;
    }

    /* =================== 数据统计 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Stats 数据统计
     */

    /* =================== 微信卡券 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Card 微信卡券
     */

    /* =================== 微信门店 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Poi 门店
     */

    /* =================== 微信小店 =================== */
    /* =================== 微信设备功能 =================== */

    /* =================== 新版客服功能 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\CustomerService 新版客服功能
     */

    /* =================== 微信摇一摇周边 =================== */
    /* =================== 微信连WIFI =================== */
    /* =================== 微信扫一扫 =================== */

    /* =================== 开放平台 =================== */

    /**
     * @see \Weikit\Wechat\Sdk\Components\Authorization 第三方平台授权
     */
}