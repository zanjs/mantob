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

class Space extends M_Controller {
	
	private $flag;
	
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$menu = array(lang('110') => 'member/admin/space/index');
		$this->flag = $this->get_cache('member', 'setting', 'space', 'flag');
		if ($this->flag && !in_array($_GET['m'], array('init', 'category', 'addinit', 'editinit'))) {
			foreach ($this->flag as $i => $t) {
				if ($t['name']) {
					$menu[$t['name'].'('.$this->db->where('flag', $i)->count_all_results('space_flag').')'] = 'member/admin/space/index/flag/'.$i;
				}
			}
		}
		$this->template->assign('menu', $this->get_menu($menu));
		$this->load->model('space_model');
    }

    /**
     * 首页
     */
    public function index() {
	
		if (IS_AJAX) {
			
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
			
			if ($this->input->post('action') == 'del') {
				$this->space_model->delete($ids);
				exit(dr_json(1, lang('000')));
			} elseif ($this->input->post('action') == 'order') {
				$_data = $this->input->post('data');
				foreach ($ids as $id) {
					$this->db
						 ->where('uid', (int)$id)
						 ->update('space', $_data[$id]);
				}
				exit(dr_json(1, lang('000')));
			} elseif ($this->input->post('action') == 'flag') {
				$flag = $this->input->post('flagid');
				foreach ($ids as $uid) {
					if ($flag > 0) {
						// 增加推荐位
						if (!$this->db->where('uid', (int)$uid)->where('flag', $flag)->count_all_results('space_flag')) {
							$this->db->replace('space_flag', array(
								'uid' => $uid,
								'flag' => $flag,
							));
						}
					} elseif ($flag < 0) {
						// 取消推荐位
						$this->db
							 ->where('uid', (int)$uid)
							 ->where('flag', abs($flag))
							 ->delete('space_flag');
					}
				}
				exit(dr_json(1, lang('000')));
			} else {
				if (!$this->is_auth('member/admin/space/edit')) {
                    exit(dr_json(0, lang('160')));
                }
				$this->db
					 ->where_in('uid', $ids)
					 ->update('space', array('status' => (int)$this->input->post('status')));
				exit(dr_json(1, lang('000')));
			}
		}

        // 重置页数和统计
        if (IS_POST) {
            $_GET['page'] = $_GET['total'] = 0;
        }

		// 根据参数筛选结果
		$param = IS_POST ? $this->input->post('data') : $this->input->get(TRUE);
		if ($this->input->get('flag')) $param['flag'] = (int)$this->input->get('flag');
		unset($param['page']);
		
		// 数据库中分页查询
		list($data, $param)	= $this->space_model->limit_page($param, max((int)$_GET['page'], 1), (int)$_GET['total']);
		
		$this->template->assign(array(
			'list' => $data,
			'flag' => isset($param['flag']) ? $param['flag'] : '',
			'name' => $this->get_cache('member', 'spacefield', 'name', 'name'),
            'param'	=> $param,
			'flags' => $this->flag,
			'pages'	=> $this->get_pagination(dr_url('member/space/index', $param), $param['total'])
		));
		$this->template->display('space_index.html');
    }
	
    /**
     * 空间修改
     */
    public function edit() {
    	
    	$uid = (int)$this->input->get('uid');
    	$data = $this->db
					 ->from($this->db->dbprefix('member').' AS m')
					 ->join($this->db->dbprefix('space').' AS a', 'a.uid=m.uid', 'join')
					 ->select('a.*,m.groupid')
					 ->where('m.uid', $uid)
					 ->limit(1)
					 ->get()
					 ->row_array();
    	if (!$data) {
            $this->admin_msg(lang('m-234'));
        }
    	
		$field = array();
		$MEMBER = $this->get_cache('member');
		$field[] = $MEMBER['spacefield']['name'];
		if ($MEMBER['spacefield'] && $MEMBER['group'][$data['groupid']]['spacefield']) {
			foreach ($MEMBER['spacefield'] as $t) {
				if (in_array($t['fieldname'], $MEMBER['group'][$data['groupid']]['spacefield'])) {
					$field[] = $t;
				}
			}
		}
		
    	if (IS_POST) {
		
			$post = $this->validate_filter($field, $data);
    		$value = $this->input->post('value');
			
			if (isset($post['error'])) {
				$data = $this->input->post('data', TRUE) + $value;
				$error = $post['msg'];
			} else {
				if ($this->db->where('uid <>', $uid)->where('name', $value['name'])->count_all_results('space')) {
					$data = $this->input->post('data', TRUE) + $value;
					$error = lang('html-539');
				} else {
					$data = $post[1] + $value;
					$this->db
						 ->where('uid', $uid)
						 ->update('space', $data);
					$this->attachment_handle($uid, $this->db->dbprefix('space').'-'.$uid, $field, $data);
					$this->member_msg(lang('000'), dr_url('member/space/index'), 1);
				}
			}
    	}
    	
    	$this->template->assign(array(
    		'data' => $data,
    		'error' => $error,
			'myfield' => $this->field_input($field, $data, FALSE, 'uid'),
    	));
    	$this->template->display('space_edit.html');
    }

    /**
     * 空间初始化栏目
     */
    public function init() {
        $this->template->assign('menu', $this->get_menu(array(
            lang('351') => 'member/admin/space/init'
        )));
        $this->template->display('space_init.html');
    }

    /**
     * 空间初始化栏目2
     */
    public function category() {

        $this->load->model('space_init_model');
        if (IS_POST) {
            $this->space_init_model->del($this->input->post('ids'));
            exit(dr_json(1, lang('000')));
        }

        $id = (int)$this->input->get('id');
        $group = $this->get_cache('member', 'group', $id);

        $this->load->library('dtree');
        $this->dtree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
        $this->dtree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $tree = array();
        $this->space_init_model->repair($id);
        $data = $this->space_init_model->get_data(0, $id, 1);

        if ($data) {
            foreach($data as $t) {

                switch ($t['showid']) {
                    case 0:
                        $t['show'] = lang('m-293');
                        break;
                    case 1:
                        $t['show'] = lang('m-294');
                        break;
                    case 2:
                        $t['show'] = lang('m-295');
                        break;
                    case 3:
                        $t['show'] = lang('m-296');
                        break;
                }

                switch ((int)$t['type']) {
                    case 0:
                        $t['model'] = lang('m-313');
                        break;
                    case 1:
                        $t['model'] = $this->get_cache('space-model', $t['modelid'], 'name');
                        break;
                    case 2:
                        $t['model'] = lang('m-314');
                        break;
                }
                $t['option'] = '<a href="'.dr_url('member/space/addinit', array('gid' => $id, 'type' => $t['type'], 'mid' => $t['modelid'], 'pid' => $t['id'])).'">'.lang('m-298').'</a>&nbsp;&nbsp;';
                $t['option'] = $t['type'] ? $t['option'] : '';
                $t['option'].= '<a href="'.dr_url('member/space/editinit', array('gid' => $id, 'id' => $t['id'])).'">'.lang('edit').'</a>';
                $tree[$t['id']] = $t;
            }
        }

        $str = "<tr>";
        $str.= "<td align='right'><input name='ids[]' type='checkbox' class='dr_select' value='\$id' /></td>";
        $str.= "<td>\$spacer<a href='".dr_url('member/space/editinit')."&gid=".$id."&id=\$id'>\$name</a></td>";
        $str.= "<td align='center'>\$model</td>";
        $str.= "<td align='center'>\$show</td>";
        $str.= "<td align='left'>\$option</td>";
        $str.= "</tr>";

        $this->dtree->init($tree);

        $this->template->assign(array(
            'list' => $this->dtree->get_tree(0, $str),
            'menu' => $this->get_menu(array(
                lang('351') => 'member/admin/space/init',
                $group['name'] => 'member/admin/space/category/id/'.$id,
                lang('add') => 'member/admin/space/addinit/gid/'.$id,
            ))
        ));
        $this->template->display('space_init_list.html');
    }

    /**
     * 添加
     */
    public function addinit() {

        $gid = (int)$this->input->get('gid');
        $data = array(
            'pid' => (int)$this->input->get('pid'),
            'type' => (int)$this->input->get('type'),
            'showid' => 3,
            'modelid' => (int)$this->input->get('mid'),
        );

        if (IS_POST) {
            $post = $this->input->post('data', TRUE);
            $post['gid'] = $gid;
            $post['modelid'] = $post['modelid'] ? $post['modelid'] : $data['modelid'];
            $this->load->model('space_init_model');
            $result = $this->space_init_model->add($post);
            if ($result === TRUE) {
                $this->member_msg(lang('000'), dr_url('member/space/category', array('id' => $gid)), 1);
            }
            $data = $post;
        } else {
            $result	= '';
        }

        $this->template->assign(array(
            'menu' => $this->get_menu(array(
                lang('351') => 'member/admin/space/init',
                $this->get_cache('member', 'group', $gid, 'name') => 'member/admin/space/category/id/'.$gid,
                lang('add') => 'member/admin/space/addinit/gid/'.$gid,
            )),
            'data' => $data,
            'result' => $result,
        ));
        $this->template->display('space_init_add.html');
    }

    /**
     * 修改
     */
    public function editinit() {

        $id = (int)$this->input->get('id');
        $gid = (int)$this->input->get('gid');
        $this->load->model('space_init_model');
        $data = $this->space_init_model->get($id);
        if (!$data)	{
            $this->member_msg(lang('019'));
        }

        $is_edit = $this->get_cache('member', 'setting', 'space', 'category') ? 0 : 1;

        if (IS_POST) {
            $post = $this->input->post('data', TRUE);
            $post['pid'] = $is_edit ? $post['pid'] : $data['pid'];
            $post['type'] = $data['type'];
            $post['modelid'] = $data['modelid'];
            $result	= $this->space_init_model->edit($id, $post);
            if ($result === TRUE) {
                $this->member_msg(lang('000'), dr_url('member/space/category', array('id' => $gid)), 1);
            }
            $post['id'] = $id;
            $data = $post;
        } else {
            $result	= '';
        }

        $this->template->assign(array(
            'menu' => $this->get_menu(array(
                lang('351') => 'member/admin/space/init',
                $this->get_cache('member', 'group', $gid, 'name') => 'member/admin/space/category/id/'.$gid,
                lang('add') => 'member/admin/space/addinit/gid/'.$gid,
                lang('edit') => 'member/admin/space/editinit/gid/'.$gid.'/id/'.$id,
            )),
            'data' => $data,
            'result' => $result,
        ));
        $this->template->display('space_init_add.html');
    }

    /**
     * 同步分类
     */
    public function syn() {

        $gid = (int)$this->input->get('gid');
        $group = $this->get_cache('member', 'group');

        if (IS_POST) {
            $syn = $this->input->post('syn');
            $this->load->model('space_init_model');
            $category = $this->space_init_model->get_data(0, $gid, 1);
            if ($syn && $category) {
                foreach ($syn as $id) {
                    $this->db->where('gid', (int)$id)->delete('space_category_init');
                    $pids = array();
                    foreach ($category as $i => $t) {
                        $this->db->insert('space_category_init', array(
                            'gid' => (int)$id,
                            'pid' => $t['pid'] ? (int)$pids[$t['pid']] : 0,
                            'type' => (int)$t['type'],
                            'name' => trim($t['name']),
                            'link' => trim($t['link']),
                            'showid' => (int)$t['showid'],
                            'modelid' => (int)$t['modelid'],
                        ));
                        $pids[$i] = $this->db->insert_id();
                    }
                    $this->space_init_model->repair($id);
                }
                $this->member_msg(lang('000'), dr_url('member/space/syn', array('gid' => $gid)), 1);
            }
        }

        $this->template->assign(array(
            'menu' => $this->get_menu(array(
                lang('351') => 'member/admin/space/init',
                $group[$gid]['name'] => 'member/admin/space/category/id/'.$gid,
                lang('add') => 'member/admin/space/addinit/gid/'.$gid,
                lang('html-219') => 'member/admin/space/syn/gid/'.$gid,
            )),
            'group' => $group
        ));
        $this->template->display('space_init_syn.html');

    }

    /**
     * 栏目分类
     */
    public function select() {

        $pid = (int)$this->input->get('pid');
        $mid = (int)$this->input->get('mid');
        $gid = (int)$this->input->get('gid');
        $type = (int)$this->input->get('type');

        $this->db->where('gid', $gid);

        switch ($type) {

            case 0: // 外链
                $this->db->where('type>', 0);
                break;

            case 1: // 模型
                $this->db->where('(type=1 and modelid='.$mid.') or type=2');
                break;

            case 2: // 单页
                $this->db->where('type>', 0);
                break;
        }

        $data = $this->db->get('space_category_init')->result_array();

        echo $this->select_space_category($data, $pid, 'name=\'data[pid]\' style=\'margin-top:7px;\'', lang('m-297'));
    }

}