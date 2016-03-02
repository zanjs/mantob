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

class Setting extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 配置
     */
    public function index() {
	
		$page = (int)$this->input->get('page');
        $data = $this->member_model->setting();
		$result = 0;
		
		if (IS_POST) {
			$post = $this->input->post('data');
			$page = (int)$this->input->post('page');
			foreach ($post as $name => $value) {
				$this->db->replace('member_setting', array(
					'name' => $name,
					'value' => is_array($value) ? dr_array2string($value) : $value
				));
			}
			$data = $post;
            $cache = $this->member_model->cache();
            $result = 1;
		} else {
            $cache = $this->member_model->cache();
        }

		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				lang('m-035') => 'member/admin/setting/index'
			)),
			'data' => $data,
			'page' => $page,
			'result' => $result,
            'synurl' => $cache['synurl'],
			'mobile' => is_file(FCPATH.'config/sms.php') ? TRUE : FALSE,
		));
		$this->template->display('setting_index.html');
    }
	
	/**
     * 导入进Ucenter用户表
     */
	public function importuc() {
		
	}
	
	/**
     * 会员权限划分
     */
	public function permission() {
		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				lang('122') => 'member/admin/setting/permission'
			))
		));
		$this->template->display('setting_permission.html');
	}
	
    /**
     * 网银配置
     */
    public function pay() {
		if (IS_POST) {
			$this->member_model->pay($this->input->post('data'));
			$this->member_model->cache();
		}
		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				lang('m-164') => 'member/admin/pay/card',
				lang('m-161') => 'member/admin/setting/pay',
			)),
			'data' => $this->member_model->pay(),
		));
		$this->template->display('setting_pay.html');
    }
	
	/**
     * 会员设置规则
     */
    public function rule() {
		$id = $this->input->get('id');
		if (IS_POST) {
			$this->member_model->permission($id, $this->input->post('data'));
			$this->member_model->cache();
			exit;
		}
		$this->template->assign(array(
			'data' => $this->member_model->permission($id),
		));
		$this->template->display('setting_rule.html');
    }
	
	/**
     * OAuth2授权登录
     */
	public function oauth() {
		$this->load->library('dconfig');
		$config = require FCPATH.'config/oauth.php';
		if (IS_POST) {
			$cfg = array();
			$data = $this->input->post('data');
			foreach ($data['id'] as $i => $id) {
				$cfg[$id] = array(
					'key' => trim($data['key'][$i]),
					'use' => isset($data['use'][$id]) ? 1 : 0,
					'name' => $config[$id]['name'],
					'icon' => $config[$id]['icon'],
					'secret' => trim($data['secret'][$i])
				);
			}
			$this->dconfig->file(FCPATH.'config/oauth.php')->note('OAuth2授权登录')->to_require($cfg);
			$config = $cfg;
			$this->template->assign('result', lang('m-036'));
		}
		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				'OAuth' => 'member/admin/setting/oauth'
			)),
			'data' => $config
		));
		$this->template->display('setting_oauth.html');
	}
	
	/**
     * 空间配置
     */
    public function space() {
	
		$data = $this->member_model->space();
		$page = (int)$this->input->get('page');
		
		if (IS_POST) {
			$post = $this->input->post('data');
			$page = (int)$this->input->post('page');
			$this->member_model->space($post);
			$this->member_model->cache();
			if ($post['open'] != $data['open']) {
                $this->admin_msg(lang('339'), '', 1);
            } else {
                $this->admin_msg(lang('000'), dr_url('member/setting/space', array('page' => $page)), 1);
            }
		}
		
		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				lang('html-521') => 'member/admin/setting/space'
			)),
			'page' => $page,
			'data' => $data,
		));
		$this->template->display('setting_space.html');
    }
	
	/**
     * 缓存
     */
    public function cache() {
		$site = $this->input->get('site') ? $this->input->get('site') : SITE_ID;
		$admin = (int)$this->input->get('admin');
		$this->member_model->cache($site);
		$admin or $this->admin_msg(lang('000'), isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', 1);
    }
}