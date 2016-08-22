<?php
namespace Weikit\Wechat\Sdk\Requests;

use Weikit\Wechat\Sdk\BaseRequest;

/**
 * Class CurlRequest
 * @package Weikit\Wechat\Sdk
 */
class CurlRequest extends BaseRequest
{
    /**
     * 发送HTTP请求
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @param bool $force
     * @return bool|mixed
     */
    public function http($method, $url, $data, $headers, $options, $force = true)
    {
        $url = $this->buildUrl($url); // 拼装Url

        $curl = curl_init();
        switch (strtoupper($method)) {
            case 'GET':
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
                if (!empty($data)) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . $data;
                }
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                throw new \UnexpectedValueException("Unsupport http method '{$method}' called.");
        }

        if (stripos($url, "https://") !== false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1); // 微信官方屏蔽了ssl2和ssl3, 启用更高级的ssl
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        if (isset($status['http_code']) && intval($status['http_code']) == 200) {
            if (!empty($status['content_type']) && stripos($status['content_type'], 'application/json') !== false) {
                return json_decode($content, true);
            } else {
                return $content;
            }
        }
        return false;
    }
}