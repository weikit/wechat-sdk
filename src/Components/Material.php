<?php

namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 素材管理
 * @package Weikit\Wechat\Sdk\Components
 */
class Material extends BaseComponent
{
    /**
     * 新增临时素材(上传临时多媒体文件)
     */
    const WECHAT_MEDIA_UPLOAD_PREFIX = 'cgi-bin/media/upload';

    /**
     * 新增临时素材(上传临时多媒体文件)
     *
     * @param string $path
     * @param string $type
     *
     * @return bool|array
     */
    public function uploadMedia($path, $type = 'image')
    {
        $result = $this->getRequest()
                       ->upload([
                           self::WECHAT_MEDIA_UPLOAD_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'type'         => $type,
                       ], [
                           'media' => $path,
                       ]);

        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 获取临时素材(下载多媒体文件)
     */
    const WECHAT_MEDIA_GET_PREFIX = 'cgi-bin/media/get';

    /**
     * 获取临时素材(下载多媒体文件)
     *
     * @param $mediaId
     *
     * @return bool|string
     */
    public function getMedia($mediaId)
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_MEDIA_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'media_id'     => $mediaId,
                       ]);

        return is_string($result) ? $result : false;
    }

    /**
     * 上传图片
     */
    const WECHAT_MEDIA_IMAGE_UPLOAD_PREFIX = 'cgi-bin/media/uploadimg';

    /**
     * 上传图片 (支持图文消息, 卡券, 门店等图片上传)
     *
     * @param $path
     * @param $name
     *
     * @return bool
     */
    public function uploadImage($path, $name = 'media')
    {
        $result = $this->getRequest()
                       ->post([
                           self::WECHAT_MEDIA_IMAGE_UPLOAD_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           $name => $path,
                       ]);

        return isset($result['url']) ? $result['url'] : false;
    }

    /**
     * 上传图文消息内的图片获取URL
     *
     * @param $path
     *
     * @return bool
     */
    public function uploadNewsImage($path)
    {
        return $this->uploadImage($path);
    }

    /**
     * 新增永久图文素材
     */
    const WECHAT_MEDIA_NEWS_MATERIAL_ADD_PREFIX = 'cgi-bin/material/add_news';
    /**
     * 上传图文消息素材(群发接口)
     */
    const WECHAT_MEDIA_NEWS_ADD_PREFIX = 'cgi-bin/media/uploadnews';

    /**
     * 新增永久图文素材
     * 上传图文消息素材(群发接口)
     *
     * @param array $articles
     * @param bool $isMass 是否新增群发接口图文素材
     *
     * @return bool|array
     */
    public function addNews(array $articles, $isMass = false)
    {
        $result = $this->getRequest()
                       ->raw([
                           $isMass ? self::WECHAT_MEDIA_NEWS_ADD_PREFIX : self::WECHAT_MEDIA_NEWS_MATERIAL_ADD_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'articles' => $articles,
                       ]);

        return isset($result['media_id']) ? $result['media_id'] : false;
    }

    /**
     * 新增其他类型永久素材
     */
    const WECHAT_MATERIAL_ADD_PREFIX = 'cgi-bin/material/add_material';

    /**
     * 新增其他类型永久素材
     *
     * @param string $path
     * @param string $type
     * @param array $data 视频素材需要description
     *
     * @return bool|mixed
     */
    public function add($path, $type, $data = [])
    {
        $result = $this->getRequest()
                       ->post([
                           self::WECHAT_MATERIAL_ADD_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'type'         => $type,
                       ], [
                           'media' => $path,
                       ], $data);

        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 获取永久素材
     */
    const WECHAT_MATERIAL_GET_PREFIX = 'cgi-bin/material/get_material';

    /**
     * 获取永久素材
     *
     * @param $mediaId
     *
     * @return bool|string
     */
    public function get($mediaId)
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_MATERIAL_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                           'media_id'     => $mediaId,
                       ]);

        return ! array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 删除永久素材
     */
    const WECHAT_MATERIAL_DELETE_PREFIX = 'cgi-bin/material/del_material';

    /**
     * 删除永久素材
     *
     * @param $mediaId
     *
     * @return bool
     */
    public function delete($mediaId)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_MATERIAL_DELETE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], [
                           'media_id' => $mediaId,
                       ]);

        return isset($result['errcode']) && ! $result['errcode'];
    }

    /**
     * 修改永久图文素材
     */
    const WECHAT_MATERIAL_NEWS_UPDATE_PREFIX = 'cgi-bin/material/update_news';

    /**
     * 修改永久图文素材
     *
     * @param array $data
     *
     * @return bool
     */
    public function updateNews(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_MATERIAL_NEWS_UPDATE_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['errcode']) && ! $result['errcode'];
    }

    /**
     * 获取素材总数
     */
    const WECHAT_MATERIAL_COUNTS_GET_PREFIX = 'cgi-bin/material/get_materialcount';

    /**
     * 获取素材总数
     *
     * @return bool|array
     */
    public function getStats()
    {
        $result = $this->getRequest()
                       ->get([
                           self::WECHAT_MATERIAL_COUNTS_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ]);

        return ! array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 获取素材列表
     */
    const WECHAT_MATERIAL_LIST_GET_PREFIX = 'cgi-bin/material/batchget_material';

    /**
     * 获取素材列表
     *
     * @param $data
     *
     * @return bool|array
     */
    public function lists($data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_MATERIAL_LIST_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return ! isset($result['errodcode']) ? $result : false;
    }
}