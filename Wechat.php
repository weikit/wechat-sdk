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
     * 微信官网基本地址
     */
    const WECHAT_WEB_URL = 'https://mp.weixin.qq.com';
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
     * @var string 消息体验证秘钥
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
            $return = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
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
        return sha1($tmpStr) == $signature;
    }

    /* =================== 基本操作 =================== */

    /**
     * 获取微信服务器IP地址
     */
    const WECHAT_IP_GET_PREFIX = 'cgi-bin/getcallbackip';
    /**
     * 获取微信服务器IP地址
     *
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getIp()
    {
        $result = $this->get([self::WECHAT_IP_GET_PREFIX, 'access_token' => $this->getAccessToken()]);
        return isset($result['ip_list']) ? $result['ip_list'] : false;
    }

    /* =================== 自定义菜单 =================== */

    /**
     * 创建菜单
     */
    const WECHAT_MENU_CREATE_PREFIX = 'cgi-bin/menu/create';
    /**
     * 创建菜单
     *
     * @param array $button 菜单结构字符串
     * ```php
     *  $this->createMenu([
     *      [
     *           'type' => 'click',
     *           'name' => '今日歌曲',
     *           'key' => 'V1001_TODAY_MUSIC'
     *      ],
     *      [
     *           'type' => 'view',
     *           'name' => '搜索',
     *           'url' => 'http://www.soso.com'
     *      ]
     *      ...
     * ]);
     * ```
     * @return bool
     */
    public function createMenu(array $button)
    {
        $result = $this->raw([self::WECHAT_MENU_CREATE_PREFIX, 'access_token' => $this->getAccessToken()], [
            'button' => $button
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取菜单列表
     */
    const WECHAT_MENU_GET_PREFIX = 'cgi-bin/menu/get';
    /**
     * 获取菜单列表
     *
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getMenu()
    {
        $result = $this->raw([self::WECHAT_MENU_GET_PREFIX, 'access_token' => $this->getAccessToken()]);
        return isset($result['menu']['button']) ? $result['menu']['button'] : false;
    }

    /**
     * 删除菜单
     */
    const WECHAT_MENU_DELETE_PREFIX = 'cgi-bin/menu/delete';
    /**
     * 删除菜单
     *
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteMenu()
    {
        $result = $this->get([self::WECHAT_MENU_DELETE_PREFIX, 'access_token' => $this->getAccessToken()]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 创建个性化菜单
     */
    const WECHAT_CONDITIONAL_MENU = 'cgi-bin/menu/addconditional';
    /**
     * 创建个性化菜单
     *
     * @param array $menu
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createConditionalMenu(array $menu)
    {
        $result = $this->raw([self::WECHAT_MENU_CREATE_PREFIX, 'access_token' => $this->getAccessToken()], $menu);
        return isset($result['menuid']) ? $result['menuid'] : false;
    }

    /**
     * 删除个性化菜单
     */
    const WECHAT_CONDITIONAL_MENU_DELETE_PREFIX = 'cgi-bin/menu/delconditional';
    /**
     * 删除个性化菜单
     *
     * @param $menuId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteConditionalMenu($menuId)
    {
        $result = $this->raw([
            self::WECHAT_CONDITIONAL_MENU_DELETE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'menuid' => $menuId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 测试个性化菜单匹配结果
     */
    const WECHAT_CONDITIONAL_MENU_MATCH_PREFIX = 'cgi-bin/menu/trymatch';
    /**
     * 测试个性化菜单匹配结果
     *
     * @param $userId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function matchConditionalMenu($userId)
    {
        $result = $this->raw([
            self::WECHAT_CONDITIONAL_MENU_MATCH_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'user_id' => $userId
        ]);
        return isset($result['button']) ? $result['button'] : false;
    }

    /**
     * 获取自定义菜单配置接口
     */
    const WECHAT_CURRENT_MENU_GET_PREFIX = 'cgi-bin/get_current_selfmenu_info';
    /**
     * 获取自定义菜单配置接口
     *
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getCurrentMenu()
    {
        $result = $this->get([self::WECHAT_CONDITIONAL_MENU_MATCH_PREFIX, 'access_token' => $this->getAccessToken()]);
        return isset($result['selfmenu_info']) ? $result : false;
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
        $result = $this->raw([
            self::WECHAT_CUSTOM_MESSAGE_SEND_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
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

    /**
     * 上传图文消息内的图片获取URL
     */
    const WECHAT_NEWS_IMAGE_UPLOAD_PREFIX = 'cgi-bin/media/uploadimg';
    /**
     * 上传图文消息内的图片获取URL
     *
     * @param $path
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function uploadNewsImage($path)
    {
        $result = $this->post([
            self::WECHAT_NEWS_IMAGE_UPLOAD_PREFIX,
            'access_token' => $this->getAccessToken()
        ], function($request) use ($path) {
            $request->addFile('media', $path);
        });
        return isset($result['url']) ? $result['url'] : false;
    }

    /**
     * 上传图文消息素材
     */
    const WECHAT_NEWS_UPLOAD_PREFIX = 'cgi-bin/media/uploadnews';

    /**
     * 上传图文消息素材
     *
     * @param array $articles
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function uploadNews(array $articles)
    {
        $result = $this->raw([self::WECHAT_NEWS_UPLOAD_PREFIX, 'access_token' => $this->getAccessToken()], [
            'articles' => $articles
        ]);
        return isset($result['media_id']) ? $result['media_id'] : false;
    }

    /**
     * 根据标签进行群发
     */
    const WECHAT_MASS_MESSAGE_SEND_BY_TAG_PREFIX = 'cgi-bin/message/mass/sendall';
    /**
     * 根据标签进行群发
     *
     * @param array $data
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function sendMassMessageByTag(array $data)
    {
        $result = $this->raw([
            self::WECHAT_MASS_MESSAGE_SEND_BY_TAG_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['msg_id']) ? $result : false;
    }
    /**
     * 根据OpenID列表群发
     */
    const WECHAT_MASS_MESSAGE_SEND_BY_OPEN_ID_PREFIX = 'cgi-bin/message/mass/send';
    /**
     * 根据OpenID列表群发
     *
     * @param array $data
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function sendMassMessageByOpenId(array $data)
    {
        $result = $this->raw([
            self::WECHAT_MASS_MESSAGE_SEND_BY_OPEN_ID_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['msg_id']) ? $result : false;
    }

    /**
     * 删除群发
     */
    const WECHAT_SENDED_MASS_MESSAGE_CANCEL_PREFIX = 'cgi-bin/message/mass/delete';
    /**
     * 删除群发
     *
     * @param $messageId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function cancelSendedMassMessage($messageId)
    {
        $result = $this->raw([
            self::WECHAT_SENDED_MASS_MESSAGE_CANCEL_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'msg_id' => $messageId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 预览接口
     */
    const WECHAT_MASS_MESSAGE_PREVIEW_PREFIX = 'cgi-bin/message/mass/preview';

    /**
     * 预览接口 (图文信息)
     * @param array $data
     * @return string|bool
     * @throws \yii\web\HttpException
     */
    public function previewMassMessage(array $data)
    {
        $result = $this->raw([
            self::WECHAT_MASS_MESSAGE_PREVIEW_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['msg_id']) ? $result['msg_id'] : false;
    }

    /**
     * 查询群发消息发送状态
     */
    const WECHAT_MASS_MESSAGE_STATUS_GET_PREFIX = 'cgi-bin/message/mass/get';
    /**
     * 查询群发消息发送状态
     *
     * @param $messageId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getMassMessageStatus($messageId)
    {
        $result = $this->raw([
            self::WECHAT_MASS_MESSAGE_STATUS_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'msg_id' => $messageId
        ]);
        return isset($result['msg_status']) ? $result['msg_status'] : false;
    }

    /**
     * 设置所属行业
     */
    const WECHAT_INDUSTRY_SET_PREFIX = 'cgi-bin/template/api_set_industry';
    /**
     * 设置所属行业
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setIndustry(array $data)
    {
        $result = $this->raw([
            self::WECHAT_INDUSTRY_SET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }
    /**
     * 获取设置的行业信息
     */
    const WECHAT_INDUSTRY_GET_PREFIX = 'cgi-bin/template/get_industry';
    /**
     * 获取设置的行业信息
     *
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getIndustry()
    {
        $result = $this->get([self::WECHAT_INDUSTRY_GET_PREFIX, 'access_token' => $this->getAccessToken()]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获得模板ID
     */
    const WECHAT_TEMPLATE_ID_GET_PREFIX = 'cgi-bin/template/api_add_template';
    /**
     * 获得模板ID
     *
     * @param $shortTemplateId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getTemplateId($shortTemplateId)
    {
        $result = $this->raw([
            self::WECHAT_INDUSTRY_SET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'template_id_short' => $shortTemplateId
        ]);
        return isset($result['template_id']) ? $result['template_id'] : false;
    }

    /**
     * 获取模板列表
     */
    const WECHAT_TEMPLATE_LIST_GET_PREFIX = 'cgi-bin/template/get_all_private_template';
    /**
     * 获取模板列表
     *
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getTemplates()
    {
        $result = $this->get([self::WECHAT_TEMPLATE_LIST_GET_PREFIX, 'access_token' => $this->getAccessToken()]);
        return isset($result['template_list']) ? $result['template_list'] : false;
    }

    /**
     * 删除模板
     */
    const WECHAT_TEMPLATE_DELETE_PREFIX = 'cgi-bin/template/del_private_template';
    /**
     * 删除模板
     *
     * @param $templateId
     * @return array|mixed
     * @throws \yii\web\HttpException
     */
    public function deleteTemplate($templateId)
    {
        $result = $this->raw([self::WECHAT_TEMPLATE_DELETE_PREFIX, 'access_token' => $this->getAccessToken()], [
            'template_id' => $templateId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 发送模板消息
     */
    const WECHAT_TEMPLATE_MESSAGE_SEND_PREFIX = 'cgi-bin/message/template/send';
    /**
     * 发送模板消息
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function sendTemplateMessage(array $data)
    {
        $result = $this->raw([
            self::WECHAT_TEMPLATE_MESSAGE_SEND_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['msgid']) ? $result['msgid'] : false;
    }

    /**
     * 获取公众号的自动回复规则
     */
    const WECHAT_AUTO_REPLY_INFO_GET_PREFIX = 'cgi-bin/get_current_autoreply_info';
    /**
     * 获取公众号的自动回复规则
     *
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getAutoReplyInfo()
    {
        $result = $this->get([
            self::WECHAT_AUTO_REPLY_INFO_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /* =================== 微信网页开发 =================== */

    /**
     * 用户同意授权，获取code
     */
    const WECHAT_OAUTH2_AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    /**
     * 用户同意授权，获取code:第一步
     * 通过此函数生成授权url
     *
     * @param $redirectUrl 授权后重定向的回调链接地址，请使用urlencode对链接进行处理
     * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     * snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @return string
     */
    public function getOauth2AuthorizeUrl($redirectUrl, $state = 'authorize', $scope = 'snsapi_base')
    {
        return $this->httpBuildQuery(self::WECHAT_OAUTH2_AUTHORIZE_URL, [
            'appid' => $this->appId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ]) . '#wechat_redirect';
    }

    /**
     * 通过code换取网页授权access_token
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_PREFIX = '/sns/oauth2/access_token';
    /**
     * 通过code换取网页授权access_token:第二步
     * 通过跳转到getOauth2AuthorizeUrl返回的授权code获取用户资料 (该函数和getAccessToken函数作用不同.请参考文档)
     * @param $code
     * @param string $grantType
     * @return array
     */
    public function getOauth2AccessToken($code, $grantType = 'authorization_code')
    {
        $result = $this->get([self::WECHAT_OAUTH2_ACCESS_TOKEN_PREFIX,
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $code,
            'grant_type' => $grantType
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 刷新access_token
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_REFRESH_PREFIX = '/sns/oauth2/refresh_token';
    /**
     * 刷新access_token:第三步(非必须)
     * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新
     * refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。
     *
     * @param $refreshToken
     * @param string $grantType
     * @return array|bool
     */
    public function refreshOauth2AccessToken($refreshToken, $grantType = 'refresh_token')
    {
        $result = $this->get([self::WECHAT_OAUTH2_ACCESS_TOKEN_REFRESH_PREFIX,
            'appid' => $this->appId,
            'grant_type' => $grantType,
            'refresh_token' => $refreshToken
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     */
    const WEHCAT_SNS_USER_INFO_PREFIX = '/sns/userinfo';
    /**
     * 拉取用户信息(需scope为 snsapi_userinfo):第四步
     * @param $openId
     * @param string $oauth2AccessToken
     * @param string $lang
     * @return array|bool
     */
    public function getSnsUserInfo($openId, $oauth2AccessToken, $lang = 'zh_CN')
    {
        $result = $this->get([self::WEHCAT_SNS_USER_INFO_PREFIX,
            'access_token' => $oauth2AccessToken,
            'openid' => $openId,
            'lang' => $lang
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 检验授权凭证（access_token）是否有效
     */
    const WECHAT_SNS_AUTH_PREFIX = '/sns/auth';
    /**
     * 检验授权凭证（access_token）是否有效
     * @param $accessToken
     * @param $openId
     * @return bool
     */
    public function checkOauth2AccessToken($accessToken, $openId)
    {
        $result = $this->get([self::WECHAT_SNS_AUTH_PREFIX,
            'access_token' => $accessToken,
            'openid' => $openId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /* =================== 素材管理 =================== */

    /**
     * 新增临时素材(上传临时多媒体文件)
     */
    const WECHAT_MEDIA_UPLOAD_PREFIX = '/cgi-bin/media/upload';
    /**
     * 新增临时素材(上传临时多媒体文件)
     *
     * @param $path
     * @param $type
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function uploadMedia($path, $type)
    {
        $result = $this->post([self::WECHAT_MEDIA_UPLOAD_PREFIX,
            'access_token' => $this->getAccessToken(),
            'type' => $type
        ], function($request) use ($path) {
            $request->addFile('media', $path);
        });
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 获取临时素材(下载多媒体文件)
     */
    const WECHAT_MEDIA_GET_PREFIX = '/cgi-bin/media/get';
    /**
     * 获取临时素材(下载多媒体文件)
     * @param $mediaId
     * @return bool|string
     * @throws \yii\web\HttpException
     */
    public function getMedia($mediaId)
    {
        $result = $this->get([self::WECHAT_MEDIA_GET_PREFIX,
            'access_token' => $this->getAccessToken(),
            'media_id' => $mediaId
        ]);
        return is_string($result) ? $result : false;
    }

    /**
     * 新增永久图文素材
     */
    const WECHAT_NEWS_MATERIAL_ADD_PREFIX = '/cgi-bin/material/add_news';
    /**
     * 新增永久图文素材
     * @param array $articles
     * @return string|bool
     * @throws \yii\web\HttpException
     */
    public function addNewsMaterial(array $articles)
    {
        $result = $this->raw([
            self::WECHAT_NEWS_MATERIAL_ADD_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'articles' => $articles
        ]);
        return isset($result['media_id']) ? $result['media_id'] : false;
    }

    /**
     * 新增其他类型永久素材
     */
    const WECHAT_MATERIAL_ADD_PREFIX = '/cgi-bin/material/add_material';
    /**
     * 新增其他类型永久素材
     *
     * @param string $path
     * @param string $type
     * @param array $data 视频素材需要description
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function addMaterial($path, $type, $data = [])
    {
        $result = $this->post([self::WECHAT_MATERIAL_ADD_PREFIX,
            'access_token' => $this->getAccessToken(),
            'type' => $type
        ], function($request) use ($path, $data) {
            if ($data !== []) {
                $request->setData($data);
            }
            $request->addFile('media', $path);
        });
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 获取永久素材
     */
    const WECHAT_MATERIAL_GET_PREFIX = '/cgi-bin/material/get_material';
    /**
     * 获取永久素材
     *
     * @param $mediaId
     * @return bool|string
     * @throws \yii\web\HttpException
     */
    public function getMaterial($mediaId)
    {
        $result = $this->get([self::WECHAT_MATERIAL_GET_PREFIX,
            'access_token' => $this->getAccessToken(),
            'media_id' => $mediaId
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 删除永久素材
     */
    const WECHAT_MATERIAL_DELETE_PREFIX = '/cgi-bin/material/del_material';
    /**
     * 删除永久素材
     * @param $mediaId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteMaterial($mediaId)
    {
        $result = $this->raw([
            self::WECHAT_MATERIAL_DELETE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'media_id' => $mediaId
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 修改永久图文素材
     */
    const WECHAT_NEWS_MATERIAL_UPDATE_PREFIX = '/cgi-bin/material/update_news';
    /**
     * 修改永久图文素材
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateNewsMaterial(array $data)
    {
        $result = $this->raw([
            self::WECHAT_NEWS_MATERIAL_UPDATE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 获取素材总数
     */
    const WECHAT_MATERIAL_COUNT_GET_PREFIX = '/cgi-bin/material/get_materialcount';
    /**
     * 获取素材总数
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getMaterialCount()
    {
        $result = $this->get([
            self::WECHAT_MATERIAL_COUNT_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 获取素材列表
     */
    const WECHAT_MATERIAL_LIST_GET_PREFIX = '/cgi-bin/material/batchget_material';
    /**
     * 获取素材列表
     * @param $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getMaterials($data)
    {
        $result = $this->raw([
            self::WECHAT_MATERIAL_LIST_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return !isset($result['errodcode']) ? $result : false;
    }

    /* =================== 用户管理 =================== */

    /**
     * 创建标签(用户标签管理)
     */
    const WECHAT_TAR_CREATE_PREFIX = 'cgi-bin/tags/create';

    /**
     * 创建标签(用户标签管理)
     *
     * @param array $tag
     * @return bool|array
     * @throws \yii\web\HttpException
     */
    public function createTag(array $tag)
    {
        $result = $this->raw([
            self::WECHAT_TARS_CREATE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'tag' => $tag
        ]);
        return isset($result['tag']) ? $result['tag'] : false;
    }

    /**
     * 获取公众号已创建的标签
     */
    const WECHAT_TAGS_GET_PREFIX = 'cgi-bin/tags/get';
    /**
     * 获取公众号已创建的标签
     *
     * @return bool|array
     * @throws \yii\web\HttpException
     */
    public function getTags()
    {
        $result = $this->get([
            self::WECHAT_TAGS_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['tags']) ? $result['tags'] : false;
    }

    /**
     * 编辑标签
     */
    const WECHAT_TAG_UPDATE_PREFIX = 'cgi-bin/tags/update';
    /**
     * 编辑标签
     *
     * @param array $tag
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateTag(array $tag)
    {
        $result = $this->raw([
            self::WECHAT_TAG_UPDATE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'tag' => $tag
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 删除标签
     */
    const WECHAT_TAG_DELETE_PREFIX = 'cgi-bin/tags/delete';
    /**
     * 删除标签
     *
     * @param array $tag
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteTag(array $tag)
    {
        $result = $this->raw([
            self::WECHAT_TAG_DELETE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'tag' => $tag
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取标签下粉丝列表
     */
    const WECHAT_USERS_GET_BY_TAG_PREFIX = 'cgi-bin/user/tag/get';

    /**
     * 获取标签下粉丝列表
     *
     * @param array $data
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getUsersByTag(array $data)
    {
        $result = $this->raw([
            self::WECHAT_USERS_GET_BY_TAG_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['data']) ? $result : false;
    }

    /**
     * 批量为用户打标签
     */
    const WECHAT_USERS_TAG_SET_PREFIX = 'cgi-bin/tags/members/batchtagging';
    /**
     * 批量为用户打标签
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setUsersTag(array $data)
    {
        $result = $this->raw([
            self::WECHAT_USERS_TAG_SET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 批量为用户取消标签
     */
    const WECHAT_USERS_TAG_REMOVE_PREFIX = 'cgi-bin/tags/members/batchuntagging';
    /**
     * 批量为用户取消标签
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function removeUsersTag(array $data)
    {
        $result = $this->raw([
            self::WECHAT_USERS_TAG_REMOVE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取用户身上的标签列表
     */
    const WECHAT_USER_TAGS_GET_PREFIX = 'cgi-bin/tags/getidlist';

    /**
     * 获取用户身上的标签列表
     *
     * @param $openId
     * @return bool|array
     * @throws \yii\web\HttpException
     */
    public function getUserTags($openId)
    {
        $result = $this->raw([
            self::WECHAT_USERS_TAG_REMOVE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'openid' => $openId
        ]);
        return isset($result['tagid_list']) ? $result['tagid_list'] : false;
    }

    /**
     * 设置用户备注名
     */
    const WECHAT_USER_MARK_SET_PREFIX = 'cgi-bin/user/info/updateremark';
    /**
     * 设置用户备注名
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setUserMark(array $data)
    {
        $result = $this->raw([
            self::WECHAT_USERS_TAG_REMOVE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取用户基本信息
     */
    const WECHAT_USER_INFO_GET_PREFIX = 'cgi-bin/user/info';
    /**
     * 获取用户基本信息
     *
     * @param string $openId
     * @param string $lang
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getUserInfo($openId, $lang = 'zh_CN')
    {
        $result = $this->get([
            self::WECHAT_USERS_TAG_REMOVE_PREFIX,
            'access_token' => $this->getAccessToken(),
            'openid' => $openId,
            'lang' => $lang
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 批量获取用户基本信息
     */
    const WECHAT_USERS_INFO_GET_PREFIX = 'cgi-bin/user/info/batchget';
    /**
     * 批量获取用户基本信息
     *
     * @param array $users
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUsersInfo(array $users)
    {
        $result = $this->raw([
            self::WECHAT_USERS_INFO_GET_PREFIX,
            'access_token' => $this->getAccessToken(),
        ], [
            'user_list' => $users
        ]);
        return isset($result['user_info_list']) ? $result['user_info_list'] : false;
    }

    /**
     * 获取用户列表
     */
    const WECHAT_USERS_GET_PREFIX = 'cgi-bin/user/get';
    /**
     * 获取用户列表
     *
     * @param $nextOpenId
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getUsers($nextOpenId)
    {
        $result = $this->get([
            self::WECHAT_USERS_INFO_GET_PREFIX,
            'access_token' => $this->getAccessToken(),
            'next_openid' => $nextOpenId
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /* =================== 账号管理 =================== */

    /**
     * 生成带参数的二维码
     */
    const WECHAT_QRCODE_CREATE_PREFIX = 'cgi-bin/qrcode/create';
    /**
     * 生成带参数的二维码
     *
     * @param array $data
     * @return array|bool|mixed
     * @throws \yii\web\HttpException
     */
    public function createQrcode(array $data)
    {
        $result = $this->raw([self::WECHAT_QRCODE_CREATE_PREFIX, 'access_token' => $this->getAccessToken()], $data);
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
    public function getQrcodeUrl($ticket)
    {
        return $this->httpBuildQuery(self::WECHAT_QRCODE_GET_URL, ['ticket' => $ticket]);
    }

    /**
     * 长链接转短链接接口
     */
    const WECHAT_SHORT_URL_GET_PREFIX = 'cgi-bin/shorturl';
    /**
     * 长链接转短链接接口
     *
     * @param $url
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getShortUrl($url)
    {
        $result = $this->raw([
            self::WECHAT_SHORT_URL_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], [
            'action' => 'long2short',
            'long_url' => $url
        ]);
        return isset($result['short_url']) ? $result['short_url'] : false;
    }

    /* =================== 数据统计 =================== */

    /**
     * 获取用户增减数据
     */
    const WECHAT_DATA_CUBE_USER_SUMMARY_GET_PREFIX = '/datacube/getusersummary';
    /**
     * 获取用户增减数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserSummary(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_USER_SUMMARY_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取累计用户数据
     */
    const WECHAT_DATA_CUBE_USER_CUMULATE_GET_PREFIX = '/datacube/getusercumulate';
    /**
     * 获取累计用户数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserCumulate(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_USER_CUMULATE_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文群发每日数据
     */
    const WECHAT_DATA_CUBE_NEWS_SUMMARY_GET_PREFIX = '/datacube/getarticlesummary';
    /**
     * 获取图文群发每日数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getNewsSummary(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_NEWS_SUMMARY_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文群发总数据
     */
    const WECHAT_DATA_CUBE_NEWS_TOTAL_GET_PREFIX = '/datacube/getarticletotal';
    /**
     * 获取图文群发总数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getNewsTotal(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_NEWS_TOTAL_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文统计数据
     */
    const WECHAT_DATA_CUBE_USER_READ_GET_PREFIX = '/datacube/getuserread';
    /**
     * 获取图文统计数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserRead(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_USER_READ_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文统计分时数据
     */
    const WECHAT_DATA_CUBE_USER_READ_HOUR_GET_PREFIX = '/datacube/getuserreadhour';
    /**
     * 获取图文统计分时数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserReadHour(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_USER_READ_HOUR_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文分享转发数据
     */
    const WECHAT_DATA_CUBE_USER_SHARE_GET_PREFIX = '/datacube/getusershare';
    /**
     * 获取图文分享转发数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserShare(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_USER_SHARE_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文分享转发分时数据
     */
    const WECHAT_DATA_CUBE_USER_SHARE_HOUR_GET_PREFIX = '/datacube/getusersharehour';
    /**
     * 获取图文分享转发分时数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserShareUour(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_USER_SHARE_HOUR_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送概况数据
     */
    const WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_GET_PREFIX = '/datacube/getupstreammsg';
    /**
     * 获取消息发送概况数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessage(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息分送分时数据
     */
    const WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_HOUR_GET_PREFIX = '/datacube/getupstreammsghour';
    /**
     * 获取消息分送分时数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageHour(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_HOUR_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送周数据
     */
    const WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_WEEK_GET_PREFIX = '/datacube/getupstreammsgweek';
    /**
     * 获取消息发送周数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageWeek(array $data)
    {
        $result = $this->raw([
            self::WECHAT_UP_STREAM_MESSAGE_WEEK_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送月数据
     */
    const WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_MONTH_GET_PREFIX = '/datacube/getupstreammsgmonth';
    /**
     * 获取消息发送月数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageMonth(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_MONTH_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布数据
     */
    const WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_DIST_GET_PREFIX = '/datacube/getupstreammsgdist';
    /**
     * 获取消息发送分布数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageDist(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_DIST_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布周数据
     */
    const WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_DIST_WEEK_GET_PREFIX = '/datacube/getupstreammsgdistweek';
    /**
     * 获取消息发送分布周数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageDistWeek(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_DIST_WEEK_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布月数据
     */
    const WECHAT_DATA_CUBE_UP_STREAM_MESSAGE_DIST_MONTH_GET_PREFIX = '/datacube/getupstreammsgdistmonth';
    /**
     * 获取消息发送分布月数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageDistMonth(array $data)
    {
        $result = $this->raw([
            self::WECHAT_UP_STREAM_MESSAGE_DIST_MONTH_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取接口分析数据
     */
    const WECHAT_DATA_CUBE_INTERFACE_SUMMARY_GET_PREFIX = '/datacube/getinterfacesummary';
    /**
     * 获取接口分析数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getInterfaceSummary(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_INTERFACE_SUMMARY_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取接口分析分时数据
     */
    const WECHAT_DATA_CUBE_INTERFACE_SUMMARY_HOUR_GET_PREFIX = '/datacube/getinterfacesummaryhour';
    /**
     * 获取接口分析分时数据
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getInterfaceSummaryHour(array $data)
    {
        $result = $this->raw([
            self::WECHAT_DATA_CUBE_INTERFACE_SUMMARY_HOUR_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['list']) ? $result['list'] : false;
    }

    /* =================== 微信卡券 =================== */
    /* =================== 微信门店 =================== */
    /* =================== 微信小店 =================== */
    /* =================== 微信设备功能 =================== */

    /* =================== 多客服功能 =================== */

    /**
     * 添加客服帐号
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_ADD_PREFIX = 'customservice/kfaccount/add';
    /**
     * 添加客服帐号
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addCustomServiceAccount(array $data)
    {
        $result = $this->raw([
            self::WECHAT_CUSTOM_SERVICE_ACCOUNT_ADD_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 删除客服帐号
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_DELETE_PREFIX = 'customservice/kfaccount/del';
    /**
     * 删除客服帐号
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteCustomServiceAccount(array $data)
    {
        $result = $this->raw([
            self::WECHAT_CUSTOM_SERVICE_ACCOUNT_DELETE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 设置客服帐号的头像
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_AVATAR_UPLOAD_PREFIX = 'customservice/kfaccount/uploadheadimg';
    /**
     * 设置客服帐号的头像
     *
     * @param $path
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function uploadCustomServiceAvatar($path)
    {
        $result = $this->post([
            self::WECHAT_CUSTOM_SERVICE_ACCOUNT_AVATAR_UPLOAD_PREFIX,
            'access_token' => $this->getAccessToken()
        ], function($request) use ($path) {
            $request->addFile('media', $path);
        });
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取所有客服账号(获取客服基本信息)
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_LIST_PREFIX = 'cgi-bin/customservice/getkflist';
    /**
     * 获取所有客服账号
     *
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getCustomServiceAccounts()
    {
        $result = $this->get([
            self::WECHAT_CUSTOM_SERVICE_ACCOUNT_LIST_PREFIX,
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['kf_list']) ? $result['kf_list'] : false;
    }

    /**
     * 获取客服基本信息
     */
    const WECHAT_ONLINE_CUSTOM_SERVICES_PREFIX = 'cgi-bin/customservice/getonlinekflist';
    /**
     * 获取客服基本信息
     *
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getOnlineCustomServices()
    {
        $result = $this->get([
            self::WECHAT_ONLINE_CUSTOM_SERVICES_PREFIX,
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['kf_online_list']) ? $result['kf_online_list'] : false;
    }

    /**
     * 邀请绑定客服帐号
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_PREFIX = 'customservice/kfaccount/inviteworker';
    /**
     * 邀请绑定客服帐号
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function inviteCustomServiceAccount(array $data)
    {
        $result = $this->raw([
            self::WECHAT_CUSTOM_SERVICE_ACCOUNT_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 设置客服信息
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_UPDATE_PREFIX = 'customservice/kfaccount/update';
    /**
     * 设置客服信息
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateCustomServiceAccount(array $data)
    {
        $result = $this->raw([
            self::WECHAT_CUSTOM_SERVICE_ACCOUNT_UPDATE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 创建会话
     */
    const WECHAT_CUSOM_SERVICE_SESSION_CREATE_PREFIX = 'customservice/kfsession/create';
    /**
     * 创建会话
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createCustomServiceSession(array $data)
    {
        $result = $this->raw([
            self::WECHAT_CUSTOM_SERVICE_ACCOUNT_UPDATE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 创建会话
     */
    const WECHAT_CUSOM_SERVICE_SESSION_CLOSE_PREFIX = 'customservice/kfsession/close';
    /**
     * 创建会话
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function closeCustomServiceSession(array $data)
    {
        $result = $this->raw([
            self::WECHAT_CUSOM_SERVICE_SESSION_CLOSE_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取客户会话状态
     */
    const WECHAT_CUSTOM_SERVICE_SERSSION_GET_PREFIX = 'customservice/kfsession/getsession';
    /**
     * 获取客户会话状态
     *
     * @param $openId
     * @return array|bool
     * @throws \yii\web\HttpException
     */
    public function getCustomServiceSession($openId)
    {
        $result = $this->get([
            self::WECHAT_CUSTOM_SERVICE_SERSSION_GET_PREFIX,
            'access_token' => $this->getAccessToken(),
            'openid' => $openId
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 获取未接入会话列表
     */
    const WECHAT_CUSTOM_SERVICE_LIST_WAITING_PREFIX = 'customservice/kfsession/getwaitcase';
    /**
     * 获取未接入会话列表
     *
     * @return bool|array
     * @throws \yii\web\HttpException
     */
    public function getWaitingCustomServices()
    {
        $result = $this->get([
            self::WECHAT_CUSTOM_SERVICE_LIST_WAITING_PREFIX,
            'access_token' => $this->getAccessToken(),
        ]);
        return isset($result['waitcaselist']) ? $result : false;
    }

    /**
     * 获取聊天记录
     */
    const WECHAT_CUSTOM_SERVICE_RECORD_GET_PREFIX = 'customservice/msgrecord/getrecord';
    /**
     * 获取聊天记录
     *
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getCustomServiceRecord(array $data)
    {
        $result = $this->raw([
            self::WECHAT_CUSTOM_SERVICE_RECORD_GET_PREFIX,
            'access_token' => $this->getAccessToken()
        ], $data);
        return isset($result['recordlist']) ? $result['recordlist'] : false;
    }

    /* =================== 微信摇一摇周边 =================== */
    /* =================== 微信连WIFI =================== */
    /* =================== 微信扫一扫 =================== */
}