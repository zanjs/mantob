<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.0.2
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class Update extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->db->db_debug = FALSE;
    }

    /**
     * 2.3.1 更新程序
     */
    public function index() {

        $this->admin_msg('升级完成，请更新全站缓存在刷新页面', '', 1);
        if (MAN_VERSION_ID != 16) {
            //$this->admin_msg('升级完成，请更新全站缓存在刷新页面', '', 1);
        }
        //
        $page = (int)$this->input->get('page');
        if (!$page) {
            $this->admin_msg('正在升级数据...', dr_url('update/index', array('page' => $page + 1)), 2);
        }

        switch($page) {
            case 1:
                $data = $this->db // 站点
                            ->get('site')
                            ->result_array();
                if ($data) {
                    $field = $this->db->dbprefix('field');
                    foreach ($data as $t) {
                        $table = $this->db->dbprefix($t['id'].'_navigator');
                        $this->db->query('ALTER TABLE `'.$table.'` ADD `childids` TEXT NULL DEFAULT NULL AFTER `child`;');
                        $this->db->query('ALTER TABLE `'.$table.'` ADD `pids` TEXT NULL DEFAULT NULL AFTER `pid`;');
                        $this->db->query('ALTER TABLE `'.$table.'` ADD `mark` VARCHAR(50) NOT NULL AFTER `show`;');
                        $this->db->query('ALTER TABLE `'.$table.'` ADD `extend` INT(1) DEFAULT NULL AFTER `mark`;');
                        $this->db->query('ALTER TABLE `'.$table.'` ADD INDEX (`mark`) ;');
                        $this->db->query('ALTER TABLE `'.$table.'` ADD INDEX (`extend`) ;');
                    }
                }
                $this->admin_msg('正在升级网站导航表结构...', dr_url('update/index', array('page' => $page + 1)), 2);
                break;
            default:
                $this->admin_msg('升级完成，请更新全站缓存在刷新页面', '', 1);
                break;
        }
    }
}