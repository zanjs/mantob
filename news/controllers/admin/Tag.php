<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.com/v2/news/controllers/tag.php
 */

require FCPATH.'mantob/core/D_Tag.php';

class Tag extends D_Tag {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }
	
	public function index() {
		$this->admin_index();
	}
	
	public function add() {
		$this->admin_add();
	}
	
	public function edit() {
		$this->admin_edit();
	}
	
	public function del() {
		$this->admin_del();
	}
	
}