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

class Group extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->template->assign('menu', $this->get_menu(array(
			lang('m-032') => 'member/admin/group/index',
			lang('add') => 'member/admin/group/add',
		)));
		$this->load->model('group_model');
    }

    /**
     * 管理
     */
    public function index() {

		if (IS_POST) {
			if ($this->input->post('action') == 'del') { // 删除
				$this->group_model->del($this->input->post('ids'));
                $this->clear_cache('member');
				exit(dr_json(1, lang('014')));
			} elseif ($this->input->post('action') == 'edit') { // 修改
				$_ids = $this->input->post('ids');
				$_data = $this->input->post('data');
				foreach ($_ids as $id) {
					$this->db
						 ->where('id', $id)
						 ->update('member_group', array('displayorder' => (int)$_data[$id]['displayorder']));
				}
				unset($_ids, $_data);
                $this->clear_cache('member');
				exit(dr_json(1, lang('014')));
			}
		}

		$this->template->assign(array(
			'list' => $this->group_model->get_data(),
		));
		$this->template->display('group_index.html');
    }
	
	/**
     * 添加
     */
    public function add() {

		$page = (int)$this->input->get('page');
		$error = 0;

		if (IS_POST) {
			$data = $this->input->post('data', TRUE);
			$page = (int)$this->input->post('page');
			if (!$data['name']) {
				$error = lang('m-033');
			} else {
				$this->group_model->add($data);
                $this->clear_cache('member');
				$this->admin_msg(lang('014'), dr_url('member/group/index'), 1);
			}
		}

		$group = $this->get_cache('member', 'group');
		$overdue = array();
		foreach ($group as $t) {
			if ($t['id'] > 2 && $t['price'] == 0) {
				$overdue[] = array(
					'id' => $t['id'],
					'name' => $t['name']
				);
			}
		}

		$this->template->assign(array(
			'page' => $page,
			'error' => $error,
            'space' => array_diff(dr_dir_map(FCPATH.'member/templates/', 1), array('admin', 'member')),
			'theme' => array_diff(dr_dir_map(FCPATH.'member/statics/', 1), array('avatar', 'js', 'OAuth', 'emotions')),
			'overdue' => $overdue,
			'mtemplate' => array_diff(dr_dir_map(FCPATH.'member/templates/member/', 1), array('admin')),
		));
		$this->template->display('group_add.html');
    }
	
	/**
     * 修改
     */
    public function edit() {

		$id = (int)$this->input->get('id');
		$data = $this->group_model->get($id);
		if (!$data) {
            $this->admin_msg(lang('019'));
        }

		$page = (int)$this->input->get('page');
		$error = 0;

		if (IS_POST) {
			$post = $this->input->post('data', TRUE);
			$page = (int)$this->input->post('page');
			if (!$data['name']) {
				$error = lang('m-033');
			} else {
				$this->group_model->edit($id, $post);
                $this->clear_cache('member');
				$this->admin_msg(lang('014'), dr_url('member/group/index'), 1);
			}
		}

		$group = $this->get_cache('member', 'group');
		$overdue = array();
		foreach ($group as $t) {
			if ($t['id'] > 2 && $t['price'] == 0) {
				$overdue[] = array(
					'id' => $t['id'],
					'name' => $t['name']
				);
			}
		}

		$this->template->assign(array(
			'page' => $page,
			'data' => $data,
			'error' => $error,
            'group' => $group,
            'space' => array_diff(dr_dir_map(FCPATH.'member/templates/', 1), array('admin', 'member')),
			'theme' => array_diff(dr_dir_map(FCPATH.'member/statics/', 1), array('avatar', 'js', 'OAuth', 'emotions')),
			'overdue' => $overdue,
			'mtemplate' => array_diff(dr_dir_map(FCPATH.'member/templates/member/', 1), array('admin')),
		));
		$this->template->display('group_add.html');
    }
	
	/**
     * 删除
     */
    public function del() {
		$this->group_model->del((int)$this->input->get('id'));
        $this->clear_cache('member');
		exit(dr_json(1, lang('014')));
	}
}