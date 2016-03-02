<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.1.2
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Downservers extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->template->assign('menu', $this->get_menu(array(
		    lang('340') => 'admin/downservers/index',
		    lang('add') => 'admin/downservers/add_js',
		)));
    }
	
	/**
     * 管理
     */
    public function index() {
		if (IS_POST) {
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
			if (!$this->is_auth('admin/downservers/del')) {
                exit(dr_json(0, lang('160')));
            }
            $this->db->where_in('id', $ids)->delete('downservers');
			$this->cache(1);
			exit(dr_json(1, lang('000')));
		}
		$this->template->assign(array(
			'list' => $this->db->order_by('displayorder asc')->get('downservers')->result_array(),
		));
		$this->template->display('downservers_index.html');
    }
	
	/**
     * 添加
     */
    public function add() {
		if (IS_POST) {
			$data = $this->input->post('data');
			if (!$data['name'] || !$data['server']) {
                exit(dr_json(0, lang('342'), 'name'));
            }
            $data['displayorder'] = (int)$data['displayorder'];
			$this->db->insert('downservers', $data);
			$this->cache(1);
			exit(dr_json(1, lang('000')));
		}
		$this->template->display('downservers_add.html');
    }

	/**
     * 修改
     */
    public function edit() {
		$id = (int)$this->input->get('id');
		$data = $this->db
					 ->where('id', $id)
					 ->limit(1)
					 ->get('downservers')
					 ->row_array();
		if (!$data) {
            $this->admin_msg(lang('019'));
        }
		if (IS_POST) {
			$data = $this->input->post('data');
			if (!$data['name'] || !$data['server']) {
                exit(dr_json(0, lang('342'), 'name'));
            }
            $data['displayorder'] = (int)$data['displayorder'];
			$this->db->where('id', $id)->update('downservers', $data);
			$this->cache(1);
			exit(dr_json(1, lang('000')));
		}
		$this->template->assign(array(
			'data' => $data,
        ));
		$this->template->display('downservers_add.html');
    }
	
    /**
     * 缓存
     */
    public function cache($update = 0) {
        $this->system_model->downservers();
		((int)$_GET['admin'] || $update) or $this->admin_msg(lang('000'), isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', 1);
	}
}