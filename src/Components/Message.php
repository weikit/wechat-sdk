<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 客服消息
 * @package Weikit\Wechat\Sdk\Components
 */
class Message extends BaseComponent
{
    /**
     * 发送客服消息
     */
    const WECHAT_CUSTOM_MESSAGE_SEND_PREFIX = 'cgi-bin/message/custom/send';
    /**
     * 发送客服消息
     *
     * @param array $data
     * @return bool
     */
    public function send(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CUSTOM_MESSAGE_SEND_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
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
        $data = array(
            'touser' => $toUser,
            'msgtype' => 'text',
            'text' => array(
                'content' => $content
            )
        );
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->send($data);
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
        $data = array(
            'touser' => $toUser,
            'msgtype' => 'image',
            'image' => array(
                'media_id' => $mediaId
            )
        );
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->send($data);
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
        $data = array(
            'touser' => $toUser,
            'msgtype' => 'voice',
            'voice' => array(
                'media_id' => $mediaId
            )
        );
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->send($data);
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
        $data = array(
            'touser' => $toUser,
            'msgtype' => 'video',
            'video' => $video
        );
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->send($data);
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
        $data = array(
            'touser' => $toUser,
            'msgtype' => 'music',
            'music' => $music
        );
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->send($data);
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
            $data = array(
                'touser' => $toUser,
                'msgtype' => "news",
                'news' => array(
                    'articles' => $news
                )
            );
        } else {
            $data = array(
                'touser' => $toUser,
                'msgtype' => "mpnews",
                'mpnews' => array(
                    'media_id' => $news
                )
            );
        }
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->send($data);
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
        $data = array(
            'touser' => $toUser,
            'msgtype' => 'wxcard',
            'wxcard' => $card
        );
        if ($account !== null) {
            $data['customservice']['kf_account'] = $account;
        }
        return $this->send($data);
    }
}