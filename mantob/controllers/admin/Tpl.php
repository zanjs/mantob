<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.com/v2/mantob/controllers/admin/tpl.php
 */

require FCPATH.'mantob/core/D_File.php';

class Tpl extends D_File {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->path = FCPATH.'mantob/templates/';
		$this->template->assign(array(
			'path' => $this->path,
			'furi' => 'admin/tpl/',
			'auth' => 'admin/tpl/',
			'menu' => $this->get_menu(array(
				lang('230') => 'admin/tpl/index',
				lang('233') => 'admin/tpl/tag',
			)),
		));
    }
	
}