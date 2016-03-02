<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mantob Website Management System
 *
 * @since		version 2.0.1
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
        $this->output->enable_profiler(FALSE);
    }
    
    public function index() {
	
		if (IS_POST) {
		
			if (get_cookie('admin_login')) {
                $this->admin_msg(lang('167'));
            }
			if (SITE_ADMIN_CODE && !$this->check_captcha('code')) {
               
                $this->admin_msg(lang('168'));
            }
			
			$uid = $this->member_model->admin_login($this->input->post('username', TRUE), $this->input->post('password', TRUE));
			if ($uid > 0) {
				$url = $this->input->get('backurl') ? urldecode($this->input->get('backurl')) : dr_url('home');
				$url = pathinfo($url);
				$url = $url['basename'] ? $url['basename'] : dr_url('home/index');
				$this->admin_msg(lang('042'), $url, 1);
			}

			if ($uid == -1) {
				$this->admin_msg(lang('043'));
			} elseif ($uid == -2) {
				$this->admin_msg(lang('044'));
			} elseif ($uid == -3) {
				$this->admin_msg(lang('045'));
			} elseif ($uid == -4) {
				$this->admin_msg(lang('046'));
			} else {
				$this->admin_msg(lang('047'));
			}
		}
		
		$this->template->assign('username', $this->member['username']);
		$this->template->display('login.html');	
    }
	
	public function logout() {
		$this->session->unset_userdata('admin');
		$this->session->unset_userdata('siteid');
		$this->admin_msg(lang('048'), dr_url(''), 1);
	}
}