<?php
namespace weikit\wechat\sdk;

use DOMText;
use DOMElement;
use DOMDocument;
use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\httpclient\Client;
use yii\web\HttpException;
use yii\helpers\ArrayHelper;
use yii\base\InvalidParamException;
use weikit\wechat\sdk\components\MessageCrypt;

require_once 'messageCrypt/wxBizMsgCrypt.php';

/**
 * Class BaseWechat
 * @package weikit\wechat\sdk
 */
abstract class BaseWechat extends Component
{
    /**
     * @var array 服务器访问令牌
     */
    private $_accessToken;

    /**
     * 请求微信服务器获取AccessToken
     * 必须返回以下格式内容失败则返回false
     * [
     *     'access_token => 'xxx',
     *     'expirs_in' => 7200
     * ]
     *
     * @return array|bool
     */
    abstract protected function requestAccessToken();

    /**
     * Access Token更新后事件
     */
    const EVENT_AFTER_ACCESS_TOKEN_UPDATE = 'afterAccessTokenUpdate';
    /**
     * 获取微信服务器访问令牌
     * 如本地无保存令牌或者令牌过期(强制获取)自动会从微信服务器获取最新令牌
     *
     * @param bool $force
     * @return mixed
     * @throws HttpException
     * @throws InvalidParamException
     */
    public function getAccessToken($force = false)
    {
        $time = time(); // 为了更精确控制.取当前时间计算
        if ($this->_accessToken === null || $this->_accessToken['expire'] < $time || $force) {
            $result = $this->_accessToken === null && !$force ? $this->getCache('access_token', false) : false;
            if ($result === false) {
                if (!($result = $this->requestAccessToken())) {
                    throw new HttpException(500, 'Fail to get access_token from wechat server. ' . $this->getErrorMessage());
                }
                $result['expires_in'] -= 15; // 15秒误差
                $result['expire'] = $time + $result['expires_in'];
                $this->trigger(self::EVENT_AFTER_ACCESS_TOKEN_UPDATE, new Event(['data' => $result]));
                $this->setCache('access_token', $result, $result['expires_in']);
            }
            $this->setAccessToken($result);
        }
        return $this->_accessToken['access_token'];
    }

    /**
     * 设置微信服务器访问令牌
     *
     * @param array $accessToken
     * @throws InvalidParamException
     */
    public function setAccessToken(array $accessToken)
    {
        if (!isset($accessToken['access_token'])) {
            throw new InvalidParamException('The access_token must be set.');
        } elseif(!isset($accessToken['expire'])) {
            throw new InvalidParamException('Wechat access_token expire time must be set.');
        }
        $this->_accessToken = $accessToken;
    }

    /**
     * 微信数据缓存基本键值
     *
     * @param $name
     * @return string
     */
    abstract protected function getCacheKey($name);
    /**
     * @var int 默认7200秒超时
     */
    public $cacheTime = 7200;
    /**
     * 缓存微信数据
     *
     * @param $name 缓存名称
     * @param $value 缓存数据
     * @param null $duration 缓存超时时间
     * @return bool
     */
    protected function setCache($name, $value, $duration = null)
    {
        $duration === null && $duration = $this->cacheTime;
        return Yii::$app->getCache()->set($this->getCacheKey($name), $value, $duration);
    }

    /**
     * 获取微信缓存数据
     *
     * @param $name 缓存名称
     * @return mixed
     */
    protected function getCache($name)
    {
        return Yii::$app->getCache()->get($this->getCacheKey($name));
    }

