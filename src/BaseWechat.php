<?php

namespace Weikit\Wechat\Sdk;

/**
 * Class BaseWechat
 * @package Weikit\Wechat\Sdk
 */
abstract class BaseWechat extends BaseObject
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
     *
     * @return string
     */
    public function getAccessToken($force = false)
    {
        $time = time(); // 为了更精确控制.取当前时间计算

        if ($this->_accessToken === null || $this->_accessToken['expire'] < $time || $force) {
            $result = $this->_accessToken === null && ! $force ? $this->getCache()->get('access_token') : false;
            if ($result === false) {
                $result = $this->requestAccessToken();
                if ( ! isset($result['access_token']) && ! isset($result['expires_in'])) {
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
        if ( ! isset($accessToken['access_token'])) {
            throw new \InvalidArgumentException('The access_token must be set.');
        } elseif ( ! isset($accessToken['expire'])) {
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
     * @var array
     */
    private $_apiTicket;

    /**
     * 获取API ticket(默认获取jsapi)
     *
     * @param bool $force
     * @param string $type
     *
     * @return mixed
     */
    public function getApiTicket($force = true, $type = 'jsapi')
    {
        $time = time(); // 为了更精确控制.取当前时间计算

        $keyExists = array_key_exists($type, $this->_apiTicket);
        if (
            ! $keyExists || $this->_apiTicket[$type] === null || $this->_apiTicket[$type]['expire'] < $time || $force
        ) {
            $cacheKey = 'api_ticket_' . $type;
            $result = ( ! $keyExists || $this->_apiTicket[$type] === null) && ! $force ? $this->getCache()->get($cacheKey) : false;
            if ($result === false) {
                $result = $this->requestApiTicket($type);
                if ( ! isset($result['ticket']) && ! isset($result['expires_in'])) {
                    throw new \UnexpectedValueException("Fail to get '{$type}' ticket from wechat server.");
                }
                $result['expires_in'] -= 15; // 15秒误差
                $result['expire'] = $time + $result['expires_in'];
                $this->getCache()->set($cacheKey, $result, $result['expires_in']);
            }
            $this->setApiTicket($result, $type);
        }

        return $this->_apiTicket[$type]['ticket'];
    }

    /**
     * 设置API ticket
     *
     * @param array $ticket
     * @param $type
     */
    public function setApiTicket(array $ticket, $type)
    {
        if ( ! isset($ticket['ticket'])) {
            throw new \InvalidArgumentException('The api ticket must be set.');
        } elseif ( ! isset($ticket['expire'])) {
            throw new \InvalidArgumentException('Wechat api ticket expire time must be set.');
        }
        $this->_apiTicket[$type] = $ticket;
    }

    /**
     * 获取消息加密类
     *
     * @return MessageCrypter
     */
    public function getMessageCrypter()
    {
        return new MessageCrypter($this->token, $this->encodingAESKey, $this->appId);
    }

    /**
     * 解析Xml数据
     *
     * @param $xml
     *
     * @return mixed
     */
    public function parseXml($xml)
    {
        libxml_disable_entity_loader(true);
        $return = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return json_decode(json_encode($return, JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 请求微信服务器获取api ticket
     * 必须返回以下格式内容失败则返回false
     * ```php
     * array(
     *     'ticket => 'xxx',
     *     'expirs_in' => 7200
     * )
     * ```
     *
     * @param string $type api类型, 订阅号(jsapi, wx_card), 企业号(jsapi)
     *
     * @return array|bool
     */
    abstract protected function requestApiTicket($type);

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