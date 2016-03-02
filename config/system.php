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
 * 系统配置文件
 */

return array(

	'SYS_LOG'                       => 0, //后台操作日志开关
	'SYS_KEY'                       => 'mantob', //安全密钥
	'SYS_DEBUG'                     => 0, //调试器开关
	'SYS_HELP_URL'                  => 'http://www.mantob.com/help/list-341.html', //系统帮助url前缀部分
	'SYS_EMAIL'                     => 'root@zanjs.com', //系统收件邮箱，用于接收系统信息
	'SYS_MEMCACHE'                  => 0, //Memcache缓存开关
	'SYS_ATTACHMENT_DIR'            => '', //系统附件目录名称
	'SYS_CRON_QUEUE'                => 1, //任务队列方式
	'SYS_CRON_NUMS'                 => 20, //每次执行任务数量
	'SYS_CRON_TIME'                 => 300, //每次执行任务间隔
	'SITE_EXPERIENCE'               => '经验值', //经验值名称
	'SITE_SCORE'                    => '虚拟币', //虚拟币名称
	'SITE_MONEY'                    => '金钱', //金钱名称
	'SITE_CONVERT'                  => 10, //虚拟币兑换金钱的比例
	'SITE_ADMIN_CODE'               => 0, //后台登录验证码开关
	'SITE_ADMIN_PAGESIZE'           => 8, //后台数据分页显示数量

);