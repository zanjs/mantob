<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 
 */

require FCPATH.'mantob/core/D_Page.php';

class Page extends D_Page {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }
	
	/**
     * 首页
     */
    public function index() {
		$this->admin_index();
	}
	
	/**
     * 添加
     */
    public function add() {
		$this->admin_add();
	}
	
	
	/**
     * 修改
     */
    public function edit() {
		$this->admin_edit();
	}
	
	/**
     * 缓存
     */
    public function cache() {
		$this->admin_cache();
	}
	
}