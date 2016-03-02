<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 高级版专享函数库（请勿复制与转载）
 *
 * @since		version 2.3.3
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

/**
 * 邮箱或手机号码登录
 *
 * @param	string	$dir	目录名称
 * @return	bool|void
 */
function dr_vip_login($db, $value) {

    if (preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/', $value)) {
        // 邮箱登录
        return $db->select('`uid`, `password`, `salt`, `email`, `username`')
                  ->where('email', $value)
                  ->limit(1)
                  ->get('member')
                  ->row_array();
    } else {
        // 手机登录
        $phone = (int)$value;
        if (strlen($phone) == 11) {
            return $db->select('`uid`, `password`, `salt`, `email`, `username`')
                      ->where('phone', $phone)
                      ->limit(1)
                      ->get('member')
                      ->row_array();
        }
        return NULL;
    }

}

/**
 * 获取站点表单内容函数
 *
 * @param	intval	$id 	表单id
 * @param	string	$form	表单表名称
 * @param	string	$field	显示字段，默认为全部数组
 * @param	intval	$sid	站点id，默认为当前站点
 * @param	intval	$cache	缓存时间，默认为10000秒
 * @return	array|void
 */
function dr_vip_form($id, $form, $field = 0, $sid = 0, $cache = 0) {

    $ci = &get_instance();
    $sid = $sid ? $sid : SITE_ID;
    $name = 'form-data-'.$sid.'-'.$form.'-'.$id;
    $data = $ci->get_cache_data($name);
    if (!$data) {
        $data = $ci->site[$sid]
                   ->where('id', (int)$id)
                   ->get($sid.'_form_'.$form)
                   ->row_array();
        $ci->set_cache_data($name, $data, $cache ? $cache : 10000);
    }

    return $field && isset($data[$field]) ? $data[$field] : $data;

}