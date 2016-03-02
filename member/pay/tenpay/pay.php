<?php

require FCPATH.'member/pay/tenpay/classes/RequestHandler.class.php';
$pay = $this->ci->get_cache('member', 'setting', 'pay', 'tenpay');

/* 创建支付请求对象 */
$reqHandler = new RequestHandler();
$reqHandler->init();
$reqHandler->setKey($pay['key']);
$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
$return_url = SITE_URL.'member/pay/tenpay/return_url.php';
$notify_url = SITE_URL.'member/pay/tenpay/notify_url.php';

//----------------------------------------
//设置支付参数
//----------------------------------------
$reqHandler->setParameter("partner", $pay['id']);
$reqHandler->setParameter("out_trade_no", $sn); // 支付订单id必须由订单id+会员id组成
$reqHandler->setParameter("total_fee", $money * 100);  //总金额,单位分，所有扩大100倍
$reqHandler->setParameter("return_url",  $return_url);
$reqHandler->setParameter("notify_url", $notify_url);
$reqHandler->setParameter("body", dr_lang('m-178', $this->member['username'], $id));
$reqHandler->setParameter("bank_type", "DEFAULT"); //银行类型，默认为财付通

//用户ip
$reqHandler->setParameter("spbill_create_ip", $this->input->ip_address());//客户端IP
$reqHandler->setParameter("fee_type", "1"); //币种
$reqHandler->setParameter("subject", $title); //商品名称，（中介交易时必填）

//系统可选参数
$reqHandler->setParameter("sign_type", "MD5"); //签名方式，默认为MD5，可选RSA
$reqHandler->setParameter("service_version", "1.0"); //接口版本号
$reqHandler->setParameter("input_charset", "UTF-8"); //字符集
$reqHandler->setParameter("sign_key_index", "1"); //密钥序号

//业务可选参数
$reqHandler->setParameter("attach", ""); //附件数据，原样返回就可以了
$reqHandler->setParameter("product_fee", ""); //商品费用
$reqHandler->setParameter("transport_fee", "0"); //物流费用
$reqHandler->setParameter("time_start", date("YmdHis")); //订单生成时间
$reqHandler->setParameter("time_expire", ""); //订单失效时间
$reqHandler->setParameter("buyer_id", ""); //买方财付通帐号
$reqHandler->setParameter("goods_tag", ""); //商品标记
$reqHandler->setParameter("trade_mode", "1"); //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
$reqHandler->setParameter("transport_desc", ""); //物流说明
$reqHandler->setParameter("trans_type", "1"); //交易类型
$reqHandler->setParameter("agentid", ""); //平台ID
$reqHandler->setParameter("agent_type", ""); //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
$reqHandler->setParameter("seller_id", "");

//请求的URL
$result = $reqHandler->getRequestURL();