    /**
     * @var array clinet默认设置
     */
    public $clientConfig = [
        'class' => 'yii\httpclient\Client',
        'transport' => 'yii\httpclient\CurlTransport',
        'requestConfig' => [
            'options' => [
                'CONNECTTIMEOUT' => 60,
                'TIMEOUT' => 60,
                'USERAGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1',
                'SSL_VERIFYPEER' => false,
                'SSL_VERIFYHOST' => 0,
                'SSLVERSION' => CURL_SSLVERSION_TLSv1,
            ]
        ]
    ];
    /**
     * @var Client
     */
    private $_client;
    /**
     * @return Client
     * @throws \yii\base\InvalidConfigException
     */
    public function getClient()
    {
        if ($this->_client === null) {
            $this->setClient(Yii::createObject($this->clientConfig));
        }
        return $this->_client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * 创建消息加密类
     *
     * @return MessageCrypt
     */
    abstract protected function createMessageCrypt();

    /**
     * @var MessageCrypt
     */
    private $_messageCrypt;

    /**
     * 设置消息加密处理类
     *
     * @return MessageCrypt
     */
    public function getMessageCrypt()
    {
        if ($this->_messageCrypt === null) {
            $this->setMessageCrypt($this->createMessageCrypt());
        }
        return $this->_messageCrypt;
    }

    /**
     * 设置消息加密处理类
     *
     * @param MessageCrypt $messageCrypt
     */
    public function setMessageCrypt(MessageCrypt $messageCrypt)
    {
        $this->_messageCrypt = $messageCrypt;
    }

    /**
     * 创建微信格式的XML
     * @param array $data
     * @param null $charset
     * @return string
     */
    public function createXml(array $data, $charset = null)
    {
        $dom = new DOMDocument('1.0', $charset === null ? Yii::$app->charset : $charset);
        $root = new DOMElement('xml');
        $dom->appendChild($root);
        $this->buildXml($root, $data);
        $xml = $dom->saveXML();
        return trim(substr($xml, strpos($xml, '?>') + 2));
    }

    /**
     * @see yii\web\XmlResponseFormatter::buildXml()
     */
    protected function buildXml($element, $data)
    {
        if (is_object($data)) {
            $child = new DOMElement(StringHelper::basename(get_class($data)));
            $element->appendChild($child);
            if ($data instanceof Arrayable) {
                $this->buildXml($child, $data->toArray());
            } else {
                $array = [];
                foreach ($data as $name => $value) {
                    $array[$name] = $value;
                }
                $this->buildXml($child, $array);
            }
        } elseif (is_array($data)) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement(is_int($name) ? $this->itemTag : $name);
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = new DOMElement(is_int($name) ? $this->itemTag : $name);
                    $element->appendChild($child);
                    $child->appendChild(new DOMText((string) $value));
                }
            }
        } else {
            $element->appendChild(new DOMText((string) $data));
        }
    }

    /**
     * Get请求服务器
     *
     * @param $url
     * @param null|string|array|callable $data
     * @param array $headers
     * @param array $options
     * @return array|mixed
     */
    public function get($url, $data = null, $headers = [], $options = [])
    {
        return $this->http('get', $url, $data, $headers, $options);
    }

    /**
     * Post 请求服务器
     *
     * @param $url
     * @param null|string|array|callable $data
     * @param array $headers
     * @param array $options
     * @return array|mixed
     */
    public function post($url, $data = null, $headers = [], $options = [])
    {
        return $this->http('post', $url, $data, $headers, $options);
    }

    /**
     * Raw方式请求服务器
     *
     * @param $url
     * @param null|string|array|callable $data
     * @param array $headers
     * @param array $options
     * @return array|mixed
     */
    public function raw($url, $data = null, $headers = [], $options = [])
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $this->http('post', $url, $data, array_merge([
            'content-type' => 'application/json'
        ], $headers), $options);
    }

    /**
     * Api url 组装
     *
     * @param $url
     * @param array $options
     * @return string
     */
    protected function httpBuildQuery($url, array $options)
    {
        if (!empty($options)) {
            $url .= (stripos($url, '?') === null ? '&' : '?') . http_build_query($options);
        }
        return $url;
    }

    /**
     * @var \yii\httpclient\Response 最后的请求Response
     */
    public $lastResponse;
    /**
     * @var array 最后请求的错误信息
     */
    public $lastError;

    /**
     * 执行HTTP请求
     *
     * @param string $method
     * @param string $url
     * @param string|array|callable $data
     * @param $headers
     * @param $options
     * @param bool $force
     * @return array|mixed
     */
    protected function http($method, $url, $data, $headers, $options, $force = true)
    {
        if(is_array($url)) {
            $baseUrl = ArrayHelper::remove($url, 0);
            $url = $this->httpBuildQuery($baseUrl, $url);
        }

        $request = $this->getClient()
            ->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->addHeaders($headers)
            ->addOptions($options);
        if (is_array($data)) {
            $request->setData($data);
        } elseif (is_callable($data)) {
            call_user_func($data, $request);
        } else {
            $request->setContent($data);
        }
        $this->lastResponse = $request->send();
        $result = $this->lastResponse->getData();
        if (isset($result['errcode']) && $result['errcode']) { // 错误判定
            switch ($result['errcode']) {
                case 40001: //该错误为access_token过期失效错误, 自动强制重新获取一次access_token再重新请求
                    if ($force) {
                        $url = preg_replace_callback("/access_token=([^&]*)/i", function(){
                            return 'access_token=' . $this->getAccessToken(true);
                        }, $url);
                        $result = $this->http($method, $url, $data, $headers, $options, false); // 仅强制获取一次
                    }
                    break;
            }
            $this->lastError = $result;
            Yii::error([
                'result' => $result,
                'params' => [
                    'method' => $method,
                    'url' => $url,
                    'headers' => $headers,
                    'options' => $options
                ]
            ],  __METHOD__);
        }
        return $result;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return !empty($this->lastError) ? 'Error: #' . implode(': ', $this->lastError) : null;
    }
}