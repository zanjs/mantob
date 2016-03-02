<?php

$pay = $this->get_cache('member', 'setting', 'pay', 'tenpay');

if (DR_PAY_FILE == 'return') {

    require APPPATH.'pay/tenpay/classes/ResponseHandler.class.php';
    require APPPATH.'pay/tenpay/classes/function.php';

    /* 创建支付应答对象 */
    $resHandler = new ResponseHandler();
    $resHandler->setKey($pay['key']);

    //判断签名
    if($resHandler->isTenpaySign()) {
        //通知id
        $notify_id = $resHandler->getParameter("notify_id");
        //商户订单号
        $out_trade_no = $resHandler->getParameter("out_trade_no");
        //财付通订单号
        $transaction_id = $resHandler->getParameter("transaction_id");
        //金额,以分为单位
        $total_fee = $resHandler->getParameter("total_fee");
        //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
        $discount = $resHandler->getParameter("discount");
        //支付结果
        $trade_state = $resHandler->getParameter("trade_state");
        //交易模式,1即时到账
        $trade_mode = $resHandler->getParameter("trade_mode");

        if ("1" == $trade_mode ) {
            if ( "0" == $trade_state){
                //------------------------------
                //处理业务开始
                //------------------------------
                $money = number_format(($total_fee / 100), 2, '.', '');
                $module = $this->pay_model->pay_success($out_trade_no, $money, '财付通订单号：'.$transaction_id);
                //------------------------------
                //处理业务完毕
                //------------------------------
                $url = $module ? MEMBER_URL.'index.php?s='.$module.'&c=order&m=index' : MEMBER_URL.'index.php?c=pay';
                $this->pay_msg("即时到帐支付成功($money)", $url, 1);
            } else {
                //当做不成功处理
                $this->pay_msg('即时到帐支付失败');
            }
        } else {
            // 交易模式错误，只能是即时到帐
            $this->pay_msg('交易模式错误，只能是即时到帐');
        }
    } else {
        $this->pay_msg('认证签名失败：'.$resHandler->getDebugInfo());
    }
} else {

    require (APPPATH."pay/tenpay/classes/ResponseHandler.class.php");
    require (APPPATH."pay/tenpay/classes/RequestHandler.class.php");
    require (APPPATH."pay/tenpay/classes/client/ClientResponseHandler.class.php");
    require (APPPATH."pay/tenpay/classes/client/TenpayHttpClient.class.php");
    require (APPPATH."pay/tenpay/classes/function.php");

    $key = $pay['key'];
    $partner = $pay['id'];

    /* 创建支付应答对象 */
    $resHandler = new ResponseHandler();
    $resHandler->setKey($key);

    //判断签名
    if ($resHandler->isTenpaySign()) {

        //通知id
        $notify_id = $resHandler->getParameter("notify_id");

        //通过通知ID查询，确保通知来至财付通
        //创建查询请求
        $queryReq = new RequestHandler();
        $queryReq->init();
        $queryReq->setKey($key);
        $queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
        $queryReq->setParameter("partner", $partner);
        $queryReq->setParameter("notify_id", $notify_id);

        //通信对象
        $httpClient = new TenpayHttpClient();
        $httpClient->setTimeOut(5);
        //设置请求内容
        $httpClient->setReqContent($queryReq->getRequestURL());

        //后台调用
        if ($httpClient->call()) {
            //设置结果参数
            $queryRes = new ClientResponseHandler();
            $queryRes->setContent($httpClient->getResContent());
            $queryRes->setKey($key);

            if ($resHandler->getParameter("trade_mode") == "1") {
                //判断签名及结果（即时到帐）
                //只有签名正确,retcode为0，trade_state为0才是支付成功
                if ($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {

                    //取结果参数做业务处理
                    $out_trade_no = $resHandler->getParameter("out_trade_no");
                    //财付通订单号
                    $transaction_id = $resHandler->getParameter("transaction_id");
                    //金额,以分为单位
                    $total_fee = $resHandler->getParameter("total_fee");
                    //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
                    $discount = $resHandler->getParameter("discount");

                    //------------------------------
                    //处理业务开始
                    //------------------------------

                    $money = number_format(($total_fee / 100), 2, '.', '');
                    $this->pay_model->pay_success($out_trade_no, $money, '财付通订单号：'.$transaction_id);

                    //------------------------------
                    //处理业务完毕
                    //------------------------------
                    echo "success";exit;

                } else {
                    //错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                    echo "fail";exit;
                }
            }
        } else {
            //通信失败
            echo "fail";exit;
        }
    } else  {
        echo "<br/>" . "认证签名失败" . "<br/>";
        echo $resHandler->getDebugInfo() . "<br>";
    }
}
exit;