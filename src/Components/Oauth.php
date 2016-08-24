<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 微信网页授权
 * @package Weikit\Wechat\Sdk\Components
 */
class Oauth extends BaseComponent
{
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
    public function getAuthorizeUrl($redirectUrl, $state = 'authorize', $scope = 'snsapi_base')
    {
        return $this->getRequest()
            ->buildUrl(array(
                self::WECHAT_OAUTH2_AUTHORIZE_URL,
                'appid' => $this->wechat->appId,
                'redirect_uri' => $redirectUrl,
                'response_type' => 'code',
                'scope' => $scope,
                'state' => $state,
            )) . '#wechat_redirect';
    }

    /**
     * 通过code换取网页授权access_token
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_PREFIX = '/sns/oauth2/access_token';
    /**
     * 通过code换取网页授权access_token:第二步
     * 通过跳转到getAuthorizeUrl返回的授权code获取用户资料 (该函数和getAccessToken函数作用不同.请参考文档)
     * @param $code
     * @param string $grantType
     * @return array
     */
    public function getAccessToken($code, $grantType = 'authorization_code')
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_OAUTH2_ACCESS_TOKEN_PREFIX,
                'appid' => $this->appId,
                'secret' => $this->appSecret,
                'code' => $code,
                'grant_type' => $grantType
            ));
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
    public function refreshAccessToken($refreshToken, $grantType = 'refresh_token')
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_OAUTH2_ACCESS_TOKEN_REFRESH_PREFIX,
                'appid' => $this->appId,
                'grant_type' => $grantType,
                'refresh_token' => $refreshToken
            ));
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
    public function getUser($openId, $oauth2AccessToken, $lang = 'zh_CN')
    {
        $result = $this->getRequest()
            ->get(array(
                self::WEHCAT_SNS_USER_INFO_PREFIX,
                'access_token' => $oauth2AccessToken,
                'openid' => $openId,
                'lang' => $lang
            ));
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 检验授权凭证（access_token）是否有效
     */
    const WECHAT_SNS_AUTH_PREFIX = '/sns/auth';
    /**
     * 检验授权凭证（access_token）是否有效
     *
     * @param $accessToken
     * @param $openId
     * @return bool
     */
    public function checkAccessToken($accessToken, $openId)
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_SNS_AUTH_PREFIX,
                'access_token' => $accessToken,
                'openid' => $openId
            ));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

}