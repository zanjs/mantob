<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.0.3
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class Mconfig extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				lang('my-50') => APP_DIR.'/admin/mconfig/index',
				lang('my-41') => APP_DIR.'/admin/mconfig/paytype',
				lang('my-04') => APP_DIR.'/admin/mconfig/expresses',
			))
		));
    }

    /**
     * 配置
     */
    public function index() {
		$name = 'config';
		if (IS_POST) $this->config($name, $this->input->post('data'));
		$this->template->assign(array(
			'data' => $this->config($name),
		));
		$this->template->display('mconfig_config.html');
    }

    /**
     * 付款方式
     */
    public function paytype() {
		$name = $this->router->method;
		if (IS_POST) $this->config($name, $this->input->post('data'));
		$this->template->assign(array(
			'data' => $this->config($name),
		));
		$this->template->display('mconfig_'.$name.'.html');
    }

    /**
     * 物流配置
     */
    public function expresses() {
	
		$name = $this->router->method;
		
		if (IS_POST) {
			$data = $this->input->post('data');
			$data['list'] = $data['list'] ? explode(',', $data['list']) : '';
			if ($data['list']) {
				$data['list'] = array_unique($data['list']);
				$data['list'] = implode(',', $data['list']);
			}
			$data = $this->config($name, $data);
		} else {
			$data = $this->config($name);
		}
		
		$this->load->model('order_model');
		$this->template->assign(array(
			'data' => $data,
			'list' => !$data['list'] ? array() : explode(',', $data['list']),
			'expresses' => $this->order_model->get_expresses(),
		));
		$this->template->display('mconfig_'.$name.'.html');
    }
	
	/**
	 * 配置存/取
	 *
	 * @param	intval	$name	name值
	 * @param	array	$data	修改数据
	 * @return	array
	 */
	private function config($name, $data = NULL) {
	
		$table = SITE_ID.'_'.APP_DIR.'_config';
		$config = $this->link
					   ->where('name', $name)
					   ->limit(1)
					   ->get($table)
					   ->row_array();
		$config = dr_string2array($config['value']);
		
		if ($data) { // 修改数据
			$config = $data;
			$this->link->replace($table, array('name' => $name, 'value' => dr_array2string($data)));
		}
		
		return $config;
	}
}