<?php

 /**
 * mantob Website Management System
 *
 * @since		version 2.0.1
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
 * 默认路由配置（不允许更改）
 */
 
$route['test']					= 'api/test';
$route['sitemap.xml']			= 'api/sitemap';
$route['404_override']			= '';
$route['default_controller']	= 'home';
$route['so-(.*)\.html']		    = 'so/index/rewrite/$1';

if (is_file(APPPATH.'config/rewrite.php')) require APPPATH.'config/rewrite.php';

/**
 * 自定义路由
 */
 
//$route['自定义路由正则规则']	= '指向的路由URI（必须是v2的URI规则：控制器/方法/参数1/参数1的值/参数2/参数2的值...）';

