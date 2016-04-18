<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.0.2
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class Login extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 登录
     */
    public function index() {
	
		if (IS_POST) {
			$pwd = $this->input->post('pwd');
			if ($this->pwd != $pwd) {
				$this->admin_msg('安全密码认证失败');
			}
			
			$this->admin_msg('验证成功', 'index.php?page='.$this->pwd, 1, 2);
		}
		
		$this->template->assign(array(
			'value' => $this->pwd == '123456' ? $this->pwd : ''
		));
		$this->template->display('login.html');
    }
}