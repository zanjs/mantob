<?php

/**
 * Omweb Website Management System
 * 
 * @since			version 1.0.6
 * @author			mantob <kefu@mantob.com>
 * @license     	http://www.mantob.com/license
 * @copyright		Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

/**
 * 站点配置文件
 */

return array(

	'SITE_NAME'                     => 'mantob', //网站的名称
	'SITE_DOMAIN'                   => 'wubu.app', //网站的域名
	'SITE_MOBILE'                   => '', //移动端域名
	'SITE_LANGUAGE'                 => 'zh-cn', //网站的语言
	'SITE_THEME'                    => 'zan', //网站的主题风格
	'SITE_TEMPLATE'                 => 'hunTpl', //网站的模板目录
	'SITE_TIMEZONE'                 => 8, //所在的时区常量
	'SITE_TIME_FORMAT'              => 'Y-m-d H:i', //时间显示格式，与date函数一致，默认Y-m-d H:i:s
	'SITE_TITLE'                    => '缘始婚庆礼仪 我们结婚吧', //网站首页SEO标题
	'SITE_SEOJOIN'                  => '_', //网站SEO间隔符号
	'SITE_KEYWORDS'                 => '缘始婚庆礼仪 我们结婚吧', //网站SEO关键字
	'SITE_DESCRIPTION'              => '缘始婚庆礼仪 我们结婚吧', //网站SEO描述信息
	'SITE_NAVIGATOR'                => '主导航,首页幻灯,底部导航', //网站导航信息，多个导航逗号分开
	'SITE_HOME_INDEX'               => 1099, //站点首页静态化有效期
	'SITE_MODULE_INDEX'             => 999, //站点模块静态化有效期
	'SITE_ATTACH_REMOTE'            => 0, //是否开启远程附件
	'SITE_MOBILE_OPEN'              => 0, //是否自动识别移动端并强制定向到移动端域名
	'SITE_QUERY_CACHE'              => 0, //页面查询的默认缓存时间

);