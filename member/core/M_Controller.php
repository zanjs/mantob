<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.1
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
require FCPATH.'mantob/core/D_Common.php';

class M_Controller extends D_Common {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		if (defined('DR_PAY_ID') && DR_PAY_ID) {
            $this->load->model('pay_model');
			require FCPATH.'member/pay/'.DR_PAY_ID.'/call.php';
		} elseif (defined('DR_UEDITOR') && is_dir(APPPATH.DR_UEDITOR)) {
            require APPPATH.DR_UEDITOR.'/php/controller.php';
            exit;
        } else {
			$this->template->assign('newpm', $this->db->where('uid', (int)$this->uid)->get('newpm')->row_array());
		}
    }
	
	/**
     * 空间模型管理
     */
	protected function space_content_index() {
	
		$this->_is_space();
		
		$mid = (int)str_replace('space', '', $this->router->class);
		$model = $this->get_cache('space-model', $mid);
		if (!$model) {
            $this->member_msg(lang('m-290'));
        }

		if (!$model['setting'][$this->markrule]['use']) {
            $this->member_msg(lang('m-307'));
        }
		
		$table = $this->db->dbprefix('space_'.$model['table']);
		
		if (IS_POST && $this->input->post('action') == 'delete') {
		
			$id = (int)$this->input->post('id');
			$this->db->where('id', $id)->delete($table);
			
			$this->load->model('attachment_model');
			$this->attachment_model->delete_for_table($table.'-'.$id); // 删除附件
			
			// 积分处理
			$experience = (int)$model['setting'][$this->markrule]['experience'];
			if ($experience > 0) {
                $this->member_model->update_score(0, $this->uid, -$experience, '', "delete");
            }
			// 虚拟币处理
			$score = (int)$model['setting'][$this->markrule]['score'];
			if ($score > 0) {
                $this->member_model->update_score(1, $this->uid, -$score, '', "delete");
            }
			
			exit(dr_json(1, lang('000'), $id));
			
		} elseif (IS_POST && $this->input->post('action') == 'remove') {
		
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('019')));
            }
			
			$catid = (int)$this->input->post('catid');
			if ($catid) {
				$this->db
					 ->where_in('id', $ids)
					 ->update($table, array(
						'catid' => $catid
					 ));
			} else {
				exit(dr_json(0, lang('m-300')));
			}
			
			exit(dr_json(1, lang('000')));
		}
		
		$this->load->model('space_category_model');
		$category = $this->space_category_model->get_data($mid);
		
		$this->db->where('uid', (int)$this->uid);
		$kw = $this->input->get('kw', TRUE);
		$order = $this->input->get('order', TRUE);
		if ($kw) {
            $this->db->like('title', $kw);
        }
		$this->db->order_by($order ? $order : 'updatetime DESC');
		
		if ($this->input->get('action') == 'search') {
			// ajax搜索数据
			$page = max((int)$this->input->get('page'), 1);
			$data = $this->db
						 ->limit($this->pagesize, $this->pagesize * ($page - 1))
						 ->get($table)
						 ->result_array();
			if (!$data) {
                exit('null');
            }
			$this->template->assign(array(
				'kw' => $kw,
                'list' => $data,
				'category' => $category,
            ));
			$this->template->display(is_file(FCPATH.'member/templates/'.MEMBER_TEMPLATE.'/space_'.$model['table'].'_data.html') ? 'space_'.$model['table'].'_data.html' : 'space_content_data.html');
		} else {
			$this->template->assign(array(
				'kw' => $kw,
				'mid' => $mid,
				'list' => $this->db
							   ->limit($this->pagesize)
							   ->get($table)
							   ->result_array(),
				'select' => $this->select_space_category($category, 0, 'name=\'catid\'', '  --  ', 1),
				'dclass' => $this->router->class,
				'category' => $category,
				'searchurl' => "index.php?c={$this->router->class}&m=index&action=search"
			));
			$this->template->display(is_file(FCPATH.'member/templates/'.MEMBER_TEMPLATE.'/space_'.$model['table'].'_index.html') ? 'space_'.$model['table'].'_index.html' : 'space_content_index.html');
		}
	}

	/**
     * 添加空间模型内容
     */
	protected function space_content_add() {
	
		$this->_is_space();
		
		$mid = (int)str_replace('space', '', $this->router->class);
		$model = $this->get_cache('space-model', $mid);
		if (!$model) {
            $this->member_msg(lang('m-290'));
        }
		if (!$model['setting'][$this->markrule]['use']) {
            $this->member_msg(lang('m-307'));
        }
		
		$this->load->model('space_content_model');
		$this->load->model('space_category_model');
		$category = $this->space_category_model->get_data($mid);
		$this->space_content_model->tablename = $this->db->dbprefix('space_'.$model['table']);
		
		// 虚拟币检查
		$score = (int)$model['setting'][$this->markrule]['score'];
		if ($score && $score + $this->member['score'] < 0) {
            $this->member_msg(dr_lang('m-302', abs($score), $this->member['score']));
        }
		// 日投稿上限检查
		if ($model['setting'][$this->markrule]['postnum']) {
			$total = $this->db
						  ->where('uid', $this->uid)
						  ->where('DATEDIFF(from_unixtime(inputtime),now())=0')
						  ->count_all_results($this->space_content_model->tablename);
			if ($total >= $model['setting'][$this->markrule]['postnum']) {
				$this->member_msg(dr_lang('m-287', $model['setting'][$this->markrule]['postnum']));
			}
		}
		// 投稿总数检查
		if ($model['setting'][$this->markrule]['postcount']) {
			$total = $this->db
						  ->where('uid', $this->uid)
						  ->count_all_results($this->space_content_model->tablename);
			if ($total >= $model['setting'][$this->markrule]['postcount']) {
				$this->member_msg(dr_lang('m-288', $model['setting'][$this->markrule]['postcount']));
			}
		}
		if (IS_POST) {
			
			// 栏目参数
			$catid = (int)$this->input->post('catid');
			
			// 设置uid便于校验处理
			$_POST['data']['uid'] = $this->uid;
			$_POST['data']['author'] = $this->member['username'];
			$_POST['data']['inputtime'] = $_POST['data']['updatetime'] = SYS_TIME;
			$data = $this->validate_filter($model['field']);
			
			// 验证出错信息
			if (isset($data['error'])) {
				$error = $data;
				$data = $this->input->post('data', TRUE);
			} elseif (!$catid) {
				$data = $this->input->post('data', TRUE);
				$error = array('error' => 'catid', 'msg' => lang('m-300'));
			} elseif ($category[$catid]['child'] || $category[$catid]['modelid'] != $mid) {
				$data = $this->input->post('data', TRUE);
				$error = array('error' => 'catid', 'msg' => lang('m-301'));
			} else {
			
				// 设定文档默认值
				$data[1]['uid'] = $this->uid;
				$data[1]['catid'] = $catid;
				$data[1]['status'] = (int)$model['setting'][$this->markrule]['verify'] ? 0 : 1;
				$data[1]['author'] = $this->member['username'];
				$data[1]['inputtime'] = $data[1]['updatetime'] = SYS_TIME;
				$data[1]['displayorder'] = $data[1]['hits'] = 0;
				
				// 发布文档
				if (($id = $this->space_content_model->add($data[1])) != FALSE) {
					$mark = $this->space_content_model->tablename.'-'.$id;
					if ($data[1]['status']) {
						// 积分处理
						$experience = (int)$model['setting'][$this->markrule]['experience'];
						if ($experience) {
                            $this->member_model->update_score(0, $this->uid, $experience, $mark, "lang,m-151,{$category[$catid]['name']}", 1);
                        }
						// 虚拟币处理
						$score = (int)$model['setting'][$this->markrule]['score'];
						if ($score) {
                            $this->member_model->update_score(1, $this->uid, $score, $mark, "lang,m-151,{$category[$catid]['name']}", 1);
                        }
					}
					// 附件归档到文档
					$this->attachment_handle($this->uid, $mark, $model['field']);
					$this->attachment_replace($this->uid, $id, $this->space_content_model->tablename);
					$this->member_msg(lang('000'), dr_member_url($this->router->class.'/index'), 1);
				}
			}
			
			if (IS_AJAX) {
                exit(dr_json(0, $error['msg'], $error['error']));
            }
			
			$data = $data[1];
			unset($data['id']);
		}
		
		$this->template->assign(array(
			'purl' => dr_url($this->router->class.'/add'),
			'error' => $error,
			'verify' => 0,
			'select' => $this->select_space_category($category, (int)$data['catid'], 'name=\'catid\'', NULL, 1),
			'listurl' => dr_url($this->router->class.'/index'),
			'myfield' => $this->field_input($model['field'], $data, TRUE),
			'meta_name' => lang('m-299'),
			'model_name' => $model['name'],
			'result_error' => $error,
		));
		$this->template->display(is_file(FCPATH.'member/templates/'.MEMBER_TEMPLATE.'/space_'.$model['table'].'_add.html') ? 'space_'.$model['table'].'_add.html' : 'space_content_add.html');
	}
	
	/**
     * 修改空间模型内容
     */
	protected function space_content_edit() {
		
		$this->_is_space();
		
		$id = (int)$this->input->get('id');
		$mid = (int)str_replace('space', '', $this->router->class);
		$model = $this->get_cache('space-model', $mid);
		if (!$model) {
            $this->member_msg(lang('m-290'));
        }
		if (!$model['setting'][$this->markrule]['use']) {
            $this->member_msg(lang('m-307'));
        }
		
		$this->load->model('space_category_model');
		$this->load->model('space_content_model');
		$category = $this->space_category_model->get_data($mid);
		$this->space_content_model->tablename = $this->db->dbprefix('space_'.$model['table']);
		$data = $this->space_content_model->get($this->uid, $id);
		if (!$data) {
            $this->member_msg(lang('m-303'));
        }
		
		if (IS_POST) {
			
			// 栏目参数
			$catid = (int)$this->input->post('catid');
			
			// 设置uid便于校验处理
			$_POST['data']['updatetime'] = SYS_TIME;
			$post = $this->validate_filter($model['field']);
			
			// 验证出错信息
			if (isset($post['error'])) {
				$error = $post;
				$data = $this->input->post('data', TRUE);
			} elseif (!$catid) {
				$data = $this->input->post('data', TRUE);
				$error = array('error' => 'catid', 'msg' => lang('m-300'));
			} elseif ($category[$catid]['child'] || $category[$catid]['modelid'] != $mid) {
				$data = $this->input->post('data', TRUE);
				$error = array('error' => 'catid', 'msg' => lang('m-301'));
			} else {
			
				// 设定文档默认值
				$post[1]['catid'] = $catid;
				$post[1]['status'] = (int)$model['setting'][$this->markrule]['verify'] ? 0 : 1;
				$post[1]['updatetime'] = SYS_TIME;
				
				// 修改文档
				if (($id = $this->space_content_model->edit($id, $data['uid'], $post[1])) != FALSE) {
					$this->attachment_handle($this->uid, $this->space_content_model->tablename.'-'.$id, $model['field'], $data, $post[1]['status'] ? TRUE : FALSE);
					$this->member_msg(lang('000'), dr_member_url($this->router->class.'/index'), 1);
				}
			}
			
			if (IS_AJAX) {
                exit(dr_json(0, $error['msg'], $error['error']));
            }
			
			$data = $data[1];
			unset($data['id']);
		}
		
		$this->template->assign(array(
			'purl' => dr_url($this->router->class.'/edit', array('id'=>$id)),
			'error' => $error,
			'verify' => 0,
			'select' => $this->select_space_category($category, (int)$data['catid'], 'name=\'catid\'', NULL, 1),
			'listurl' => dr_url($this->router->class.'/index'),
			'myfield' => $this->field_input($model['field'], $data, TRUE),
			'meta_name' => lang('m-299'),
			'model_name' => $model['name'],
            'result_error' => $error,
		));
		$this->template->display(is_file(FCPATH.'member/templates/'.MEMBER_TEMPLATE.'/space_'.$model['table'].'_add.html') ? 'space_'.$model['table'].'_add.html' : 'space_content_add.html');
	}
	
	/**
     * 判断当前空间是否可以使用
     */
	protected function _is_space($return = FALSE) {
	
		if (!MEMBER_OPEN_SPACE) {
            $this->member_msg(lang('m-111'));
        }
	
		// 判断会员组是否可以使用
		if (!$this->member['allowspace']) {
			if ($return) {
				return FALSE;
			} else {
				$this->member_msg(lang('m-342'));
			}
		}
		
		// 空间状态判断
		$data = $this->db
					 ->select('status')
					 ->where('uid', (int)$this->uid)
					 ->limit(1)
					 ->get('space')
					 ->row_array();
					 
		if (!$data) {
			if ($return) {
				return FALSE;
			} else {
				$this->member_msg(lang('m-234'));
			}
		}
		
		if (!$data['status']) {
			if ($return) {
				return FALSE;
			} else {
				$this->member_msg(lang('m-235'));
			}
		}
	}
	
	/**
	 * 栏目选择
	 *
	 * @param array			$data		栏目数据
	 * @param intval/array	$id			被选中的ID，多选是可以是数组
	 * @param string		$str		属性
	 * @param string		$default	默认选项
	 * @param intval		$onlysub	只可选择子栏目
	 * @param intval		$is_push	是否验证权限
	 * @return string
	 */
	public function select_space_category($data, $id = 0, $str = '', $default = ' -- ', $onlysub = 0, $is_push = 0) {
		
		$cache = md5(dr_array2string($data).$id.$str.$default.$onlysub.$is_push);
		if ($cache_data = $this->cache->file->get($cache)) {
            return $cache_data;
        }
		
		$tree = array();
		$string = '<select '.$str.'>';
		
		if ($default) {
            $string .= "<option value='0'>$default</option>";
        }
		
		if (is_array($data)) {
		
			foreach($data as $t) {
			
				// 选中操作
				$t['selected'] = '';
				if (is_array($id)) {
					$t['selected'] = in_array($t['id'], $id) ? 'selected' : '';
				} elseif(is_numeric($id)) {
					$t['selected'] = $id == $t['id'] ? 'selected' : '';
				}
				
				// 是否可选子栏目
				$t['html_disabled'] = !empty($onlysub) && $t['child'] != 0 ? 1 : 0;
				
				$tree[$t['id']] = $t;
			}
		}
		
		$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
		$str2 = "<optgroup label='\$spacer \$name'></optgroup>";
		
		$this->load->library('dtree');
		$this->dtree->init($tree);
		
		$string .= $this->dtree->get_tree_category(0, $str, $str2);
		$string .= '</select>';
		
		$this->cache->file->save($cache, $string, 7200);
		
		return $string;
	}
	
	/**
	 * 验证会员名称
	 *
	 * @param	string	$username
	 * @return	NULL
	 */
	protected function is_username($username) {
		
		if (!$username) return lang('m-008');
		
		$setting = $this->get_cache('member', 'setting');
		if ($setting['regnamerule'] && !preg_match($setting['regnamerule'], $username)) return lang('m-008');
		if ($setting['regnotallow'] && @in_array($username, explode(',', $setting['regnotallow']))) return lang('m-010');
		
		return NULL;
	}
	
	/**
	 * 验证Email
	 *
	 * @param	string	$email
	 * @return	NULL
	 */
	protected function is_email($email) {
		
		if (!$email) return lang('m-011');
		
		if (!preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/', $email)) return lang('m-011');
		
		return NULL;
	}
	
	/**
	 * 本地会员空间
	 *
	 * @return	array
	 */
	protected function get_local_space() {
		
		$this->load->helper('directory');
		$file = directory_map(FCPATH.'member/templates/', 1);
		$data = array();
		if ($file) {
			foreach ($file as $t) {
				$t = basename($t);
				$config = FCPATH.'member/templates/'.$t.'/config.php';
				if (!in_array($t, array('admin', 'member')) && is_file($config)) {
					$data[$t] = require $config;
				}
			}
		}
		return $data;
	}
}