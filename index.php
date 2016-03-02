<?php

/**
 * Omweb Website Management System
 *
 * @since	    version 2.3.0
 * @author	    mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
// 显示错误提示
if (function_exists('ini_set')) {
    ini_set('display_errors', TRUE);
    ini_set('memory_limit', '1024M');
}
// 查询执行超时时间
if (function_exists('set_time_limit')) {
    set_time_limit(0);
}
// 该文件的名称
if (!defined('SELF')) {
    define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
}
// 网站根目录
if (!defined('FCPATH')) {
    define('FCPATH', str_replace(SELF, '', __FILE__));
}

if (PHP_SAPI === 'cli' || defined('STDIN')) {
    unset($_GET);
    $_GET['c'] = 'cron';
    $_GET['m'] = 'index';
    chdir(dirname(__FILE__));
}

define('EXT', '.php'); // PHP文件扩展名
define('BASEPATH', FCPATH . 'mantob/system/'); // CI框架目录
define('SYSDIR', 'system'); // “系统文件夹”的名称

// 判断s参数,“应用程序”文件夹目录
if (!defined('APP_DIR') && isset($_GET['s'])
    && preg_match('/^[a-z]+$/i', $_GET['s'])
    && is_dir(FCPATH . 'app/' . $_GET['s'] . '/')) {
    define('APPPATH', FCPATH . 'app/' . $_GET['s'] . '/');
    define('APP_DIR', $_GET['s']); // 应用目录名称
}

$uri = isset($_SERVER['HTTP_X_REWRITE_URL']) && trim($_SERVER['REQUEST_URI'], '/') == SELF ? trim($_SERVER['HTTP_X_REWRITE_URL'], '/') : ($_SERVER['REQUEST_URI'] ? trim($_SERVER['REQUEST_URI'], '/') : NULL);
define('SYS_URL', 'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim($uri, '/'));

if ($uri) {
    if (strpos($uri, '?') !== FALSE) {
        $uri = explode('?', $uri);
        $uri = $uri[0];
    }
    if (strpos($uri, SELF) === FALSE
        && !file_exists(FCPATH . $uri)) {
        if (strpos($uri, '/') !== FALSE) {
            $uri = explode('/', $uri);
            if (is_dir(FCPATH . $uri[0])) {
                define('APPPATH', FCPATH . $uri[0] . '/');
                define('APP_DIR', $uri[0]); // 模块目录名称
                unset($uri[0]);
            }
            define('DR_URI', implode('/', $uri)); // 组合URI
        } else {
            define('DR_URI', $uri); // URI
        }
    }
}

// 模块/应用目录名称
if (!defined('APP_DIR')) {
    define('APP_DIR', '');
}
// 后台管理标识
if (!defined('IS_ADMIN')) {
    define('IS_ADMIN', FALSE);
}
// 前端会员标识
if (!defined('IS_MEMBER')) {
    define('IS_MEMBER', FALSE);
}
// “应用程序”文件夹目录
if (!defined('APPPATH')) {
    define('APPPATH', FCPATH . 'mantob/');
}

define('VIEWPATH', FCPATH . 'mantob/'); // 定义视图目录，我们把它当做主项目目录
define('ENVIRONMENT', FCPATH . 'config'); // 环境配置文件目录

// 禁止d参数
if (!IS_ADMIN && !IS_MEMBER && isset($_GET['d'])) {
    unset($_GET['d']);
}

// 加载单页缓存
// if (!IS_ADMIN && !IS_MEMBER) {
//     $file = FCPATH.'cache/page/url.php';
//     $urls = @unserialize(@file_get_contents($file));
//     if (isset($urls[SYS_URL]) && is_file($urls[SYS_URL])) {
//         echo file_get_contents($urls[SYS_URL]);
//         exit;
//     }
//     unset($file, $urls);
// }

unset($uri);

require BASEPATH . 'core/CodeIgniter.php'; // CI框架核心文件