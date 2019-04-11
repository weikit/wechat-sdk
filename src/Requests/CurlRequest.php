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
     * @inheritdoc
     */
    public function request($method, $url, $data, array $options = [], $force = true)
    {
        $curl = curl_init();
        switch (strtoupper($method)) {
            case 'GET':
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
                if ( ! empty($data)) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . $data;
                }
                break;
            case 'POST':
                // 上传图片
                if (isset($options['files']) && is_array($options['files'])) {
                    $data = (array)$data;
                    foreach ($options['files'] as $name => $path) {
                        // php 5.5将抛弃@写法,引用CURLFile类来实现 @see http://segmentfault.com/a/1190000000725185
                        $data[$name] = class_exists('\CURLFile') ? new \CURLFile($path) : '@' . $path;
                    }
                }

                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                throw new \UnexpectedValueException("Unsupport http method '{$method}' called.");
        }
        // header头内容
        if (isset($options['headers']) && is_array($options['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        }
        // cookie内容
        if (isset($options['cookies']) && is_array($options['cookies'])) {
            $parts = [];
            foreach ($options['cookies'] as $name => $value) {
                $parts[] = $name . '=' . urlencode($value);
            }
            curl_setopt($curl, CURLOPT_COOKIE, implode(';', $parts));
        }
        if (stripos($url, "https://") !== false) { // 是否https协议
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1); // 微信官方屏蔽了ssl2和ssl3, 启用更高级的ssl
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $responseInfo = curl_getinfo($curl);
        curl_close($curl);
        if (isset($responseInfo['http_code']) && intval($responseInfo['http_code']) == 200) {
            return $response;
        }

        return false;
    }
}