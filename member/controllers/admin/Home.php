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

class Home extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->template->assign('menu', $this->get_menu(array(
			lang('m-031') => 'member/admin/home/index',
			lang('add') => 'member/admin/home/add_js'
		)));
    }

    /**
     * 首页
     */
    public function index() {

		if (IS_POST && $this->input->post('action')) {

            // ID格式判断
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
			
			if ($this->input->post('action') == 'del') {
                // 删除
				if (!$this->is_auth('member/admin/home/del')) {
                    exit(dr_json(0, lang('160')));
                }
                if($this->member['uid']==$ids[0]){
                	exit(dr_json(0, lang('161')));
                };
				$this->member_model->delete($ids);
				exit(dr_json(1, lang('000')));
			} else {
                // 修改会员组
				if (!$this->is_auth('member/admin/home/edit')) {
                    exit(dr_json(0, lang('160')));
                }
				$gid = (int)$this->input->post('groupid');
				$note = dr_lang('m-078', $this->member['username'], $this->get_cache('member', 'group', $gid, 'name'));
				$this->db
					 ->where_in('uid', $ids)
					 ->update('member', array('groupid' => $gid));
				$this->member_model->add_notice($ids, 1, $note);
                foreach ($ids as $uid) {
                    // 会员组升级挂钩点
                    $this->hooks->call_hook('member_group_upgrade', array('uid' => $uid, 'groupid' => $gid));
                }
				exit(dr_json(1, lang('000')));
			}
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
		list($data, $_param, $_search) = $this->member_model->limit_page($param, max((int)$_GET['page'], 1), (int)$_GET['total']);
		$param = $_param ? $param + $_param : $param;
        $field = $this->get_cache('member', 'field');
        $field = array(
            'name' => array('fieldname' => 'name','name' => lang('m-242')),
            'email' => array('fieldname' => 'email','name' => lang('m-244')),
            'phone' => array('fieldname' => 'phone','name' => lang('m-243'))
        ) + ($field ? $field : array());
        $search = $_search ? $param + $_search : $param;

		$this->template->assign(array(
			'list' => $data,
            'field' => $field,
			'param'	=> $search,
			'pages'	=> $this->get_pagination(dr_url('member/home/index', $param), $param['total']),
		));
		 $admin_role=$this->admin[role][id];

	   $this->template->assign('admin_role', $admin_role);
		$this->template->display('index.html');
    }
	
	/**
     * 添加
     */
    public function add() {
	
		if (IS_POST) {
		
			$all = $this->input->post('all');
			$info = $this->input->post('info');
			$data = $this->input->post('data');
			
			if (!$data['groupid']) {
                exit(dr_json(0, lang('m-156'), 'groupid'));
            }
			
			if ($all) {
				// 批量添加
				if (!$info) {
                    exit(dr_json(0, lang('m-155'), 'info'));
                }
				$data = explode(PHP_EOL, $info);
				$success = $error = 0;
				foreach ($data as $t) {
					list($username, $password, $email) = explode('|', $t);
					if ($username && $password && $email) { 
						$uid = $this->member_model->register(array(
							'username' => $username,
							'password' => $password,
							'email' => $email
						), $data['groupid']);
						if ($uid) {
							$success ++;
						} else {
							$error ++;
						}
					}
				}
				exit(dr_json(1, dr_lang('m-157', $success, $error)));
			} else {
				// 单个添加
				$uid = $this->member_model->register(array(
					'username' => $data['username'],
					'password' => $data['password'],
					'email' => $data['email']
				), $data['groupid']);
				if ($uid == -1) {
					exit(dr_json(0, lang('m-021'), 'username'));
				} elseif ($uid == -2) {
					exit(dr_json(0, lang('m-011'), 'email'));
				} elseif ($uid == -3) {
					exit(dr_json(0, lang('m-022'), 'email'));
				} elseif ($uid == -4) {
					exit(dr_json(0, lang('m-023'), 'username'));
				} elseif ($uid == -5) {
					exit(dr_json(0, lang('m-024'), 'username'));
				} elseif ($uid == -6) {
					exit(dr_json(0, lang('m-025'), 'username'));
				} elseif ($uid == -7) {
					exit(dr_json(0, lang('m-026'), 'username'));
				} elseif ($uid == -8) {
					exit(dr_json(0, lang('m-027'), 'username'));
				} elseif ($uid == -9) {
					exit(dr_json(0, lang('m-028'), 'username'));
				} else {
					exit(dr_json(1, lang('000')));
				}
			}
		}
		
		$this->template->display('add.html');
    }
	
	/**
     * 修改
     */
    public function edit() {
	
		$uid = (int)$this->input->get('uid');
		$page = (int)$this->input->get('page');
		$data = $this->member_model->get_member($uid);
		if (!$data) {
            $this->admin_msg(lang('019'));
        }
		
		$field = array();
		$MEMBER = $this->get_cache('member');
		if ($MEMBER['field'] && $MEMBER['group'][$data['groupid']]['allowfield']) {
			foreach ($MEMBER['field'] as $t) {
				if (in_array($t['fieldname'], $MEMBER['group'][$data['groupid']]['allowfield'])) {
					$field[] = $t;
				}
			}
		}

		$is_uc = function_exists('uc_user_edit') && $MEMBER['setting']['ucenter'];
		
		if (IS_POST) {
			$edit = $this->input->post('member');
			$page = (int)$this->input->post('page');
			$post = $this->validate_filter($field, $data);
			if (!$edit['groupid']) {
				$error = lang('m-156');
			} elseif (isset($post['error'])) {
				$error = $post['msg'];
			} else {
				$post[1]['uid'] = $uid;
				$post[1]['complete'] = (int)$data['complete'];
				$this->db->replace('member_data', $post[1]);
				$this->attachment_handle($uid, $this->db->dbprefix('member').'-'.$uid, $field, $data);
				$update = array(
					'name' => $edit['name'],
					'phone' => $edit['phone'],
					'groupid' => $edit['groupid'],
				);
                // 修改密码
				if ($edit['password']) {
					if ($is_uc) {
                        uc_user_edit($data['username'], '', $edit['password'], '', 1);
                    }
					$update['password'] = md5(md5($edit['password']).$data['salt'].md5($edit['password']));
					$this->member_model->add_notice($uid, 1, dr_lang('m-079', $this->member['username']));
				}
                // 修改邮箱
                if ($edit['email'] != $data['email']) {
                    if (!preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/', $edit['email'])) {
                        $this->admin_msg(lang('m-011'));
                    }
                    if ($this->db->where('email', $edit['email'])->where('uid<>', $uid)->count_all_results('member')) {
                        $this->admin_msg(lang('m-022'));
                    }
                    if ($is_uc) {
                        $ucid = uc_user_edit($data['username'], '', '', $edit['email'], 1);
                        if ($ucid == -4) {
                            $this->admin_msg(lang('m-026'));
                        } elseif ($ucid == -5) {
                            $this->admin_msg(lang('m-027'));
                        } elseif ($ucid == -6) {
                            $this->admin_msg(lang('m-028'));
                        }
                    }
                    $update['email'] = $edit['email'];
                    $this->member_model->add_notice($uid, 1, dr_lang('m-121', $this->member['username']));
                }
				$this->db->where('uid', $uid)->update('member', $update);
                // 会员组升级挂钩点
                if ($data['groupid'] != $edit['groupid']) {
                    $this->hooks->call_hook('member_group_upgrade', array('uid' => $uid, 'groupid' => $edit['groupid']));
                }
				$this->admin_msg(lang('000'), dr_url('member/home/edit', array('uid' => $uid, 'page' => $page)), 1);
			}
			$this->admin_msg($error, dr_url('member/home/edit', array('uid' => $uid, 'page' => $page)));
		}
		
		$this->template->assign(array(
			'data' => $data,
			'page' => $page,
			'myfield' => $this->field_input($field, $data, TRUE),
		));
		$this->template->display('edit.html');
    }

    public function ajax_email() {

        $uid = (int)$this->input->get('uid');
        $email = $this->input->get('email');

        if (!$email || !preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/', $email)) {
            exit(lang('m-011'));
        }

        if ($this->db->where('email', $email)->where('uid<>', $uid)->count_all_results('member')) {
            exit(lang('m-022'));
        }

        exit(0);
    }
}