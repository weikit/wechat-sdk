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
     * @see Weikit\Wechat\Sdk\Components\Menu
     * /

    /* =================== 消息管理 =================== */


    /**
     * 上传图文消息内的图片获取URL
     */
    const WECHAT_NEWS_IMAGE_UPLOAD_PREFIX = 'cgi-bin/media/uploadimg';
    /**
     * 上传图文消息内的图片获取URL
     *
     * @param $path
     * @return bool
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