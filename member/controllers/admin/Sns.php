<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.5
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Sns extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->template->assign('menu', $this->get_menu(array(
		    lang('041') => 'member/admin/sns/index',
		    lang('201') => 'member/admin/sns/topic',
		)));
		$this->load->model('sns_model');
    }
	
	/**
     * 动态管理
     */
    public function index() {

        if (IS_POST && $this->input->post('action')) {
            // ID格式判断
            $ids = $this->input->post('ids', TRUE);
            if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
            // 删除
            if (!$this->is_auth('member/admin/sns/del')) {
                exit(dr_json(0, lang('160')));
            }
            foreach ($ids as $id) {
                $this->sns_model->delete($id);
            }
            exit(dr_json(1, lang('000')));
        }

        // 重置页数和统计
        if (IS_POST) {
            $_GET['page'] = $_GET['total'] = 0;
        }

        // 根据参数筛选结果
        $param = array();
        if ($this->input->get('search')) {
            $param['search'] = 1;
        }

        // 数据库中分页查询
        list($data, $_param, $_search) = $this->sns_model->feed_limit_page(
            $param,
            max((int)$_GET['page'], 1),
            (int)$_GET['total']
        );
        $param = $_param ? $param + $_param : $param;
        $field = array(
                'username' => array('fieldname' => 'username','name' => lang('html-347')),
                'content' => array('fieldname' => 'content','name' => lang('html-214'))
            ) + ($field ? $field : array());
        $search = $_search ? $param + $_search : $param;
        $this->template->assign(array(
            'list' => $data,
            'field' => $field,
            'param' => $search,
            'pages'	=> $this->get_pagination(dr_url('member/sns/index', $param), $param['total']),
        ));
        $this->template->display('sns_index.html');
    }

	/**
     * 话题管理
     */
    public function topic() {

        if (IS_POST && $this->input->post('action')) {
            // ID格式判断
            $ids = $this->input->post('ids', TRUE);
            if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
            // 删除
            if (!$this->is_auth('member/admin/sns/del')) {
                exit(dr_json(0, lang('160')));
            }
            foreach ($ids as $id) {
                $this->sns_model->delete_topic($id);
            }
            exit(dr_json(1, lang('000')));
        }

        // 重置页数和统计
        if (IS_POST) {
            $_GET['page'] = $_GET['total'] = 0;
        }

        // 根据参数筛选结果
        $param = array();
        if ($this->input->get('search')) {
            $param['search'] = 1;
        }

        // 数据库中分页查询
        list($data, $_param, $_search) = $this->sns_model->topic_limit_page(
            $param,
            max((int)$_GET['page'], 1),
            (int)$_GET['total']
        );
        $param = $_param ? $param + $_param : $param;
        $field = array(
                'username' => array('fieldname' => 'username','name' => lang('html-766')),
                'name' => array('fieldname' => 'name','name' => lang('html-764'))
            ) + ($field ? $field : array());
        $search = $_search ? $param + $_search : $param;
        $this->template->assign(array(
            'list' => $data,
            'field' => $field,
            'param' => $search,
            'pages'	=> $this->get_pagination(dr_url('member/topic/index', $param), $param['total']),
        ));
        $this->template->display('sns_topic.html');
    }

}