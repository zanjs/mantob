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
	
class Block extends M_Controller {

	private $link;
	private $field;
	private $tablename;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->template->assign('menu', $this->get_menu(array(
		    lang('203') => 'admin/block/index',
		    lang('add') => 'admin/block/add_js',
		)));
		$this->link = $this->site[SITE_ID];
		$this->tablename = $this->db->dbprefix(SITE_ID.'_block');
		$this->field = array(
			'name' => array(
				'ismain' => 1,
				'fieldname' => 'name',
				'fieldtype' => 'Text',
				'setting' => array(
					'option' => array(
						'width' => 200,
					),
					'validate' => array(
						'required' => 1,
					)
				)
			),
			'content' => array(
				'ismain' => 1,
				'fieldname' => 'content',
				'fieldtype'	=> 'Textarea',
				'setting' => array(
					'option' => array(
						'width' => '370',
						'height' => 250,
					),
                    'validate' => array(
                        'xss' => 1,
                    )
				)
			),
		);
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
			if (!$this->is_auth('admin/block/del')) {
                exit(dr_json(0, lang('160')));
            }
            $this->link->where_in('id', $ids)->delete($this->tablename);
            $this->cache(1);
			exit(dr_json(1, lang('000')));
		}
		
		$this->template->assign('list', $this->link->get($this->tablename)->result_array());
		$this->template->display('block_index.html');
    }
	
	/**
     * 添加
     */
    public function add() {
	
		if (IS_POST) {
			$data = $this->validate_filter($this->field);
			if (isset($data['error'])) {
                exit(dr_json(0, $data['msg'], $data['error']));
            }
			$this->link
				 ->insert($this->tablename, $data[1]);
			$this->cache(1);
			exit(dr_json(1, lang('000'), ''));
		}
		
		$this->template->assign(array(
			'field' => $this->field,
        ));
		$this->template->display('block_add.html');
    }

	/**
     * 修改
     */
    public function edit() {
	
		$id = (int)$this->input->get('id');
		$data = $this->link
					 ->where('id', $id)
					 ->limit(1)
					 ->get($this->tablename)
					 ->row_array();
		if (!$data) {
            exit(lang('019'));
        }
		
		if (IS_POST) {
			$data = $this->validate_filter($this->field);
			if (isset($data['error'])) {
                exit(dr_json(0, $data['msg'], $data['error']));
            }
			$this->link
				 ->where('id',(int) $id)
				 ->update($this->tablename, $data[1]);
			$this->cache(1);
			exit(dr_json(1, lang('000'), ''));
		}
		
		$this->template->assign(array(
			'data' => $data,
			'field' => $this->field,
        ));
		$this->template->display('block_add.html');
    }
	
    /**
     * 缓存
     */
    public function cache($update = 0) {
		$this->system_model->block(isset($_GET['site']) && $_GET['site'] ? (int)$_GET['site'] : SITE_ID);
		((int)$_GET['admin'] || $update) or $this->admin_msg(lang('000'), isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', 1);
	}
}