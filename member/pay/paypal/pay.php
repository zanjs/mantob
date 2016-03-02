<?php

$url = SITE_URL.'member/pay/paypal/return_url.php';
$pay = $this->ci->get_cache('member', 'setting', 'pay', 'paypal');

$html = '';
$html.= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="E_FORM">';
$html.= '<input type="hidden" name="cmd" value="_xclick">';
$html.= '<input type="hidden" name="business" value="'.$pay['id'].'">';
$html.= '<input type="hidden" name="item_name" value="'.$title.'">';
$html.= '<input type="hidden" name="amount" value="'.$money.'">';
$html.= '<input type="hidden" name="currency_code" value="'.$pay['type'].'">';
$html.= '<input type="hidden" name="return" value="'.$url.'?payok=payok">';
$html.= '<input type="hidden" name="invoice" value="'.$sn.'">';
$html.= '<input type="hidden" name="charset" value="utf-8">';
$html.= '<input type="hidden" name="no_shipping" value="1">';
$html.= '<input type="hidden" name="no_note" value="">';
$html.= '<input type="hidden" name="notify_url" value="'.$url.'">';
$html.= '<input type="hidden" name="rm" value="2">';
$html.= '<input type="hidden" name="cancel_return" value="'.MEMBER_URL.'">';
$html.= '</form>';
$html.= '<script>$(function(){ document.forms["E_FORM"].submit(); });</script>';

$result = $html;