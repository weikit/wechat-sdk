<?php

namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 新版客服功能
 * @package Weikit\Wechat\Sdk\Components
 */
class CustomerService extends BaseComponent
{
    /**
     * 添加客服帐号
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_ADD_PREFIX = 'customservice/kfaccount/add';

    /**
     * 添加客服帐号
     *
     * @param array $data
     *
     * @return bool
     */
    public function create(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_CUSTOM_SERVICE_ACCOUNT_ADD_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool
     */
    public function delete(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_CUSTOM_SERVICE_ACCOUNT_DELETE_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool
     */
    public function setAvatar($path)
    {
        $result = $this->getRequest()
                       ->upload([
                           self::WECHAT_CUSTOM_SERVICE_ACCOUNT_AVATAR_UPLOAD_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'media' => $path,
                       ]);

        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取所有客服账号(获取客服基本信息)
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_LIST_PREFIX = 'cgi-bin/customservice/getkflist';

    /**
     * 获取所有客服账号
     *
     * @return bool|array
     */
    public function lists()
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_CUSTOM_SERVICE_ACCOUNT_LIST_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ]);

        return isset($result['kf_list']) ? $result['kf_list'] : false;
    }

    /**
     * 获取客服基本信息
     */
    const WECHAT_CUSTOM_SERVICE_ONLINE_GET_PREFIX = 'cgi-bin/customservice/getonlinekflist';

    /**
     * 获取客服基本信息
     *
     * @return bool|array
     */
    public function getOnlines()
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_CUSTOM_SERVICE_ONLINE_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool
     */
    public function invite(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_CUSTOM_SERVICE_ACCOUNT_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool
     */
    public function update(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_CUSTOM_SERVICE_ACCOUNT_UPDATE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 创建会话
     */
    const WECHAT_CUSTOM_SERVICE_SESSION_CREATE_PREFIX = 'customservice/kfsession/create';

    /**
     * 创建会话
     *
     * @param array $data
     *
     * @return bool
     */
    public function createSession(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_CUSTOM_SERVICE_SESSION_CREATE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 关闭会话
     */
    const WECHAT_CUSTOM_SERVICE_SESSION_CLOSE_PREFIX = 'customservice/kfsession/close';

    /**
     * 关闭会话
     *
     * @param array $data
     *
     * @return bool
     */
    public function closeSession(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_CUSTOM_SERVICE_SESSION_CLOSE_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return array|bool
     */
    public function getSession($openId)
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_CUSTOM_SERVICE_SERSSION_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'openid'       => $openId,
                       ]);

        return ! array_key_exists('errcode', $result) ? $result : false;
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
    public function getWaiting()
    {
        $result = $this->getRequest()
                       ->get([
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
     *
     * @return bool
     */
    public function getRecords(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_CUSTOM_SERVICE_RECORD_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['recordlist']) ? $result['recordlist'] : false;
    }
}