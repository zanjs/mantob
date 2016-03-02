<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since	    version 2.2.2
 * @author	    mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
class D_Member_Extend_Back extends M_Controller {

    public $content; // 内容数据
    protected $field; // 自定义字段+含系统字段

    /**
     * 构造函数
     */

    public function __construct() {
        parent::__construct();
        $this->load->library('Dfield', array(APP_DIR));
    }

    /**
     * 审核
     */
    public function index() {

        if (IS_POST) {

            $ids = $this->input->post('ids', TRUE);
            if (!$ids) {
                exit(dr_json(0, lang('019')));
            }

            $this->load->model('attachment_model');
            foreach ($ids as $id) {
                $data = $this->link // 主表状态
                             ->where('id', (int)$id)
                             ->where('uid', (int)$this->uid)
                             ->select('cid')
                             ->limit(1)
                             ->get($this->content_model->prefix.'_extend_index')
                             ->row_array();
                if ($data) {
                    // 删除数据
                    $this->content_model->del_extend_verify($id);
                    // 删除表对应的附件
                    $this->attachment_model->delete_for_table($this->content_model->prefix.'_verify-'.$data['cid'].'-'.$id);
                }
            }

            exit(dr_json(1, lang('mod-40')));
        }

        $this->link
             ->select('id,inputtime,catid,content')
             ->where('uid', $this->uid)
             ->where('status', 0)
             ->order_by('inputtime DESC');
        if ($this->input->get('action') == 'more') { // ajax更多数据
            $page = max((int) $this->input->get('page'), 1);
            $data = $this->link
                         ->limit($this->pagesize, $this->pagesize * ($page - 1))
                         ->get($this->content_model->prefix.'_extend_verify')
                         ->result_array();
            if (!$data) {
                exit('null');
            }
            $this->template->assign('list', $data);
            $this->template->display('eback_data.html');
        } else {
            $this->template->assign(array(
                'list' => $this->link
                               ->limit($this->pagesize)
                               ->get($this->content_model->prefix.'_extend_verify')
                               ->result_array(),
                'total' => $this->total[2],
                'moreurl' => 'index.php?s='.APP_DIR.'&c=eback&m=index&action=more',
                'meta_name' => lang('mod-42'),
            ));
            $this->template->display('eback_index.html');
        }
    }

    /**
     * 修改审核
     */
    public function edit() {

        $id = (int) $this->input->get('id');
        $data = $this->content_model->get_extend_verify($id);
        $error = array();
        if (!$data) {
            $this->member_msg(lang('019'));
        }
        // 禁止修改他人文档
        if ($data['author'] != $this->member['username']
            && $data['uid'] != $this->member['uid']) {
            $this->member_msg(lang('mod-05'));
        }
        $field = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'extend');

        if (IS_POST) {

            $_data = $data;
            // 设置uid便于校验处理
            $_POST['data']['id'] = $id;
            $_POST['data']['uid'] = $this->uid;
            $_POST['data']['author'] = $this->member['username'];
            $data = $this->validate_filter($field, $_data);

            if (isset($data['error'])) {
                $error = $data;
                $data = $this->input->post('data', TRUE);
            } else {
                $this->content = $this->content_model->get($_data['cid']);
                $data[1]['cid'] = (int)$this->content['id'];
                $data[1]['uid'] = $this->member['uid'];
                $data[1]['catid'] = (int)$this->content['catid'];
                $data[1]['status'] = 1;
                $data[1]['author'] = $this->member['username'];
                if (isset($data[1]['mytype'])) {
                    $data[1]['mytype'] = $_data['mytype'];
                }
                // 修改数据
                if ($this->content_model->edit_extend($_data, $data)) {
                    if (IS_AJAX) {
                        exit(dr_json(1, lang('m-341'), dr_member_url(APP_DIR.'/everify/index')));
                    }
                    $this->template->assign(array(
                        'url' => dr_member_url(APP_DIR.'/everify/index'),
                        'add' => dr_member_url(APP_DIR.'/extend/add', array('cid' => $_data['cid'])),
                        'edit' => 1,
                        'list' => dr_member_url(APP_DIR.'/extend/index', array('cid' => $_data['cid'])),
                        'meta_name' => lang('mod-03')
                    ));
                    $this->template->display('verify.html');
                } else {
                    $this->member_msg(lang('mod-06'));
                }
                exit;
            }
        }

        $backurl = str_replace(MEMBER_URL, '', $_SERVER['HTTP_REFERER']);
        $this->template->assign(array(
            'purl' => dr_url(APP_DIR.'/everify/edit', array('id' => $id)),
            'data' => $data,
            'myfield' => $this->field_input($field, $data, TRUE),
            'backurl' => $backurl ? $backurl : dr_url(APP_DIR.'/everify/index'),
            'listurl' => $backurl ? $backurl : dr_url(APP_DIR.'/everify/index'),
            'meta_name' => lang('mod-41'),
            'result_error' => $error,
        ));
        $this->template->display('eback_edit.html');
    }

}
