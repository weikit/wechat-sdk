<?php
namespace Weikit\Wechat\Sdk;

/**
 * 基础Request类
 * @package Weikit\Wechat\Sdk
 */
abstract class BaseRequest extends BaseComponent
{
    /**
     * @var string 基本路径
     */
    public $baseUrl;
    /**
     * GET方式发送请求
     *
     * @param $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return mixed
     */
    public function get($url, $data = null, $headers = array(), $options = array())
    {
        return $this->http('GET', $url, $data, $headers, $options);
    }

    /**
     * POST方式发送请求
     *
     * @param $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return mixed
     */
    public function post($url, $data = null, $headers = array(), $options = array())
    {
        return $this->http('POST', $url, $data, $headers, $options);
    }

    /**
     * POST方式发送(json body)请求
     *
     * @param $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return mixed
     */
    public function raw($url, $data = null, $headers = array(), $options = array())
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $this->http('POST', $url, $data, array_merge(array(
            'Content-Type' => 'application/json'
        ), $headers), $options);
    }
    /**
     * 发起HTTP Request请求获取数据
     *
     * @param $method
     * @param $url
     * @param $data
     * @param $headers
     * @param $options
     * @param bool $force
     * @return mixed
     */
    abstract public function http($method, $url, $data, $headers, $options, $force = true);

    /**
     * url拼装(baseUrl,数组转换)
     * 注意: Http必须使用该函数拼装符合规则的url
     *
     * @param string|array $url
     * @return string
     */
    public function buildUrl($url)
    {
        if (is_array($url)) {
            $base = '';
            if (isset($url[0])) {
                $base = $url[0];
                unset($url[0]);
            }
            $url = $base . (stripos($base, '?') === null ? '&' : '?') . http_build_query($url);
        }
        if (!preg_match('/^https?:\\/\\//i', $url) && $this->baseUrl !== '') {
            $url = $this->baseUrl . '/' . $url;
        }
        return $url;
    }
}