<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.2.2
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Register extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 注册
     */
    public function index() {
	
		$MEMBER = $this->get_cache('MEMBER');
        // 判断是否开启注册
		if (!$MEMBER['setting']['register']) {
            $this->member_msg(lang('m-016'));
        }
        // 已经登录不允许注册
		if ($this->member) {
            $this->member_msg(lang('m-017'));
        }

		if (IS_POST) {
			$data = $this->input->post('data', TRUE);
            $back_url = $_POST['back'] ? urldecode($this->input->post('back')) : '';
			if ($MEMBER['setting']['regcode'] && !$this->check_captcha('code')) {
				$error = array('name' => 'code', 'msg' => lang('m-000'));
			} elseif (!$data['password']) {
				$error = array('name' => 'password', 'msg' => lang('m-018'));
			} elseif ($data['password'] !== $data['password2']) {
				$error = array('name' => 'password2', 'msg' => lang('m-019'));
			} elseif ($result = $this->is_username($data['username'])) {
				$error = array('name' => 'username', 'msg' => $result);
			} elseif ($result = $this->is_email($data['email'])) {
				$error = array('name' => 'email', 'msg' => $result);
			} else {
                $this->hooks->call_hook('member_register_before', $data); // 注册之前挂钩点
				$id = $this->member_model->register($data);
				if ($id > 0) {
				    // 注册成功
                    $this->hooks->call_hook('member_register_after', $data); // 注册之后挂钩点
					$this->member_msg(lang('m-020'), $back_url && strpos($back_url, 'register') === FALSE ? $back_url : dr_url('login/index'), 1);
				} elseif ($id == -1) {
					$error = array('name' => 'username', 'msg' => dr_lang('m-021', $data['username']));
				} elseif ($id == -2) {
					$error = array('name' => 'email', 'msg' => lang('m-011'));
				} elseif ($id == -3) {
					$error = array('name' => 'email', 'msg' => dr_lang('m-022', $data['email']));
				} elseif ($id == -4) {
					$error = array('name' => 'username', 'msg' => lang('m-023'));
				} elseif ($id == -5) {
					$error = array('name' => 'username', 'msg' => lang('m-024'));
				} elseif ($id == -6) {
					$error = array('name' => 'username', 'msg' => lang('m-025'));
				} elseif ($id == -7) {
					$error = array('name' => 'username', 'msg' => lang('m-026'));
				} elseif ($id == -8) {
					$error = array('name' => 'username', 'msg' => lang('m-027'));
				} elseif ($id == -9) {
					$error = array('name' => 'username', 'msg' => lang('m-028'));
				}
			}
		} else {
            $data = array();
            $error = '';
            $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        }
		
		$this->template->assign(array(
			'data' => $data,
			'code' => $MEMBER['setting']['regcode'],
            'back_url' => $back_url,
			'meta_name' => lang('m-029'),
			'result_error' => $error,
		));
		$this->template->display('register.html');
    }
}