<?php

$id = $sn; // 支付订单id必须由订单id+会员id组成
$url = SITE_URL.'member/pay/chinabank/return_url.php';
$pay = $this->ci->get_cache('member', 'setting', 'pay', 'chinabank');
$md5 = strtoupper(md5($money.'CNY'.$id.$pay['id'].$url.$pay['key']));

$html = '';
$html.= '<form method="post" name="E_FORM" action="https://pay3.chinabank.com.cn/PayGate">';
$html.= '<input type="hidden" name="v_mid" value="'.$pay['id'].'">';
$html.= '<input type="hidden" name="v_oid" value="'.$id.'">';
$html.= '<input type="hidden" name="v_amount" value="'.$money.'">';
$html.= '<input type="hidden" name="v_moneytype" value="CNY">';
$html.= '<input type="hidden" name="v_url" value="'.$url.'">';
$html.= '<input type="hidden" name="v_md5info" value="'.$md5.'">';
$html.= '<input type="hidden" name="remark1" value="'.$title.'">';
$html.= '</form>';
$html.= '<script>$(function(){ document.forms["E_FORM"].submit(); });</script>';

$result = $html;