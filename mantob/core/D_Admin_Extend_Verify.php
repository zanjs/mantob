<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.2
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class D_Admin_Extend_Verify extends M_Controller {

    public $content;
    protected $verify; // 审核流程
	protected $table; // 审核表
	protected $field; // 自定义字段+含系统字段
	protected $sysfield; // 系统字段
	
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->load->library('Dfield', array(APP_DIR));
        $this->table = $this->content_model->prefix . '_extend_verify';
		$this->sysfield = array(
			'inputtime' => array(
				'name' => lang('104'),
				'ismain' => 1,
				'fieldtype' => 'Date',
				'fieldname' => 'inputtime',
				'setting' => array(
					'option' => array(
						'width' => 140
					),
					'validate' => array(
						'formattr' => '',
					)
				)
			)
		);
		$field = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'extend');
		$this->field = $field ? array_merge($field, $this->sysfield) : $this->sysfield;
        if ($this->admin['adminid'] > 1) {
            $this->verify = $this->_get_verify();
        }
	}

    /**
     * 审核
     */
    public function index() {

        if ($this->admin['adminid'] > 1 && !$this->verify) {
            $this->admin_msg(lang('337'));
        }

        if (IS_POST && $this->input->post('action') != 'search') {
            $ids = $this->input->post('ids', TRUE);
            if (!$ids) {
                exit(dr_json(0, lang('013')));
            }

            if ($this->admin['adminid'] > 1) {
                // 非管理员角色只能操作自己审核的
                $status = array();
                foreach ($this->verify as $t) {
                    $status+=$t['status'];
                }
                $where = '`status` IN (' . implode(',', $status) . ')';
            } else {
                $where = '';
            }

            switch ($this->input->post('action')) {
                case 'del': // 删除
                    $this->load->model('attachment_model');
                    foreach ($ids as $id) {
                        $data = $this->link // 主表状态
                                     ->where($where ? $where.' AND `id`='.(int)$id : '`id`='.(int)$id)
                                     ->select('uid,catid')
                                     ->limit(1)
                                     ->get($this->content_model->prefix . '_extend_index')
                                     ->row_array();
                        if ($data) {
                            // 删除数据
                            $this->content_model->del_extend_verify($id);
                            // 删除表对应的附件
                            $this->attachment_model->delete_for_table($this->table.'-' . $id);
                        }
                    }
                    exit(dr_json(1, lang('000')));
                    break;
                case 'flag': // 标记
                    $js = $error = array();
                    if (!$this->input->post('flagid')) {
                        exit(dr_json(0, lang('013')));
                    }
                    foreach ($ids as $id) {
                        $result = $this->_verify($id, NULL, $where ? $where.' AND `id`='.(int)$id : '`id`='.(int)$id);
                        if (is_array($result)) {
                            if (MODULE_HTML) {
                                $js[] = dr_module_create_show_file($result['id'], 1);
                                $js[] = dr_module_create_list_file($result['catid'], 1);
                            }
                        } elseif ($result) {
                            $error[] = str_replace('<br>', '', $result);
                        }
                    }
                    if ($error) {
                        exit(dr_json(1, $error, $js));
                    } else {
                        exit(dr_json(2, lang('000'), $js));
                    }
                    break;
                default:
                    exit(dr_json(0, lang('047')));
                    break;
            }
        }

        $param = array();
        $param['status'] = (int) $this->input->get('status');
        if ($this->admin['adminid'] == 1) {
            // 管理员角色列出所有审核流程
            $where = '`status`=' . $param['status'];
            for ($i = 0; $i < 9; $i++) {
                $total = (int) $this->db->where('status', $i)->count_all_results($this->table);
                $_menu[lang('05' . $i) . ' (' . $total . ')'] = APP_DIR . '/admin/verify/index' . (isset($_GET['status']) || $i ? '/status/'.$i : '');
            }
        } else {
            // 非管理员角色列出自己审核的
            $status = array();
            foreach ($this->verify as $t) {
                $status+=$t['status'];
            }
            if ($param['status']) {
                $where = '`status` IN (' . implode(',', $status) . ')';
            } else {
                $where = '`status`=0 AND `backuid`=' . $this->uid;
            }
        }
        // 栏目筛选
        if ($this->input->get('cid')) {
            $param['cid'] = (int) $this->input->get('cid');
            $where .= ' AND `cid` = ' . $param['cid'];
        }
        // 获取总数量
        $param['total'] = $total = $this->input->get('total') ? $this->input->get('total') : $this->link->where($where)->count_all_results($this->table);
        $page = max(1, (int) $this->input->get('page'));
        $data = $this->link
                     ->select('id,cid,author,content,inputtime,status')
                     ->where($where)
                     ->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1))
                     ->order_by('inputtime DESC, id DESC')
                     ->get($this->table)
                     ->result_array();

        if ($this->admin['adminid'] > 1) {
            // 被退回
            $_total = $this->link
                           ->where('`status`=0 AND `backuid`=' . $this->uid)
                           ->count_all_results($this->table);
            $_menu[lang('050') . ' (' . $_total . ')'] = APP_DIR . '/admin/verify/index';
            // 我的审核
            $_total = $this->link
                           ->where_in('status', $status)
                           ->count_all_results($this->table);
            $_menu[lang('120') . ' (' . $_total . ')'] = APP_DIR . '/admin/verify/index/status/1';
        }

        $this->template->assign(array(
            'list' => $data,
            'menu' => $this->get_menu($_menu),
            'param' => $param,
            'pages' => $this->get_pagination(dr_url(APP_DIR . '/verify/index', $param), $param['total'])
        ));
        $this->template->display('content_extend_verify.html');
    }

    /**
     * 修改审核文档
     */
    public function edit() {

        $id = (int) $this->input->get('id');
        $data = $this->content_model->get_extend_verify($id);
        $error = array();
        if (!$data) {
            $this->admin_msg(lang('019'));
        }

        if (IS_POST) {

            $_data = $data;
            $this->content = $this->content_model->get($data['cid']);
            $_POST['data']['cid'] = $this->content['id'];
            $_POST['data']['uid'] = $this->content['uid'];
            $data = $this->validate_filter($this->field, $_data);

            if (isset($data['error'])) {
                $error = $data;
                $data = $this->input->post('data', TRUE);
            } else {
                $data[1]['cid'] = $this->content['id'];
                $data[1]['uid'] = $this->content['uid'];
                $data[1]['catid'] = $this->content['catid'];
                $data[1]['status'] = $_data['status'];
                $data[1]['author'] = $this->content['author'];
                if (isset($data[1]['mytype'])) {
                    $data[1]['mytype'] = $_data['mytype'];
                }
                $result = $this->_verify($id, $data, '`id`='.$id);
                if (is_array($result)) {
                    $this->admin_msg(
                        lang('000').
                        (MODULE_HTML ? dr_module_create_show_file($this->content['id']).dr_module_create_list_file($this->content['catid']) : ''),
                        $this->input->post('backurl'),
                        1,
                        0
                    );
                } elseif ($result) {
                    $this->admin_msg($result);
                }
                $this->admin_msg(lang('000'), $this->input->post('backurl'), 1);
            }
        }

        if ($data['status'] == 0) { // 退回
            $backuri = APP_DIR . '/admin/verify/index/status/0';
        } elseif ($data['status'] > 0 && $data['status'] < 9) {
            $backuri = APP_DIR . '/admin/verify/index/status/' . $data['status'];
        } else {
            $backuri = APP_DIR . '/admin/verify/index/';
        }

        $this->template->assign(array(
            'page' => max((int) $this->input->post('page'), 0),
            'data' => $data,
            'menu' => $this->get_menu(array(
                lang('back') => $backuri
            )),
            'error' => $error,
            'backurl' => $_SERVER['HTTP_REFERER'],
            'myfield' => $this->field_input($this->field, $data, TRUE),
        ));
        $this->template->display('content_extend_edit.html');
    }


    // 审核内容
    public function _verify($id, $data, $_where) {

        // 获得审核数据
        $verify = $this->content_model->get_extend_verify($id);
        if (!$verify) {
            return;
        }
        // 通过审核
        if ($this->input->post('flagid') > 0) {
            // 查询当前的审核状态id
            $status = $this->_get_verify_status($verify['uid'], $verify['catid'], $verify['status']);
            // 权限验证
            if ($status == 9) {
                $member = $this->member_model->get_base_member($verify['uid']);
                $category = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category', $verify['catid']);
                // 标示
                $rule = $category['permission'][$member['markrule']];
                $mark = $this->content_model->prefix.'-'.$verify['cid'].'-'.$id;
                // 积分处理
                if ($rule['experience']) {
                    $this->member_model->update_score(0, $verify['uid'], $rule['experience'], $mark, "lang,m-151,{$category['name']}", 1);
                }
                // 虚拟币处理
                if ($rule['score']) {
                    if (!$this->db
                              ->where('type', 1)
                              ->where('mark', $mark)
                              ->count_all_results('member_scorelog_'.(int)substr((string)$verify['uid'], -1, 1))) {
                        if ($rule['score'] + $member['score'] < 0) {
                            // 数量不足提示
                            return dr_lang('m-118', $verify['name'],  $member['username'], SITE_SCORE, abs($rule['score']));
                        }
                        $this->member_model->update_score(1, $verify['uid'], $rule['score'], $mark, "lang,m-151,{$category['name']}", 1);
                    }
                }
            }
            // 筛选字段
            if (!$data) {
                $data = array();
                foreach ($this->field as $field) {
                    if ($field['fieldtype'] == 'Group') {
                        continue;
                    }
                    if ($field['fieldtype'] == 'Baidumap') {
                        $data[$field['ismain']][$field['fieldname'].'_lng'] = (double)$verify[$field['fieldname'].'_lng'];
                        $data[$field['ismain']][$field['fieldname'].'_lat'] = (double)$verify[$field['fieldname'].'_lat'];
                    } else {
                        $value = $verify[$field['fieldname']];
                        if (strpos($field['setting']['option']['fieldtype'], 'INT') !== FALSE) {
                            $value = (int)$value;
                        } elseif ($field['setting']['option']['fieldtype'] == 'DECIMAL'
                            || $field['setting']['option']['fieldtype'] == 'FLOAT') {
                            $value = (double)$value;
                        }
                        $data[$field['ismain']][$field['fieldname']] = $value;
                    }
                }
                $data[1]['id'] = $data[0]['id'] = $id;
                $data[1]['uid'] = (int)$verify['uid'];
                $data[1]['catid'] = (int)$verify['catid'];
                $data[1]['author'] = $verify['author'];
                if (isset($data[1]['mytype'])) {
                    $data[1]['mytype'] = $verify['mytype'];
                }
            }
            $data[1]['status'] = $status;
            // 保存内容
            $this->content_model->edit_extend($verify, $data);
            // 审核通过
            if ($status == 9) {
                $mark = $this->content_model->prefix.'-'.$data[1]['cid'].'-'.$id;
                // 操作成功处理附件
                $this->attachment_handle($data[1]['uid'], $mark, $this->field, $data);
                $this->member_model->add_notice(
                    $data[1]['uid'],
                    3,
                    dr_lang('m-084', $verify['title'].$data[1]['name'])
                );
                return array('id' => $id, 'catid' => $data[1]['catid']);
            }
        } else {
            // 拒绝审核
            $this->link // 更改主表状态
                 ->where($_where)
                 ->update($this->content_model->prefix.'_extend', array('status' => 0));
            $this->link // 更改索引表状态
                 ->where($_where)
                 ->update($this->content_model->prefix.'_extend_index', array('status' => 0));
            $this->link // 更改审核表状态
                 ->where($_where)
                 ->update($this->content_model->prefix.'_extend_verify', array(
                    'status' => 0,
                    'backuid' => (int) $this->uid,
                    'backinfo' => dr_array2string(array(
                        'uid' => $this->uid,
                        'author' => $this->admin['username'],
                        'rolename' => $this->admin['role']['name'],
                        'optiontime' => SYS_TIME,
                        'backcontent' => $this->input->post('backcontent')
                    ))
            ));
            $this->member_model->add_notice(
                $verify['uid'],
                3,
                dr_lang('m-124', $verify['name'], MEMBER_URL.'index.php?s='.APP_DIR.'&c=eback&m=edit&id='.$id)
            );
        }
    }

}