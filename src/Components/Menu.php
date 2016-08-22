<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 自定义菜单
 * @package Weikit\Wechat\Sdk\Components
 */
class Menu extends BaseComponent
{
    /**
     * 创建菜单
     */
    const WECHAT_MENU_CREATE_PREFIX = 'cgi-bin/menu/create';
    /**
     * 创建菜单
     *
     * ```php
     *  $wechat->menu->create([
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
     * @param array $button 菜单结构字符串
     * @return bool
     */
    public function create(array $button)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MENU_CREATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'button' => $button
            ));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取菜单列表
     */
    const WECHAT_MENU_GET_PREFIX = 'cgi-bin/menu/get';
    /**
     * 获取菜单列表
     *
     * @return array|bool
     */
    public function get()
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MENU_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
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
     */
    public function delete()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_MENU_DELETE_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 创建个性化菜单
     */
    const WECHAT_CONDITIONAL_MENU_ADD_PREFIX = 'cgi-bin/menu/addconditional';
    /**
     * 创建个性化菜单
     *
     * @param array $menu
     * @return int|bool
     */
    public function addConditional(array $menu)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CONDITIONAL_MENU_ADD_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $menu);
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
     */
    public function deleteConditional($menuId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CONDITIONAL_MENU_DELETE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'menuid' => $menuId
            ));
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
     * @return array|bool
     */
    public function matchConditional($userId)
    {
        $result = $this->getRequest()
            ->raw(array(
                    self::WECHAT_CONDITIONAL_MENU_MATCH_PREFIX,
                    'access_token' => $this->getAccessToken()
            ), array(
                'user_id' => $userId
            ));
        return isset($result['button']) ? $result['button'] : false;
    }

    /**
     * 获取自定义菜单配置接口
     */
    const WECHAT_CURRENT_MENU_GET_PREFIX = 'cgi-bin/get_current_selfmenu_info';
    /**
     * 获取自定义菜单配置接口
     *
     * @return array|bool
     */
    public function getCurrent()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_CURRENT_MENU_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['selfmenu_info']) ? $result : false;
    }

}