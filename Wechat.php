<?php
namespace weikit\wechat\sdk;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Class Wechat
 * @package weikit\wechat\sdk
 */
class Wechat extends BaseWechat
{
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://api.weixin.qq.com';
    /**
     * @var string 公众号appId
     */
    public $appId;
    /**
     * @var string 公众号appSecret
     */
    public $appSecret;
    /**
     * @var string 公众号接口验证令牌,可自由设定. 并填写在微信公众平台->开发者中心
     */
    public $token;
    /**
     * @var 消息体验证秘钥
     */
    public $encodingAesKey;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->appId === null) {
            throw new InvalidConfigException('The "appId" property must be set.');
        } elseif ($this->appSecret === null) {
            throw new InvalidConfigException('The "appSecret" property must be set.');
        } elseif ($this->token === null) {
            throw new InvalidConfigException('The "token" property must be set.');
        }

        $this->clientConfig = array_merge([
            'baseUrl' => static::WECHAT_BASE_URL
        ], $this->clientConfig);
    }

    /**
     * @inheritdoc
     */
    public function getCacheKey($name)
    {
        return 'cache_wechat_sdk_' . $this->appId . '_' . $name;
    }
    
    /* =================== 基本接口 =================== */

    /**
     * access token获取
     */
    const WECHAT_ACCESS_TOKEN_PREFIX = 'cgi-bin/token';
    /**
     * 请求微信服务器获取访问令牌
     *
     * @param string $grantType
     * @return array|bool
     */
    protected function requestAccessToken($grantType = 'client_credential')
    {
        $result = $this->get(self::WECHAT_ACCESS_TOKEN_PREFIX, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => $grantType
        ]);
        return isset($result['access_token']) ? $result : false;
    }

    /**
     * @inheritdoc
     */
    protected function createMessageCrypt()
    {
        return Yii::createObject(MessageCrypt::className(), [
            $this->token,
            $this->encodingAesKey,
            $this->appId
        ]);
    }

    /**
     * 解析微信请求内容
     *
     * @param null $data
     * @param null $messageSignature
     * @param null $timestamp
     * @param null $nonce
     * @param null $encryptType
     * @return array
     */
    public function parseXml($data = null, $messageSignature = null, $timestamp = null , $nonce = null, $encryptType = null)
    {
        $data === null && $data = Yii::$app->request->getRawBody();
        $return = [];
        if (!empty($data)) {
            $messageSignature === null && isset($_GET['msg_signature']) && $messageSignature = $_GET['msg_signature'];
            $encryptType === null && isset($_GET['encrypt_type']) && $encryptType = $_GET['encrypt_type'];
            if ($messageSignature !== null && $encryptType == 'aes') { // 自动解密
                $timestamp === null && isset($_GET['timestamp']) && $timestamp = $_GET['timestamp'];
                $nonce === null && isset($_GET['nonce']) && $nonce = $_GET['nonce'];
                $data = $this->decryptXml($data, $messageSignature, $timestamp, $nonce);
                if ($data === false) {
                    return $return;
                }
            }
            libxml_disable_entity_loader(true);
            $return = (array) simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $return;
    }

    /**
     * 微信服务器请求签名验证
     *
     * @param string $signature 微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
     * @param string $timestamp 时间戳
     * @param string $nonce 随机数
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
        $a = sha1($tmpStr);
        return sha1($tmpStr) == $signature;
    }

    /* =================== 消息管理 =================== */

    /**
     * 发送客服消息
     */
    const WECHAT_CUSTOM_MESSAGE_SEND_PREFIX = 'cgi-bin/message/custom/send';
    /**
     * 发送客服消息
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function sendMessage(array $data)
    {
        $result = $this->raw([self::WECHAT_CUSTOM_MESSAGE_SEND_PREFIX, 'access_token' => $this->getAccessToken()], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 发送文本消息
     *
     * @param $toUser
     * @param $content
     * @param null $account
     * @return bool
     */
    public function sendText($toUser, $content, $account = null)
    {
        $data = [
            'touser' => $toUser,
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]
        ];
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->sendMessage($data);
    }

    /**
     * 发送图片消息
     *
     * @param $toUser
     * @param $mediaId
     * @param null $account
     * @return bool
     */
    public function sendImage($toUser, $mediaId, $account = null)
    {
        $data = [
            'touser' => $toUser,
            'msgtype' => 'image',
            'image' => [
                'media_id' => $mediaId
            ]
        ];
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->sendMessage($data);
    }

    /**
     * 发送语音消息
     *
     * @param $toUser
     * @param $mediaId
     * @param null $account
     * @return bool
     */
    public function sendVoice($toUser, $mediaId, $account = null)
    {
        $data = [
            'touser' => $toUser,
            'msgtype' => 'voice',
            'voice' => [
                'media_id' => $mediaId
            ]
        ];
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->sendMessage($data);
    }

    /**
     * 发送视频消息
     *
     * @param $toUser
     * @param array $video
     * @param null $account
     * @return bool
     */
    public function sendVideo($toUser, array $video, $account = null)
    {
        $data = [
            'touser' => $toUser,
            'msgtype' => 'video',
            'video' => $video
        ];
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->sendMessage($data);
    }

    /**
     * 发送音乐消息
     *
     * @param $toUser
     * @param array $music
     * @param null $account
     * @return bool
     */
    public function sendMusic($toUser, array $music, $account = null)
    {
        $data = [
            'touser' => $toUser,
            'msgtype' => 'music',
            'music' => $music
        ];
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->sendMessage($data);
    }

    /**
     * 发送图文消息 (限制8条)
     *
     * @param $toUser
     * @param string|array $news 字符串则为微信图文消息页面,数组则为外链图文
     * @param null $account
     * @return bool
     */
    public function sendNews($toUser, $news, $account = null)
    {
        if (is_array($news)) {
            $data = [
                'touser' => $toUser,
                'msgtype' => "news",
                'news' => [
                    'articles' => $news
                ]
            ];
        } else {
            $data = [
                'touser' => $toUser,
                'msgtype' => "mpnews",
                'mpnews' => [
                    'media_id' => $news
                ]
            ];
        }
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->sendMessage($data);
    }

    /**
     * 发送卡卷消息
     *
     * @param $toUser
     * @param array $card
     * @param null $account
     * @return bool
     */
    public function sendCard($toUser, array $card, $account = null)
    {
        $data = [
            'touser' => $toUser,
            'msgtype' => 'wxcard',
            'wxcard' => $card
        ];
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->sendMessage($data);
    }
}