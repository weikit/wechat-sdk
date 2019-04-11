<?php

namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 数据统计
 * @package Weikit\Wechat\Sdk\Components
 */
class Stats extends BaseComponent
{
    /**
     * 获取用户增减数据
     */
    const WECHAT_DATA_CUBE_USER_SUMMARY_GET_PREFIX = '/datacube/getusersummary';

    /**
     * 获取用户增减数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUserSummary(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_USER_SUMMARY_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取累计用户数据
     */
    const WECHAT_DATA_CUBE_USER_CUMULATE_GET_PREFIX = '/datacube/getusercumulate';

    /**
     * 获取累计用户数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUserCumulate(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_USER_CUMULATE_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文群发每日数据
     */
    const WECHAT_DATA_CUBE_NEWS_SUMMARY_GET_PREFIX = '/datacube/getarticlesummary';

    /**
     * 获取图文群发每日数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getNewsSummary(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_NEWS_SUMMARY_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文群发总数据
     */
    const WECHAT_DATA_CUBE_NEWS_TOTAL_GET_PREFIX = '/datacube/getarticletotal';

    /**
     * 获取图文群发总数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getNewsTotal(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_NEWS_TOTAL_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文统计数据
     */
    const WECHAT_DATA_CUBE_USER_READ_GET_PREFIX = '/datacube/getuserread';

    /**
     * 获取图文统计数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUserReadSummary(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_USER_READ_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文统计分时数据
     */
    const WECHAT_DATA_CUBE_USER_READ_HOUR_GET_PREFIX = '/datacube/getuserreadhour';

    /**
     * 获取图文统计分时数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUserReadHourly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_USER_READ_HOUR_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文分享转发数据
     */
    const WECHAT_DATA_CUBE_USER_SHARE_GET_PREFIX = '/datacube/getusershare';

    /**
     * 获取图文分享转发数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUserShareSummary(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_USER_SHARE_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文分享转发分时数据
     */
    const WECHAT_DATA_CUBE_USER_SHARE_HOUR_GET_PREFIX = '/datacube/getusersharehour';

    /**
     * 获取图文分享转发分时数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUserShareUourly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_USER_SHARE_HOUR_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送概况数据
     */
    const WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_GET_PREFIX = '/datacube/getupstreammsg';

    /**
     * 获取消息发送概况数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUpstreamMessageSummary(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息分送分时数据
     */
    const WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_HOUR_GET_PREFIX = '/datacube/getupstreammsghour';

    /**
     * 获取消息分送分时数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUpstreamMessageHourly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_HOUR_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送周数据
     */
    const WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_WEEK_GET_PREFIX = '/datacube/getupstreammsgweek';

    /**
     * 获取消息发送周数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUpstreamMessageWeekly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_WEEK_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送月数据
     */
    const WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_MONTH_GET_PREFIX = '/datacube/getupstreammsgmonth';

    /**
     * 获取消息发送月数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUpstreamMessageMonthly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_MONTH_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布数据
     */
    const WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_DIST_GET_PREFIX = '/datacube/getupstreammsgdist';

    /**
     * 获取消息发送分布数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUpstreamMessageDistSummary(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_DIST_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布周数据
     */
    const WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_DIST_WEEK_GET_PREFIX = '/datacube/getupstreammsgdistweek';

    /**
     * 获取消息发送分布周数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUpstreamMessageDistWeekly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_DIST_WEEK_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布月数据
     */
    const WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_DIST_MONTH_GET_PREFIX = '/datacube/getupstreammsgdistmonth';

    /**
     * 获取消息发送分布月数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getUpstreamMessageDistMonthly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_UPSTREAM_MESSAGE_DIST_MONTH_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取接口分析数据
     */
    const WECHAT_DATA_CUBE_INTERFACE_SUMMARY_GET_PREFIX = '/datacube/getinterfacesummary';

    /**
     * 获取接口分析数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getInterfaceSummary(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_INTERFACE_SUMMARY_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取接口分析分时数据
     */
    const WECHAT_DATA_CUBE_INTERFACE_SUMMARY_HOUR_GET_PREFIX = '/datacube/getinterfacesummaryhour';

    /**
     * 获取接口分析分时数据
     *
     * @param array $data
     *
     * @return bool|array
     */
    public function getInterfaceSummaryHourly(array $data)
    {
        $result = $this->getRequest()
                       ->raw([
                           self::WECHAT_DATA_CUBE_INTERFACE_SUMMARY_HOUR_GET_PREFIX,
                           'access_token' => $this->getAccessToken(),
                       ], $data);

        return isset($result['list']) ? $result['list'] : false;
    }
}