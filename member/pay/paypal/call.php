<?php

if ($_GET['payok'] == 'payok') {
    $this->pay_msg('付款成功', dr_member_url('pay/index'), 1);
}

$myf = dirname(__FILE__)."/a.txt";
file_put_contents($myf,"\r\n \$_POST = " . print_r($_POST,true),FILE_APPEND);

$pay = $this->get_cache('member', 'setting', 'pay', 'paypal');

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
    $req.= '&' . $key . '=' . urlencode(stripslashes($value));
}

// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header.= "Content-Type: application/x-www-form-urlencoded\r\n";
$header.= "Content-Length: " . strlen($req) ."\r\n\r\n";
$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

// assign posted variables to local variables
$payment_status = $_POST['payment_status']; // 返回状态
$receiver_email = $_POST['receiver_email']; // 商家账户

// 处理订单
if (!$fp) {
    fclose($fp);
} else {
    fputs ($fp, $header . $req);
    while (!feof($fp)) {
        $res = fgets($fp, 1024);
        $return = $header."\r\n REQ: ".$req."\r\n RES:".$res;
        if (strcmp($res, 'VERIFIED') == 0 || strcmp($res, 'INVALID') == 0) {
            // 检查状态是否是：Completed
            if ($payment_status != 'Completed' && $payment_status != 'Pending') {
                fclose($fp);
                break;
            }
            // 检查账户是否正确
            if ($receiver_email != $pay['id']) {
                fclose($fp);
                break;
            }
            // 支付成功处理
            $s=$this->pay_model->pay_success($_POST['invoice'], $_POST['mc_gross'], 'PayPal支付成功');
            fclose($fp);
            break;
        } elseif (strcmp($res, 'INVALID') == 0) {
            fclose($fp);
            break;
        }
    }
}