<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class Db extends M_Controller {
	
	private $link;
	private $siteid;
	
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->siteid = (int)$this->input->get('siteid');
		$this->link = $this->siteid ? $this->site[$this->siteid] : $this->db;
    }

    /**
     * 数据维护
     */
    public function index() {
	
		$list = $this->siteid ? $this->system_model->get_site_table($this->siteid) : $this->system_model->get_system_table();
		
		if (IS_POST) {
			
			$tables = $this->input->post('select');
			if (!$tables) {
                $this->admin_msg(lang('196'));
            }
			
			switch ((int)$this->input->post('action')) {
			
				case 1: // 优化表
					foreach ($tables as $table) {
						$this->link->query("OPTIMIZE TABLE `$table`");
					}
					$result = lang('000');
					break;
					
				case 2: // 修复表
					foreach ($tables as $table) {
						$this->link->query("REPAIR TABLE `$table`");
					}
					$result = lang('000');
					break;
			}
		}
		
		$menu = array();
		$menu[lang('194')] = 'admin/db/index';
		foreach ($this->SITE as $id => $s) {
			$menu[$s['SITE_NAME']] = 'admin/db/index/siteid/'.$id;
		}
		$this->template->assign(array(
			'menu' => $this->get_menu($menu),
			'list' => $list,
			'result' => $result,
		));
		$this->template->display('db_index.html');
	}
	
	/**
     * 数据恢复
     */
	public function recovery() {
		$this->admin_msg('此功能已废弃，请进入“应用-云商店”下载<br>由【张敏工作室】出品的【数据备份王】');
	}
	
	/**
     * 数据备份
     */
	public function backup() {
        $this->admin_msg('此功能已废弃，请进入“应用-云商店”下载<br>由【张敏工作室】出品的【数据备份王】');
    }


	/**
     * 表结构
     */
    public function tableshow() {
		$name = $this->input->get('name');
		$cache = $this->dcache->get('table');
		$this->template->assign('table', $cache[$name]);
		$this->template->display('db_table.html');
	}

}