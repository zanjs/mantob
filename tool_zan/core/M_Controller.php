<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class M_Controller extends CI_Controller {

	public $pwd;
	public $code;
	public $admin;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		
        define('IS_AJAX', $this->input->is_ajax_request());
		define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) ? TRUE : FALSE);
		define('SYS_TIME', $_SERVER['REQUEST_TIME'] ? $_SERVER['REQUEST_TIME'] : time());
		define('SITE_URL', '/');
		define('SITE_PATH', '/');
		define('MEMBER_PATH', '/member/');
		
		$this->code = $this->pwd = require APPPATH.'core/Pwd.php';
		if ($_GET['page'] == $this->pwd) {
			$this->admin = 1;
		}
		
		$this->load->helper('cookie');
		$this->load->library('template');
		
		$this->template->ci = $this;
		$this->template->assign('version', '3.0');
    }
	
	/**
     * 后台提示消息显示
	 *
	 * @param	string	$msg	提示信息
	 * @param	string	$url	转向地址
	 * @param	int		$mark	标示符号1：成功；0：失败；2：等待
	 * @param	int		$time	等待时间
     * @return  void
     */
	protected function admin_msg($msg, $url = '', $mark = 0, $time = 1) {
		$this->template->assign(array(
			'msg' => $msg,
			'url' => $url,
			'time' => $time,
			'mark' => $mark
		));
		$this->template->display('msg.html', 'admin');
		exit;
	}
	/**
     * 后台操作界面中的顶部导航菜单
	 *
	 * @param	array	$menu
	 * @return	string
     */
	protected function get_menu($menu) {
	
		if (!$menu) return NULL;
		
		$_i = 0;
		$_str = '';
		$_uri = str_replace(APP_DIR.'/', '', $this->duri->uri(1)); // 当前uri
		$_mark = true;
		
		foreach ($menu as $name => $uri) {
			$uri = trim($uri, '/');
			if (!$name && !$uri) continue;
			$url = $this->duri->uri2url($uri).'&page='.$this->code;
			
			$mark = $_i == 0 ? '{MARK}' : '';
			$class = ''; // 判断选中
			if ($this->get_menu_calss($menu, $uri, $_uri)) {
				$_mark = FALSE;
				$class = ' class="on"';
			}
			$_str .= '<a href="'.$url.'" '.$class.$mark.'><em>'.$name.'</em></a><span>|</span>';
			$_i ++;
		}
		if ($_mark && $this->router->method == 'edit') {
			$_str .= '<a href="javascript:;" class="on"><em>'.lang('edit').'</em></a><span>|</span>';
			$_mark = FALSE;
		}
		return $_mark ? str_replace('{MARK}', ' class="on"', $_str) : str_replace('{MARK}', '', $_str);
	}
	
	private function get_menu_calss($menu, $uri, $_uri) {
	
		if ($uri == $_uri) return TRUE;
		
		if (!in_array($_uri, $menu)) {
			if (@strpos($_uri, $uri) === FALSE) return FALSE;
			$uri_arr = explode('/', $_uri);
			$uri_arr = array_slice($uri_arr, 0, -2);
			$__uri = implode('/', $uri_arr);
			if (in_array($__uri, $menu) && $__uri == $uri) return TRUE;
			return $this->get_menu_calss($menu, $uri, $__uri);
		}
	}
}