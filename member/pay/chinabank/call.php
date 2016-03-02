<?php

$pay = $this->get_cache('member', 'setting', 'pay', 'chinabank');

if (DR_PAY_FILE == 'return') {
    $v_oid =trim($_POST['v_oid']); // 商户发送的v_oid定单编号
    $v_pmode =trim($_POST['v_pmode']); // 支付方式（字符串）
    $v_pstatus =trim($_POST['v_pstatus']); //  支付状态 ：20（支付成功）；30（支付失败）
    $v_pstring =trim($_POST['v_pstring']); // 支付结果信息 ： 支付完成（当v_pstatus=20时）；失败原因（当v_pstatus=30时,字符串）；
    $v_amount =trim($_POST['v_amount']); // 订单实际支付金额
    $v_moneytype =trim($_POST['v_moneytype']); //订单实际支付币种
    $v_md5str =trim($_POST['v_md5str' ]); //拼凑后的MD5校验值
    $md5string = strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$pay['key'])); // 重新计算md5的值
    // 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理
    if ($v_md5str == $md5string) {
        if($v_pstatus == "20") {
            //支付成功，可进行逻辑处理！
            //商户系统的逻辑处理（例如判断金额，判断支付状态，更新订单状态等等）......
            $module = $this->pay_model->pay_success($v_oid, $v_amount, '银行订单编号：'.$_POST['v_idx']);
            $url = $module ? MEMBER_URL.'index.php?s='.$module.'&c=order&m=index' : MEMBER_URL.'index.php?c=pay';
            $this->pay_msg("网银在线充值成功($v_amount)", $url, 1);
        } else {
            $this->pay_msg("支付失败");
        }
    } else {
        $this->pay_msg("校验失败,数据可疑");
    }
}