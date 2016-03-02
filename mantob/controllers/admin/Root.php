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
	
class Root extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$this->template->assign('menu', $this->get_menu(array(
		    lang('029') => 'admin/root/index',
		    lang('add') => 'admin/root/add_js'
		)));
    }
	
	/**
     * 管理员管理
     */
    public function index() {

		if (IS_POST) {
		
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
			if (!$this->is_auth('admin/root/del')) {
                exit(dr_json(0, lang('160')));
            }
	
			foreach ($ids as $id) {
				$this->member_model->del_admin($id);
			}
			
			exit(dr_json(1, lang('000'), ''));
		}
		$admin_role=$this->admin[role][id];
	   $this->template->assign('admin_role', $admin_role);
		$this->template->assign('list', $this->member_model->get_admin_all((int)$this->input->get('roleid'), $this->input->get('keyword', TRUE)));
		$this->template->display('admin_index.html');
    }
	
	/**
     * 添加
     */
    public function add() {
	
		$role = $this->dcache->get('role');
		
		if (IS_POST) {
		
			$data = $this->input->post('data', TRUE);
			if (!$data['adminid'] || !isset($role[$data['adminid']])) {
                exit(dr_json(0, lang('022'), 'adminid'));
            }
			
			$check = $this->db
                          ->select('uid,adminid')
                          ->where('username', $data['username'])
                          ->limit(1)
                          ->get($this->db->dbprefix('member'))
                          ->row_array();
			$uid = $check['uid'];
			
			if (!$check) { // 会员不存在时，需要注册
				$member = array(
					'username' => $data['username'],
					'password' => $data['password'],
					'email' => $data['email']
				);
				$uid = $this->member_model->register($member, 3);
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
				}
			} elseif ($check['adminid'] > 0) { // 已经属于管理组
				exit(dr_json(0, lang('023'), 'username'));
			}
			
			$menu = array();
			if ($data['usermenu']) {
				foreach ($data['usermenu']['name'] as $id => $v) {
					if ($v && $data['usermenu']['url'][$id]) {
                        $menu[$id] = array('name' => $v, 'url' => $data['usermenu']['url'][$id]);
                    }
				}
			}
			
			$insert	= array(
				'uid' => $uid,
				'realname' => $data['realname'],
				'usermenu' => dr_array2string($menu)
			);
			$update	= array('adminid' => $data['adminid']);
			exit(dr_json(1, lang('000'), $this->member_model->insert_admin($insert, $update, $uid)));
		}
		
		$this->template->assign('role', $role);
		$this->template->display('admin_add.html');
    }

	/**
     * 修改
     */
    public function edit() {
	
		$uid = (int)$this->input->get('id');
		$data = $this->member_model->get_admin_member($uid);
		if (!$data) {
            exit(lang('019'));
        }
		
		$role = $this->dcache->get('role');
		
		if (IS_POST) {
			$menu = array();
			$data = $this->input->post('data', TRUE);
			if (!$data['adminid'] || !isset($role[$data['adminid']])) {
                exit(dr_json(0, lang('022'), 'adminid'));
            }
			if ($data['usermenu']) {
				foreach ($data['usermenu']['name'] as $id => $v) {
					if ($v && $data['usermenu']['url'][$id]) {
                        $menu[$id] = array('name' => $v, 'url' => $data['usermenu']['url'][$id]);
                    }
				}
			}
			$insert	= array(
				'uid' => $uid,
				'realname' => $data['realname'],
				'usermenu' => dr_array2string($menu)
			);
			$update	= array('adminid' => $data['adminid']);
			exit(dr_json(1, lang('000'), $this->member_model->update_admin($insert, $update, $uid)));
		}
		
		$this->template->assign(array(
			'role' => $role,
			'data' => $data
		));
		$this->template->display('admin_add.html');
    }
	
	/**
     * 修改资料
     */
    public function my() {
		if (IS_POST) {
			$menu = array();
			$data = $this->input->post('data', TRUE);
			$password = $this->input->post('password');
			if ($data['usermenu']) {
				foreach ($data['usermenu']['name'] as $id => $v) {
					if ($v && $data['usermenu']['url'][$id]) {
                        $menu[$id] = array('name' => $v, 'url' => $data['usermenu']['url'][$id]);
                    }
				}
			}
			if ($password) {
				if (defined('UC_KEY')) {
                    uc_user_edit($this->member['username'], '', $password, '', 1);
                }
				$this->db
					 ->where('uid', $this->uid)
					 ->update('member', array('password' => md5(md5($password).$this->member['salt'].md5($password))));
			}
			$this->db
				 ->where('uid', $this->uid)
				 ->update('admin', array( 'realname' => $data['realname'], 'usermenu' => dr_array2string($menu)));
			$this->admin_msg(lang('000'), dr_url('root/my'), 1);
		} else {
			$this->template->display('admin_my.html');
		}
    }
	
	/**
     * 登录日志
     */
    public function log() {

		list($data, $total, $param)	= $this->_limit_page(); // 数据库中分页查询
		
		$uid = (int)$param['uid'];
		
		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				lang('029') => 'admin/root/index',
				lang('add') => 'admin/root/add_js',
				lang('344') => 'admin/root/log/'.(isset($_GET['uid']) && $_GET['uid'] ? 'uid/'.$uid : '')
			)),
			'list' => $data,
			'total' => $total,
			'param' => $param,
			'auser' => $this->db
						    ->where('uid', $uid)
						    ->get('admin')
						    ->row_array(),
			'pages'	=> $this->get_pagination(dr_url('admin/root/log', array(
				'uid' => $uid,
				'total' => $total,
				'search' => IS_POST ? 1 : $this->input->get('search')
			)), $total),
		));
		$this->template->display('admin_log.html');
	}
	
	/**
     * 删除
     */
    public function del() {
		$s=$this->member_model->del_admin((int)$this->input->get('id'));
	if($s==1){
	   exit(dr_json(0, lang('delme')));
	}else{
		exit(dr_json(1, lang('000')));
	}
	}
	
	/**
     * 检查用户情况
     */
	public function check_username() {
		$result = $this->db
                       ->select('uid,adminid')
                       ->where('username', $this->input->post('username', TRUE))
                       ->limit(1)
                       ->get($this->db->dbprefix('member'))
                       ->row_array();
		if (!$result) {
            exit(dr_json(1, lang('024')));
        } // 不存在，注册新会员
		if ($result['adminid'] > 0) {
            exit(dr_json(2, lang('023')));
        } // 已经属于管理组
		exit(dr_json(0, lang('025'), $result['uid'])); // 已经注册会员
	}
	
	private function _where(&$select) {
	
		$uid = (int)$this->input->get('uid');
        $uid = $uid ? $uid : $this->uid;
		$search = $this->input->get('search');
		$cache_file = md5($this->duri->uri(1).$uid.$this->uid.SITE_ID.$this->input->ip_address().$this->input->user_agent()); // 缓存文件名称
		
		// 存在POST提交时，重新生成缓存文件
		if (IS_POST) {
			$data = $this->input->post('data');
			$this->cache->file->save($cache_file, $data, 3600);
			$search = 1;
			unset($_GET['page']);
		}
		
		$select->where('uid', $uid);
		
		// 存在search参数时，读取缓存文件
		if ($search == 1) {
			$data = $this->cache->file->get($cache_file);
			if (isset($data['start']) && $data['start'] && $data['start'] != $data['end']) {
				$data['end'] = $data['end'] ? $data['end'] : SYS_TIME;
				$select->where('logintime BETWEEN '.$data['start'].' AND '. $data['end']);
			}
		}
		
		$data['uid'] = $uid;

		return $data;
	}
	
	/**
	 * 后台数据分页显示
	 *
	 * @return	array	
	 */
	private function _limit_page() {
	
		$page = max((int)$this->input->get('page'), 1);
		$_total = (int)$this->input->get('total');
		
		if (!$_total) {
			$select = $this->db;
			$_param = $this->_where($select);
			$_total = $this->db->count_all_results('admin_login');
			if (!$_total) {
                return array(array(), 0, $_param);
            }
		}

        $select = $this->db;
		$_param = $this->_where($select);
		$result = $this->db
					   ->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1))
					   ->order_by('logintime DESC')
					   ->get('admin_login')
					   ->result_array();
					   
		return array($result, $_total, $_param);
	}
	
}