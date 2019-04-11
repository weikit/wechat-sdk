<?php

namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 用户管理
 * @package Weikit\Wechat\Sdk\Components
 */
class User extends BaseComponent
{
    /**
     * 创建标签(用户标签管理)
     */
    const WECHAT_TAR_CREATE_PREFIX = 'cgi-bin/tags/create';

    /**
     * 创建标签(用户标签管理)
     *
     * @param array $tag
     *
     * @return bool|array
     */
    public function createTag(array $tag)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_TAR_CREATE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'tag' => $tag,
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
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_TAGS_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool
     */
    public function updateTag(array $tag)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_TAG_UPDATE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'tag' => $tag,
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
     *
     * @return bool
     */
    public function deleteTag(array $tag)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_TAG_DELETE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'tag' => $tag,
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
     *
     * @return array|bool|mixed
     */
    public function getUsersByTag(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_USERS_GET_BY_TAG_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool
     */
    public function setUsersTag(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_USERS_TAG_SET_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool
     */
    public function removeUsersTag(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_USERS_TAG_REMOVE_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return bool|array
     */
    public function getUserTags($openId)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_USERS_TAG_REMOVE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'openid' => $openId,
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
     *
     * @return bool
     */
    public function setUserMark(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_USERS_TAG_REMOVE_PREFIX,
                           'access_token' => $this->getAccessToken(),
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
     *
     * @return array|bool|mixed
     */
    public function getUserInfo($openId, $lang = 'zh_CN')
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_USERS_TAG_REMOVE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'openid'       => $openId,
                           'lang'         => $lang,
                       ]);

        return ! array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 批量获取用户基本信息
     */
    const WECHAT_USERS_INFO_GET_PREFIX = 'cgi-bin/user/info/batchget';

    /**
     * 批量获取用户基本信息
     *
     * @param array $users
     *
     * @return bool
     */
    public function getUsersInfo(array $users)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_USERS_INFO_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'user_list' => $users,
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
     *
     * @return array|bool|mixed
     */
    public function lists($nextOpenId)
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_USERS_INFO_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'next_openid'  => $nextOpenId,
                       ]);

        return ! array_key_exists('errcode', $result) ? $result : false;
    }
}