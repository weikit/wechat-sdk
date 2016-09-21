<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 模板消息接口
 * @package Weikit\Wechat\Sdk\Components
 */
class Template extends BaseComponent
{

    /**
     * 获取设置的行业信息
     */
    const WECHAT_TEMPLATE_INDUSTRY_GET_PREFIX = 'cgi-bin/template/get_industry';

    /**
     * 获取设置的行业信息
     *
     * @return bool|array
     */
    public function getIndustry()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_TEMPLATE_INDUSTRY_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['primary_industry']) ? $result : false;
    }

    /**
     * 设置所属行业
     */
    const WECHAT_TEMPLATE_INDUSTRY_SET_PREFIX = 'cgi-bin/template/api_set_industry';
    /**
     * 设置所属行业
     *
     * @param array $data
     * @return bool
     */
    public function setIndustry(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_TEMPLATE_INDUSTRY_SET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
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
    public function getId($shortTemplateId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_TEMPLATE_ID_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'template_id_short' => $shortTemplateId
            ));
        return isset($result['template_id']) ? $result['template_id'] : false;
    }

    /**
     * 获取模板列表
     */
    const WECHAT_TEMPLATE_ALL_GET_PREFIX = 'cgi-bin/template/get_all_private_template';
    /**
     * 获取模板列表
     *
     * @return bool|array
     */
    public function lists()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_TEMPLATE_ALL_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
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
    public function delete($templateId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_TEMPLATE_DELETE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'template_id' => $templateId
            ));
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
     * @return bool|int
     */
    public function send(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_TEMPLATE_MESSAGE_SEND_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['msgid']) ? $result['msgid'] : false;
    }
}