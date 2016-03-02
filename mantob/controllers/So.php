<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.2
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

require_once FCPATH.'mantob/core/D_Module.php';

class So extends D_Module {

    /**
     * 高级版搜索
     */
    public function __construct() {
        define('DR_IS_SO', TRUE);
        parent::__construct();
    }

    /**
     * 搜索跳转
     */
    public function index() {

        $mod = $this->get_module(SITE_ID);
        if ($mod) {
            // 搜索参数
            $get = $this->input->get(NULL, TRUE);
            $get = isset($get['rewrite']) ? dr_rewrite_decode($get['rewrite']) : $get;
            $dir = $get['module'];
            $module = array();
            foreach ($mod as $mdir => $t) {
                if (!$t['setting']['search']['close']) {
                    $module[$mdir]['dir'] = $mdir;
                    $module[$mdir]['url'] = $t['url'];
                    $module[$mdir]['name'] = $t['name'];
                    $module[$mdir]['search'] = $this->_search($mdir);
                    if (!$dir && isset($module[$mdir]['search']['data']['params']['keyword'])) {
                        $dir = $module[$mdir]['search']['data']['params']['keyword'];
                    }
                }
            }
            if ($dir && isset($module[$dir])) {
                $now = $module[$dir];
            } else {
                $now = reset($module);
            }
            $dir = $now['dir'];
            $now = $now['search'];
            if ($get['keyword'] || $now['data']['keyword']) {
                $this->template->assign(array(
                    'module' => $module,
                    'dirname' => $dir,
                    'keyword' => $now['data']['keyword'] ? $now['data']['keyword'] : $get['keyword']
                ));
                $this->template->assign($now['seoinfo']);
                $this->template->assign($now['data']);
                unset($now['seoinfo'], $now['data'], $now['keyword']);
                $this->template->assign($now);
                $this->template->display($get['name'] ? $get['name'] : 'solist.html');
            } else {
                if ($get['name']) {
                    exit('error');
                }
                $this->template->display('so.html');
            }
        } else {
            $this->msg('您还没有安装任何模块呢~');
        }

    }

}