<?php

namespace Weikit\Wechat\Sdk\Components;

/**
 * 第三方平台授权
 * @package Weikit\Wechat\Sdk\Components
 */
class Authorization
{

    /* ==== 第三方强授权相关接口 ==== */
    /**
     * 使用授权码换取公众号的授权信息
     */
    const WECHAT_AUTHORIZATION_INFO_TOKEN_GET_PREFIX = 'cgi-bin/component/api_query_auth';

    /**
     * 使用授权码换取公众号的授权信息
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getAuthorizationByToken(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_AUTHORIZATION_INFO_TOKEN_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['authorization_info']) ? $result['authorization_info'] : false;
    }

    /**
     * 获取授权方的账户信息
     */
    const WECHAT_AUTHORIZER_INFO_TOKEN_GET_PREFIX = 'cgi-bin/component/api_get_authorizer_info';

    /**
     * 获取授权方的账户信息
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getAuthorizer(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_AUTHORIZER_INFO_TOKEN_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['authorizer_info']) ? $result : false;
    }

    /**
     * 确认授权
     */
    const WECHAT_AUTHORIZATION_CONFIRM_PREFIX = 'cgi-bin/component/api_confirm_authorization';

    /**
     * 确认授权
     *
     * @param array $data
     *
     * @return bool
     */
    public function confirmAuthorization(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_AUTHORIZATION_INFO_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['errcode']) && ! $result['code'];
    }
}