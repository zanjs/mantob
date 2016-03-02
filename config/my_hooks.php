<?php
/* 
* @Author: zan
* @Date:   2014-09-03 09:58:45
* @Last Modified by:   zan
* @Last Modified time: 2014-09-05 19:29:37
*/
/**
 * 我的钩子定义配置
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/*
$hook['钩子名称'][] = array(
    'class' => '类名称',
    'function' => '方法名称',
    'filename' => '钩子文件.php',
    'filepath' => 'hooks',
);
 */
$hook['member_register_before'][] = array(
    'class' => 'zan_hooks',
    'function' => 'reg',
    'filename' => 'zan_hooks.php',
    'filepath' => 'hooks',
);

$hook['member_register_before'][] = array(
    'class' => 'app_hooks',
    'function' => 'reg1',
    'filename' => 'app_hooks.php',
    'filepath' => '{app}hooks',//这里的{app}标签标示当前应用目录
);
$hook['member_register_before'][] = array(
    'class' => 'app_hooks',
    'function' => 'reg2',
    'filename' => 'app_hooks.php',
    'filepath' => '{app}hooks',//这里的{app}标签标示当前应用目录
);
