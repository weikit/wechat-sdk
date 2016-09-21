<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 高级群发接口
 * @package Weikit\Wechat\Sdk\Components
 */
class MassMessage extends BaseComponent
{
    /**
     * 根据标签进行群发
     */
    const WECHAT_MASS_MESSAGE_SEND_BY_TAG_PREFIX = 'cgi-bin/message/mass/sendall';
    /**
     * 根据OpenID列表群发
     */
    const WECHAT_MASS_MESSAGE_SEND_BY_OPEN_ID_PREFIX = 'cgi-bin/message/mass/send';

    /**
     * 群发接口
     * 支持两种方式群发:
     *  根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     *  根据标签进行群发【订阅号与服务号认证后均可用】
     *
     * @param array $data
     * @return bool|mixed
     */
    public function send(array $data)
    {
        if (array_key_exists('touser', $data)) { // 根据OpenID列表群发
            $api = self::WECHAT_MASS_MESSAGE_SEND_BY_OPEN_ID_PREFIX;
        } else { // 根据标签进行群发
            $api = self::WECHAT_MASS_MESSAGE_SEND_BY_TAG_PREFIX;
        }
        $result = $this->getRequest()
            ->raw(array($api,'access_token' => $this->getAccessToken()), $data);
        return isset($result['msg_id']) ? $result : false;
    }

    /**
     * 删除群发
     */
    const WECHAT_MASS_MESSAGE_SENDED_CANCEL_PREFIX = 'cgi-bin/message/mass/delete';
    /**
     * 删除群发
     *
     * @param $messageId
     * @return bool
     */
    public function cancelSended($messageId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MASS_MESSAGE_SENDED_CANCEL_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'msg_id' => $messageId
            ));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 预览接口
     */
    const WECHAT_MASS_MESSAGE_PREVIEW_PREFIX = 'cgi-bin/message/mass/preview';

    /**
     * 预览接口
     *
     * @param array $data
     * @return bool
     */
    public function preview(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MASS_MESSAGE_PREVIEW_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
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
    public function status($messageId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MASS_MESSAGE_STATUS_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'msg_id' => $messageId
            ));
        return isset($result['msg_status']) ? $result['msg_status'] : false;
    }
}