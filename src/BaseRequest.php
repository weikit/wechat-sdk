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
     * @param string|array $url
     * @param array $options
     * @return mixed
     */
    public function get($url, array $options = array())
    {
        return $this->http('GET', $url, null, $options);
    }

    /**
     * POST方式发送请求
     *
     * @param string|array $url
     * @param mixed $data
     * @param array $options
     * @return mixed
     */
    public function post($url, $data = null, array $options = array())
    {
        return $this->http('POST', $url, $data, $options);
    }

    /**
     * POST方式发送(json body)请求
     *
     * @param string|array $url
     * @param mixed $data
     * @param array $options
     * @return mixed
     */
    public function raw($url, $data = null, array $options = array())
    {
        return $this->http('POST', $url, $this->jsonEncode($data), array_merge(array(
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ), $options));
    }

    /**
     * 上传图片
     *
     * @param string|array $url
     * @param array $files
     * @param mixed $data
     * @param array $options
     * @return mixed
     */
    public function upload($url, array $files = array(), $data = null, array $options = array())
    {
        foreach ($files as $name => $path) {
            if (!file_exists($path) || !is_readable($path)) {
                throw new \InvalidArgumentException("File '{$name}' path '{$path}' does not exist or the file is unreadable");
            }
        }
        return $this->http('POST', $url, $data, array_merge(array(
            'files' => $files
        ), $options));
    }

    /**
     * HTTP请求, 并对响应做相应处理
     *
     * @param string $method
     * @param string|array $url
     * @param $data
     * @param array $options
     * @param bool $force
     * @return bool|mixed
     * @throws \HttpException
     */
    public function http($method, $url, $data, array $options = array(), $force = true)
    {
        $url = $this->buildUrl($url); // 拼装Url
        $response = $this->request($method, $url, $data, $options);

        if (is_string($response)) {
            $response = $this->jsonDecode($response);
        }

        if (empty($response)) {
            return false;
        }
        // 错误定制
        if (isset($response['errcode'])) {
            switch ($response['errcode']) {
                case 40001: // access_token 失效,强制更新access_token, 并更新请求地址重新执行请求
                    if ($force) {
                        $url = preg_replace_callback("/access_token=([^&]*)/i", function(){
                            return 'access_token=' . $this->getAccessToken(true);
                        }, $url);
                        $response = $this->request($method, $url, $data, $options, false); // 仅重试一次
                    }
                    break;
            }
        }
        return $response;
    }

    /**
     * 发起HTTP Request请求获取数据
     *
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param array $options 请求附加参数, 继承类需实现options附件参数相关功能
     * ```php
     *     array(
     *         'headers' => array('headerName' => 'headerValue'), // header内容
     *         'cookies' => array('cookieName' => 'cookieValue'), // cookies内容
     *         'files' => array('name' => 'filePath'), // 上传的文件内容, 注意只在POST方式下执行
     *         ... // 自定义设置
     *     )
     * ```
     * @return mixed
     */
    abstract protected function request($method, $url, $data, array $options = array());

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

    /**
     * 微信请求数据JSON编码
     *
     * @param array $data
     * @return array|mixed|string
     */
    protected function jsonEncode(array $data)
    {
        if (defined('JSON_UNESCAPED_UNICODE')) { // for 5.4+
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else { // for 5.3
            // @see http://stackoverflow.com/questions/24932572/how-to-save-a-json-as-unescaped-utf-8-in-php-5-3
            $data = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
                return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
            }, json_encode($data));
        }
        return $data;
    }

    /**
     * 微信响应数据JSON解码
     *
     * @param $data
     * @return mixed
     * @throws \HttpException
     */
    protected function jsonDecode($data)
    {
        // 替换掉微信返回特殊字符(这是个坑)并返回json解析
        $return = json_decode(preg_replace("/\p{Cc}/u", '', $data), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \HttpException('Failed to parse JSON string: ' . json_last_error_msg());
        }
        return $return;
    }
}