<?php
namespace Weikit\Wechat\Sdk\Components;

use Weikit\Wechat\Sdk\BaseComponent;

/**
 * 微信卡券
 * @package Weikit\Wechat\Sdk\Components
 */
class Card extends BaseComponent
{
    /**
     * 上传图片
     *
     * @param $path
     * @return bool
     * @see \Weikit\Wechat\Sdk\Components\Material::uploadImage
     */
    public function uploadImage($path)
    {
        return $this->wechat->material->uploadImage($path, 'buffer');
    }

    /**
     * 获取微信卡券颜色列表
     */
    const WECHAT_CARD_COLORS_GET_PREFIX = 'card/getcolors';
    /**
     * 获取微信卡券颜色列表
     *
     * @return bool
     */
    public function getColors()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_CARD_COLORS_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['colors'] : false;
    }

    /**
     * 创建卡券
     */
    const WECHAT_CARD_CREATE_PREFIX = 'card/create';
    /**
     * 创建卡券
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CREATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['card_id'] : false;
    }

    /**
     * 设置买单接口
     */
    const WECHAT_CARD_PAYCELL_SET_PREFIX = 'card/create';
    /**
     * 设置买单接口
     *
     * @param array $data
     * @return bool
     */
    public function setPayCell(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_PAYCELL_SET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 设置自助核销接口
     */
    const WECHAT_CARD_SELF_CONSUME_SET_PREFIX = 'card/selfconsumecell/set';
    /**
     * 设置自助核销接口
     *
     * @param array $data
     * @return bool
     */
    public function setConsumeSell(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_SELF_CONSUME_SET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 创建二维码
     */
    const WECHAT_CARD_QRCODE_CREATE_PREFIX = 'card/qrcode/create';
    /**
     * 创建二维码接口
     *
     * @param array $data
     * @return bool
     */
    public function createQrCode(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_QRCODE_CREATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['ticket'] : false;
    }

    /**
     * 创建货架接口
     */
    const WECHAT_CARD_LANDINGPAGE_CREATE_PREFIX = 'card/landingpage/create';
    /**
     * 创建货架接口
     *
     * @param array $data
     * @return bool|mixed
     */
    public function createLandingPage(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_LANDINGPAGE_CREATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 导入code接口
     */
    const WECHAT_CARD_CODE_DEPOSIT_PREFIX = 'card/code/deposit';
    /**
     * 导入code接口
     *
     * @param array $data
     * @return bool
     */
    public function depositCode(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_DEPOSIT_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 查询导入code数目接口
     */
    const WECHAT_CARD_CODE_DEPOSIT_COUNT_GET_PREFIX = 'card/code/getdepositcount';
    /**
     * 查询导入code数目接口
     *
     * @param $cardId
     * @return int|bool
     */
    public function getCodeDepositCount($cardId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_DEPOSIT_COUNT_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'card_id' => $cardId
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['count'] : false;
    }

    /**
     * 核查code接口
     */
    const WECHAT_CARD_CODE_CHECK_PREFIX = 'card/code/checkcode';
    /**
     * 核查code接口
     *
     * @param array $data
     * @return bool|array
     */
    public function checkCode(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_CHECK_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 图文消息群发卡券
     */
    const WECHAT_CARD_NEWS_HTML_GET_PREFIX = 'card/mpnews/gethtml';
    /**
     * 图文消息群发卡券(获取图文信息内用)
     *
     * @param $cardId
     * @return bool|string
     */
    public function getNewsHtml($cardId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_NEWS_HTML_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'card_id' => $cardId
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['content'] : false;
    }

    /**
     * 设置测试用户白名单
     */
    const WECHAT_CARD_WHITE_LIST_SET_PREFIX = 'card/testwhitelist/set';
    /**
     * 设置测试用户白名单
     * @param array $data
     * @return bool
     */
    public function setWhiteList(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_WHITE_LIST_SET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 查询code
     */
    const WECHAT_CARD_CODE_GET_PREFIX = 'card/code/get';
    /**
     * 查询code
     *
     * @param array $data
     * @return bool|mixed
     */
    public function getCode(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 核销Code接口
     */
    const WECHAT_CARD_CODE_CONSUME_PREFIX = 'card/code/consume';
    /**
     * 核销Code接口
     *
     * @param string $code
     * @param null|string $cardId
     * @return bool|array
     */
    public function consumeCode($code, $cardId = null)
    {
        $data = array(
            'code' => $code
        );
        if ($cardId !== null) {
            $data['card_id'] = $cardId;
        }
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_CONSUME_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * code解码接口
     */
    const WECHAT_CARD_CODE_DECRYPT_PREFIX = 'card/code/decrypt';
    /**
     * Code解码接口
     *
     * @param string $encryptCode
     * @return bool|string
     */
    public function decryptCode($encryptCode)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_DECRYPT_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'encrypt_code' => $encryptCode
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['code'] : false;
    }

    /**
     * Mark(占用)Code接口
     */
    const WECHAT_CARD_CODE_MARK_PREFIX = 'card/code/mark';
    /**
     * Mark(占用)Code接口
     *
     * @param array $data
     * @return bool
     */
    public function markCode(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_MARK_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 获取用户已领取卡券接口
     */
    const WECHAT_CARD_LIST_GET_PREFIX = 'card/user/getcardlist';
    /**
     * 获取用户已领取卡券接口
     *
     * @param $openId
     * @param $cardId
     * @return bool
     */
    public function getUserCards($openId, $cardId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_LIST_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'openid' => $openId,
                'card_id' => $cardId
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 查看卡券详情
     */
    const WECHAT_CARD_GET_PREFIX = 'card/get';
    /**
     * 查看卡券详情
     *
     * @param $cardId
     * @return bool|array
     */
    public function get($cardId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'card_id' => $cardId
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['card'] : false;
    }

    /**
     * 批量查询卡券列表
     */
    const WECHAT_CARD_BATCH_GET_PREFIX = 'card/batchget';
    /**
     * 批量查询卡券列表
     *
     * @param array $data
     * @return bool|array
     */
    public function lists(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_BATCH_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 更改卡券信息接口
     */
    const WECHAT_CARD_UPDATE_PREFIX = 'card/update';
    /**
     * 更改卡券信息接口
     *
     * @param array $data
     * @return bool
     */
    public function update(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_UPDATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['send_check'] : false;
    }

    /**
     * 修改库存接口
     */
    const WECHAT_CARD_STOCK_UPDATE_PREFIX = 'card/modifystock';
    /**
     * 修改库存接口
     *
     * @param array $data
     * @return bool
     */
    public function updateStock(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_STOCK_UPDATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 更改Code接口
     */
    const WECHAT_CARD_CODE_UPDATE_PREFIX = 'card/code/update';
    /**
     * 更改Code接口
     *
     * @param array $data
     * @return bool
     */
    public function updateCode(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_CODE_UPDATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 删除卡券接口
     */
    const WECHAT_CARD_DELETE_PREFIX = 'card/delete';

    /**
     * 删除卡券接口
     *
     * @param $cardId
     * @return bool
     */
    public function delete($cardId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_DELETE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'card_id' => $cardId
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 设置卡券失效接口
     */
    const WECHAT_CARD_DISABLE_CODE = 'card/code/unavailable';
    /**
     * 设置卡券失效接口
     *
     * @param string $code
     * @param null|string $cardId
     * @return bool
     */
    public function disable($code, $cardId = null)
    {
        $data = array(
            'code' => $code
        );
        if ($cardId !== null) {
            $data['card_id'] = $cardId;
        }
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_DISABLE_CODE,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 拉取卡券概况数据接口
     */
    const WECHAT_CARD_SUMMARY_GET_PREFIX = 'datacube/getcardbizuininfo';
    /**
     * 拉取卡券概况数据接口
     *
     * @param array $data
     * @return bool
     */
    public function getCardSummary(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_SUMMARY_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['list'] : false;
    }

    /**
     * 获取免费券数据接口
     */
    const WECHAT_CARD_FRESS_SUMMARY_PREFIX = 'datacube/getcardcardinfo';
    /**
     * 获取免费券数据接口
     *
     * @param array $data
     * @return bool
     */
    public function getFreeCardSummary(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_DELETE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['list'] : false;
    }

    /**
     * 拉取会员卡概况数据接口
     */
    const WECHAT_MEMBER_CARD_SUMMARY_PREFIX = 'datacube/getcardmembercardinfo';
    /**
     * 拉取单张会员卡数据接口
     */
    const WECHST_MEMBER_CARD_DETAIL_SUMMARY_PREFIX = 'datacube/getcardmembercarddetail';
    /**
     * 拉取会员卡概况数据接口|拉取单张会员卡数据接口
     *
     * @param array $data 如果参数中带有card_id则返回单张会员数据,否则返回会员卡概况数据
     * @return bool|array
     */
    public function getMemberCardSummary(array $data)
    {
        if (isset($data['card_id'])) { // 拉取单张会员卡数据接口
            $prefix = self::WECHST_MEMBER_CARD_DETAIL_SUMMARY_PREFIX;
        } else { // 拉取会员卡概况数据接口
            $prefix = self::WECHAT_MEMBER_CARD_SUMMARY_PREFIX;
        }
        $result = $this->getRequest()
            ->raw(array(
                $prefix,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['list'] : false;
    }

    /**
     * 激活会员卡
     */
    const WECHAT_MEMBER_CARD_ACTIVE_PREFIX = 'card/membercard/activate';
    /**
     * 激活会员卡
     *
     * @param array $data
     * @return bool
     */
    public function activeMemberCard(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MEMBER_CARD_ACTIVE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 设置开卡字段接口
     */
    const WECHAT_MEMBER_CARD_FORM_SET_PREFIX = 'card/membercard/activateuserform/set';
    /**
     * 设置开卡字段接口
     *
     * @param array $data
     * @return bool
     */
    public function setMemberCardForm(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MEMBER_CARD_FORM_SET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 拉取会员信息接口
     */
    const WECHAT_MEMBER_CARD_USER_GET_PREFIX = 'card/membercard/userinfo/get';
    /**
     * 拉取会员信息接口
     *
     * @param $cardId
     * @param $code
     * @return bool|array
     */
    public function getMemberCardUser($cardId, $code)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MEMBER_CARD_USER_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'card_id' => $cardId,
                'code' => $code
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 获取用户提交资料
     */
    const WECHAT_MEMBER_CARD_USER_ACTIVATE_PREFIX = 'card/membercard/activatetempinfo/get';
    /**
     * 获取用户提交资料(用户填写并提交开卡资料)
     *
     * @param string $activateTicket
     * @return bool|array
     */
    public function getMemberCardUserActivate($activateTicket)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MEMBER_CARD_USER_ACTIVATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'activate_ticket' => $activateTicket
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 更新会员信息
     */
    const WECHAT_EMBER_CARD_USER_UPDATE_PREFIX = 'card/membercard/updateuser';
    /**
     * @param array $data
     * @return bool|array
     */
    public function updateMemberCardUser(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_EMBER_CARD_USER_UPDATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 增加支付即会员规则接口
     */
    const WECHAT_MEMBER_CARD_PAY_RULE_ADD_PREFIX = 'card/paygiftmembercard/add';
    /**
     * 增加支付即会员规则接口
     *
     * @param array $data
     * @return bool|array
     */
    public function addMemberCardPayRule(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MEMBER_CARD_PAY_RULE_ADD_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 删除支付即会员规则接口
     */
    const WECHAT_MEMBER_CARD_PAY_RULE_DELETE_PREFIX = 'card/paygiftmembercard/delete';
    /**
     * 删除支付即会员规则接口
     *
     * @param array $data
     * @return bool|array
     */
    public function deleteMemberCardPayRule(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MEMBER_CARD_PAY_RULE_DELETE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 查询商户号支付即会员规则接口
     */
    const WECHAT_MEMBER_CARD_PAY_RULE_GET_PREFIX = 'card/paygiftmembercard/get';
    /**
     * 查询商户号支付即会员规则接口
     *
     * @param $mchid
     * @return bool|array
     */
    public function getMemberCardPayRule($mchid)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_MEMBER_CARD_PAY_RULE_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'mchid' => $mchid
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 开通券点账户接口
     */
    const WECHAT_CARD_PAY_ACTIVATE_PREFIX = 'card/pay/activate';
    /**
     * 开通券点账户接口
     *
     * @return bool
     */
    public function activateCardPay()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_CARD_PAY_ACTIVATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['reward'] : false;
    }

    /**
     * 对优惠券批价
     */
    const WECHAT_CARD_PAY_PRICE_PREFIX = 'card/pay/getpayprice';
    /**
     * 对优惠券批价
     *
     * @param $cardId
     * @param int $quantity
     * @return bool
     */
    public function getCardPayPrice($cardId, $quantity = 1)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_PAY_PRICE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'card_id' => $cardId,
                'quantity' => $quantity
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['reward'] : false;
    }

    /**
     * 查询券点余额接口
     */
    const WECHAT_CARD_PAY_COINS_GET_PREFIX = 'card/pay/getcoinsinfo';

    /**
     * 查询券点余额接口
     *
     * @return bool
     */
    public function getCardPayCoins()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_CARD_PAY_PRICE_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['reward'] : false;
    }

    /**
     * 确认兑换库存接口
     */
    const WECHAT_CARD_PAY_CONFIRM_PREFIX = 'card/pay/confirm';
    /**
     * 确认兑换库存接口
     *
     * @param array $data
     * @return bool
     */
    public function confirmCardPay(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_PAY_CONFIRM_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 充值券点接口
     */
    const WECHAT_CARD_PAY_RECHARGE_PREFIX = 'card/pay/recharge';
    /**
     * 充值券点接口
     *
     * @param $coinCount
     * @return bool
     */
    public function rechargeCardPay($coinCount)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_PAY_RECHARGE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'coin_count' => $coinCount
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 查询订单详情接口
     */
    const WECHAT_CARD_PAY_ORDER_GET_PREFIX = 'card/pay/getorder';
    /**
     * 查询订单详情接口
     *
     * @param $orderId
     * @return bool
     */
    public function getCardPayOrder($orderId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_PAY_ORDER_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'order_id' => $orderId
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['order_info'] : false;
    }

    /**
     * 查询券点流水详情接口
     */
    const WECHAT_CARD_PAY_ORDERS_GET_PREFIX = 'card/pay/getorderlist';
    /**
     * 查询券点流水详情接口
     *
     * @param array $data
     * @return bool
     */
    public function getCardPayOrders(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_PAY_ORDERS_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 更新会议门票
     */
    const WECHAT_CARD_MEETING_TICKET_UPDATE = 'card/meetingticket/updateuser';
    /**
     * 更新会议门票
     *
     * @param array $data
     * @return bool
     */
    public function updateMeetingTicket(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_MEETING_TICKET_UPDATE,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 更新电影票
     */
    const WECHAT_CARD_MOVIE_TICKET_UPDATE_PREFIX = 'card/movieticket/updateuser';
    /**
     * 更新电影票
     *
     * @param array $data
     * @return bool
     */
    public function updateMovieTicket(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_MEETING_TICKET_UPDATE,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 更新飞机票信息接口
     */
    const WECHAT_CARD_BOARDING_PASS_UPDATE_PREFIX = 'card/boardingpass/checkin';
    /**
     * 更新飞机票信息接口
     *
     * @param array $data
     * @return bool
     */
    public function updateBoardingPass(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_BOARDING_PASS_UPDATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 创建子商户接口
     */
    const WECHAT_CARD_SUBMERCHANT_CREATE_PREFIX = 'card/submerchant/submit';
    /**
     * 创建子商户接口
     *
     * @param array $data
     * @return bool
     */
    public function createSubmerchant(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_SUBMERCHANT_CREATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 卡券开放类目查询接口
     */
    const WECHAT_CARD_APPLY_PROTOCOL_GET_PREFIX = 'card/getapplyprotocol';
    /**
     * 卡券开放类目查询接口
     *
     * @return bool|array
     */
    public function getApplyProtocol()
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_CARD_APPLY_PROTOCOL_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['category'] : false;
    }

    /**
     * 更新子商户接口
     */
    const WECHAT_CARD_SUBMERCHANT_UPDATE_PREFIX = 'card/submerchant/update';
    /**
     * 更新子商户接口
     *
     * @param array $data
     * @return bool|array
     */
    public function updateSubmerchant(array $data)
    {
        $result = $this->getRequest()
            ->get(array(
                self::WECHAT_CARD_SUBMERCHANT_UPDATE_PREFIX,
                'access_token' => $this->getAccessToken()
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['info'] : false;
    }

    /**
     * 拉取单个子商户信息接口
     */
    const WECHAT_CARD_SUBMERCHANT_GET_PREFIX = 'card/submerchant/get';
    /**
     * 拉取单个子商户信息接口
     *
     * @param $merchantId
     * @return bool|array
     */
    public function getSubmerchant($merchantId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_SUBMERCHANT_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'merchant_id' => $merchantId
            ));
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['info'] : false;
    }

    /**
     * 拉取单个子商户信息接口
     */
    const WECHAT_CARD_SUBMERCHANTS_GET_PREFIX = 'card/submerchant/batchget';
    /**
     * 拉取单个子商户信息接口
     *
     * @param array $data
     * @return bool|array
     */
    public function getSubmerchants(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_SUBMERCHANTS_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 母商户资质申请接口
     */
    const WECHAT_CARD_AGENT_QUALIFICATION_UPLOAD_PFEFIX = 'cgi-bin/component/upload_card_agent_qualification';
    /**
     * 母商户资质申请接口
     *
     * @param array $data
     * @return bool|array
     */
    public function uploadAgentQualification(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_AGENT_QUALIFICATION_UPLOAD_PFEFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['result']) ? $result['result'] : false;
    }

    /**
     * 子商户资质申请接口
     */
    const WECHAT_CARD_MERCHANT_QUALIFICATION_UPLOAD_PFEFIX = 'cgi-bin/component/upload_card_merchant_qualification';
    /**
     * 子商户资质申请接口
     *
     * @param array $data
     * @return bool|array
     */
    public function uploadMerchantQualification(array $data)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_MERCHANT_QUALIFICATION_UPLOAD_PFEFIX,
                'access_token' => $this->getAccessToken()
            ), $data);
        return isset($result['result']) ? $result['result'] : false;
    }

    /**
     * 子商户资质审核查询接口
     */
    const WECHAT_CARD_MERCHANT_QUALIFICATION_CHECK_PFEFIX = 'cgi-bin/component/check_card_merchant_qualification';
    /**
     * 子商户资质审核查询接口
     *
     * @param $appId
     * @return bool
     */
    public function checkMerchantQualification($appId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_MERCHANT_QUALIFICATION_CHECK_PFEFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'appid' => $appId
            ));
        return isset($result['result']) ? $result['result'] : false;
    }

    /**
     * 拉取单个子商户信息接口
     */
    const WECHAT_CARD_MERCHANT_GET_PREFIX = 'cgi-bin/component/get_card_merchant';
    /**
     * 拉取单个子商户信息接口
     *
     * @param $appId
     * @return bool
     */
    public function getMerchant($appId)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_MERCHANT_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'appid' => $appId
            ));
        return !isset($result['errcode']) ? $result : false;
    }

    /**
     * 拉取子商户列表接口
     */
    const WECHAT_CARD_MERCHANTS_GET_PREFIX = 'cgi-bin/component/batchget_card_merchant';
    /**
     * 拉取子商户列表接口
     *
     * @param $nextGet
     * @return bool
     */
    public function getMerchants($nextGet)
    {
        $result = $this->getRequest()
            ->raw(array(
                self::WECHAT_CARD_MERCHANTS_GET_PREFIX,
                'access_token' => $this->getAccessToken()
            ), array(
                'next_get' => $nextGet
            ));
        return !isset($result['errcode']) ? $result : false;
    }
}