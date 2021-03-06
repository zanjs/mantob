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
	
class Navigator extends M_Controller {

	private $type;
	private $menu;
	private $field;
    
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$use = $menu = array();
		$data = explode(',', SITE_NAVIGATOR);
		$this->type = (int)$this->input->get('type');
		foreach ($data as $i => $name) {
			if ($name) {
                $use[$i] = $i;
				$menu[$name] = 'admin/navigator/index'.(isset($_GET['type']) || $i ? '/type/'.$i : '');
				$this->menu[$i] = $name;
			}
		}
        // 设置默认选中
        if (!isset($use[$this->type])) {
            $this->type = @reset($use);
            $_SERVER['QUERY_STRING'].= '&type='.$this->type;
        }
        // 带分类参数时的选中
        if (isset($_GET['pid'])) {
            $_SERVER['QUERY_STRING'] = str_replace('&pid='.$_GET['pid'], '', $_SERVER['QUERY_STRING']);
        }
        // 存在导航配置时才显示添加链接
        if ($this->menu) {
            $menu[lang('add')] = 'admin/navigator/add/type/'.$this->type;
        }
		$this->template->assign('menu', $this->get_menu($menu));
		$this->template->assign('name', $this->menu[$this->type]);
        // 导航默认字段
		$this->field = array(
			'name' => array(
				'ismain' => 1,
				'fieldname' => 'name',
				'fieldtype' => 'Text',
				'setting' => array(
					'option' => array(
						'width' => 200,
					)
				)
			),
			'title' => array(
				'ismain' => 1,
				'fieldname' => 'title',
				'fieldtype'	=> 'Text',
				'setting' => array(
					'option' => array(
						'width' => 300,
					)
				)
			),
			'description' => array(
				'ismain' => 1,
				'fieldname' => 'description',
				'fieldtype'	=> 'Textarea',
				'setting' => array(
					'option' => array(
						'width' => 300,
					)
				)
			),
			'url' => array(
				'name' => '',
				'ismain' => 1,
				'fieldname' => 'url',
				'fieldtype'	=> 'Text',
				'setting' => array(
					'option' => array(
						'width' => 400,
						'value' => 'http://',
					)
				)
			),
			'thumb' => array(
				'ismain' => 1,
				'fieldname' => 'thumb',
				'fieldtype' => 'File',
				'setting' => array(
					'option' => array(
						'ext' => 'jpg,gif,png',
						'size' => 10,
					)
				)
			),
		);
        $this->load->model('page_model');
		$this->load->model('navigator_model');
    }
    
	/**
     * 管理列表
     */
    public function index() {
		
		if (IS_POST && $this->input->post('ids')) {
			$table = SITE_ID.'_navigator';
			if ($this->input->post('action') == 'del') {
				// 删除
				$this->navigator_model->delete($this->input->post('ids'));
                $this->cache(1);
			} elseif ($this->input->post('action') == 'order'
                && $this->is_auth('navigator/edit')) {
				// 修改
				$_ids = $this->input->post('ids');
				$_data = $this->input->post('data');
				foreach ($_ids as $id) {
					$this->db
						 ->where('id', (int)$id)
						 ->update($table, $_data[$id]);
				}
				$this->cache(1);
				unset($_ids, $_data);
			}
			exit(dr_json(1, lang('000')));
		}
		
		$this->load->library('dtree');
		$this->dtree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$this->dtree->nbsp = '&nbsp;&nbsp;&nbsp;';
		
		$tree = array();
		$data = $this->navigator_model->get_data($this->type);
		
		if ($data) {
			foreach($data as $t) {
				$add = dr_url('navigator/add', array('pid' => $t['id'], 'type' => $this->type));
				$edit = dr_url('navigator/edit', array('id' => $t['id'], 'type' => $this->type));
				$t['option'] = '';
				if ($this->is_auth('admin/navigator/add')) {
					$t['option'].= '<a class="add" style="margin-top:3px;" title="'.lang('add').'" href="'.$add.'"></a>';
				}
				if ($this->is_auth('admin/navigator/edit')) {
					$t['option'].= '&nbsp;&nbsp;&nbsp;<a title="'.lang('edit').'" href="'.$edit.'">'.lang('edit').'</a>';
				}
				$t['option'].= '&nbsp;&nbsp;<a title="'.lang('go').'" href="'.$t['url'].'" target="_blank">'.lang('go').'</a>';
                if (strpos($t['mark'], 'page') === 0) {
                    //1
                    $t['ntype'] = '<font color=blue>'.lang('128').'</font>';
                } elseif (strpos($t['mark'], 'module') === 0) {
                    //2
                    list($a, $dir, $catid) = explode('-', $t['mark']);
                    $t['ntype'] = '<font color=green>'.lang('html-010').'</font>';
                    if ($catid) {
                        $t['option'].= '&nbsp;&nbsp;<a href="'.dr_url($dir.'/category/add', array('id' => $catid)).'">'.lang('355').'</a>';
                        $t['option'].= '&nbsp;&nbsp;<a href="'.dr_url($dir.'/category/edit', array('id' => $catid)).'">'.lang('356').'</a>';
                    }
                } else {
                    //0
                    $t['ntype'] = lang('198');
                }
                $tree[$t['id']] = $t;
			}
		}
		
		$str = "<tr class='\$class'>";
		$str.= "<td align='right'><input name='ids[]' type='checkbox' class='dr_select' value='\$id' />&nbsp;</td>";
		$str.= "<td align='left'><input class='input-text displayorder' type='text' name='data[\$id][displayorder]' value='\$displayorder' /></td>";
		$str.= "<td align='left'>\$id</td>";
		if ($this->is_auth('admin/navigator/edit')) {
			$str.= "<td>\$spacer<a href='".dr_url(APP_DIR.'/navigator/edit')."&id=\$id&type=".$this->type."'>\$name</a>  \$parent</td>";
		} else {
			$str.= "<td>\$spacer\$name  \$parent</td>";
		}
        $str.= "<td align='center'>\$ntype</td>";
		$str.= "<td align='center'>";
		if ($this->is_auth('admin/navigator/edit')) {
			$str.= "<a href='".dr_url('navigator/target')."&id=\$id'><img src='".SITE_URL."mantob/statics/images/\$target.gif' /></a>";
		} else {
			$str.= "<img src='".SITE_URL."mantob/statics/images/\$target.gif' />";
		}
		$str.= "</td>";
		$str.= "<td align='center'>";
		if ($this->is_auth('admin/navigator/edit')) {
			$str.= "<a href='".dr_url('navigator/show')."&id=\$id'><img src='".SITE_URL."mantob/statics/images/\$show.gif' /></a>";
		} else {
			$str.= "<img src='".SITE_URL."mantob/statics/images/\$show.gif' />";
		}
		$str.= "</td>";
		$str.= "<td align='left'>\$option</td>";
		$str.= "</tr>";
		$this->dtree->init($tree);
		
		$this->template->assign(array(
			'type' => $this->type,
			'list' => $this->dtree->get_tree(0, $str)
		));
		$this->template->display('navigator_index.html');
    }
	
	/**
     * 添加
     */
    public function add() {
		
		$pid = (int)$this->input->get('pid');
		
		if (IS_POST) {
			$data = $this->validate_filter($this->field);
            $ntype = (int)$this->input->post('ntype');
			
            if ($ntype == 0) {
                // 自定义
                $data[1]['mark'] = '';
				
            } elseif ($ntype == 1) {
                // 单页
                $page = $this->input->post('page');
                if (!$page['id']) {
                    // 单页不存在
                    $data = array(
                        'msg' => lang('353'),
                        'error' => 1,
                    );
                } else {
                    $ppid = $page['id'];
					
                    $temp = $this->page_model->get($page['id']);
                    
                    if ($temp) {
                        $data[1]['url'] = $temp['url'];
                        $data[1]['mark'] = 'page-'.$page['id'];
                        $data[1]['name'] = $data[1]['name'] ? $data[1]['name'] : $temp['name'];
                        $data[1]['thumb'] = $temp['thumb'];
                        $data[1]['title'] = $temp['title'];
                        $data[1]['description'] = $temp['description'];
                        $data[1]['extend'] = (int)$page['extend'];
                        $data[1]['extends'] = array();
                        $childs = explode(',', $temp['childids']);
                        if ($childs && $data[1]['extend']) {
                            $page = $this->page_model->get_data_all();
                            foreach ($childs as $i) {
                                if ($i != $ppid) {
                                    $data[1]['extends'][$i] = $page[$i];
                                }
                            }
                        }
                        unset($childs);
                    } else {
                        // 单页不存在
                        $data = array(
                            'msg' => lang('353'),
                            'error' => 1,
                        );
                    }
                }
                unset($temp, $page);
            } elseif ($ntype == 2) {
                // 模块
                $module = $this->input->post('module');
                if (!$module['dir']) {
                    // 模块不存在
                    $data = array(
                        'msg' => lang('354'),
                        'error' => 1,
                    );
                } else {
                    $temp = $this->get_cache('module-'.SITE_ID.'-'.$module['dir']);
                    if ($temp) {
                        $data[1]['url'] = $temp['url'];
                        $data[1]['mark'] = 'module-'.$module['dir'].'-0';
                        $data[1]['name'] = $data[1]['name'] ? $data[1]['name'] : $temp['name'];
                        $data[1]['extend'] = (int)$module['extend'];
                        $data[1]['extends'] = 0;
                        if ($module['extend']) {
                            // 选择的有继承栏目
                            $catid = (int)$module['catid'];
                            $data[1]['mark'] = 'module-'.$module['dir'].'-'.$catid;
                            if (isset($temp['category'][$catid]) && $temp['category'][$catid]) {
                                $data[1]['url'] = $temp['category'][$catid]['url'];
                                $data[1]['name'] = $temp['category'][$catid]['name'];
                                $data[1]['thumb'] = $temp['category'][$catid]['thumb'];
                                $data[1]['extends'] = array();
                                $childs = explode(',', $temp['category'][$catid]['childids']);
                                if ($childs) {
                                    foreach ($childs as $i) {
                                        if ($i != $catid) {
                                            $data[1]['extends'][$i] = $temp['category'][$i];
                                        }
                                    }
                                }
                                unset($childs);
                            } else {
                                $data[1]['extends'] = $temp['category'];
                            }
                        }
                        unset($module);
                    } else {
                        // 模块不存在
                        $data = array(
                            'msg' => lang('354'),
                            'error' => 1,
                        );
                    }
                }
                unset($temp, $page);
            }
			if (isset($data['error'])) {
				$error = $data['msg'];
				$data = $this->input->post('data');
			} else {
				
				$data[1]['pid'] = (int)$this->input->post('pid');				
                $data[1]['type'] = (int)$this->type;
				$id = (int)$this->navigator_model->add($data[1]);
				$this->cache(1);
				$this->attachment_handle($this->uid, $this->navigator_model->tablename.'-'.$id, $this->field);
				$this->admin_msg(lang('000'), dr_url('navigator/index', array('type' => $this->type)), 1);
			}
		} else {
            $error = '';
            $ntype = $ppid =  0;
            $data['extend'] = 1;
        }
		
		$this->template->assign(array(
			'data' => $data,
			'ntype' => $ntype,
			'error' => $error,
			'field' => $this->field,
			'select' => $this->_select($this->navigator_model->get_data($this->type), $pid, 'name=\'pid\'', lang('150')),
			'select_page' => $this->_select($this->page_model->get_data_all(), (int)$ppid, 'name=\'page[id]\'', ''),
		));
		$this->template->display('navigator_add.html');
	}
	
	/**
     * 修改
     */
    public function edit() {
	
		$id = (int)$this->input->get('id');
		$nav = $this->navigator_model->get_data($this->type);
		$data = $nav[$id];
		if (!$data) {
            $this->admin_msg(lang('019'));
        }
        if (strpos($data['mark'], 'page') === 0) {
            list($a, $ppid) = explode('-', $data['mark']);
            $ntype = 1;
        } elseif (strpos($data['mark'], 'module') === 0) {
            list($a, $dir, $catid) = explode('-', $data['mark']);
            $ntype = 2;
        } else {
            $ntype = 0;
        }

		if (IS_POST) {

			$post = $this->validate_filter($this->field);
            $extends = array();

            if ($ntype == 0) {
                // 自定义
                $post[1]['extend'] = 0;
            } elseif ($ntype == 1) {
                // 单页
                $page = $this->input->post('page');
                $post[1]['extend'] = $page['extend'];
                // 查询下级所有数据项
                if ($ppid && $page['extend']) {
                    $temp = $this->page_model->get($ppid);
                    if ($temp) {
                        $childs = explode(',', $temp['childids']);
                        if ($childs) {
                            $page = $this->page_model->get_data_all();
                            foreach ($childs as $i) {
                                if ($i != $ppid) {
                                    $extends[$i] = $page[$i];
                                }
                            }
                        }
                        unset($childs);
                    }
                    unset($temp);
                }
            } elseif ($ntype == 2) {
                // 模块
                $module = $this->input->post('module');
                $post[1]['extend'] = $module['extend'];
                // 查询下级所有数据项
                if ($dir && $module['extend']) {
                    $temp = $this->get_cache('module-'.SITE_ID.'-'.$dir);
                    if ($temp) {
                        // 选择的有继承栏目
                        if (isset($temp['category'][$catid]) && $temp['category'][$catid]) {
                            $childs = explode(',', $temp['category'][$catid]['childids']);
                            if ($childs) {
                                foreach ($childs as $i) {
                                    if ($i != $catid) {
                                        $extends[$i] = $temp['category'][$i];
                                    }
                                }
                            }
                            unset($childs);
                        } else {
                            $extends = $temp['category'];
                        }
                    }
                    unset($temp);
                }
                unset($module);
            }

			if (isset($post['error'])) {
				$data = $this->input->post('data');
				$error = $post['msg'];
			} else {
				$post[1]['pid'] = $this->input->post('pid');
				$id = (int)$this->navigator_model->edit($id, $post[1]);
                if ($ntype && $data['extend'] != $post[1]['extend']) {
                    $this->navigator_model->update_extend($data['childs'], $post[1]['extend']);
                    if ($post[1]['extend'] && $extends) {
                        $this->navigator_model->set_extend($id, $data['mark'], $extends, $this->type);
                    }
                }
				$this->cache(1);
				$this->attachment_handle($this->uid, $this->navigator_model->tablename.'-'.$id, $this->field, $data);
				$this->admin_msg(lang('000'), dr_url('navigator/index', array('type' => $this->type)), 1);
			}
		}
		
		$this->template->assign(array(
            'dir' => $dir,
			'data' => $data,
			'error' => $error,
            'ntype' => $ntype,
            'catid' => (int)$catid,
			'field' => $this->field,
			'select' => $this->_select($nav, $data['pid'], 'name=\'pid\'', lang('150')),
            'select_page' => $this->_select($this->page_model->get_data_all(), (int)$ppid, 'disabled name=\'page[id]\'', ''),
		));
		$this->template->display('navigator_add.html');
	}
	
	/**
     * 新窗口打开
     */
    public function target() {
		if ($this->is_auth('admin/navigator/edit')) {
			$id = (int)$this->input->get('id');
			$data = $this->db
						 ->select('target,type')
						 ->where('id', $id)
						 ->limit(1)
						 ->get(SITE_ID.'_navigator')
						 ->row_array();
			$this->db
				 ->where('id', $id)
				 ->update(SITE_ID.'_navigator', array('target' => ($data['target'] == 1 ? 0 : 1)));
			$this->cache(1);
			
			$this->admin_msg(lang('000'), dr_url('navigator/index', array('type' => $data['type'])), 1);
		} else {
			$this->admin_msg(lang('160'));
		}
    }
	
	/**
     * 显示
     */
    public function show() {
		if ($this->is_auth('admin/navigator/edit')) {
			$id = (int)$this->input->get('id');
			$data = $this->db
						 ->select('show,type')
						 ->where('id', $id)
						 ->limit(1)
						 ->get(SITE_ID.'_navigator')
						 ->row_array();
			$this->db
				 ->where('id', $id)
				 ->update(SITE_ID.'_navigator', array('show' => ($data['show'] == 1 ? 0 : 1)));
			$this->cache(1);	 
				 
			$this->admin_msg(lang('000'), dr_url('navigator/index', array('type' => $data['type'])), 1);
		} else {
			$this->admin_msg(lang('160'));
		}
    }
	
	/**
     * 缓存
	 * array(
	 *			'站点id' =>	array(
	 *						'导航类型id' => array(导航数据),
	 *						... ,
	 *					),
	 *			... ,
	 *		)
     */
    public function cache($update = 0) {
		$this->navigator_model->cache(isset($_GET['site']) && $_GET['site'] ? (int)$_GET['site'] : SITE_ID);
		((int)$_GET['admin']|| $update) or $this->admin_msg(lang('000'), isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', 1);
	}
	
	/**
	 * 上级选择
	 *
	 * @param array			$data		数据
	 * @param intval/array	$id			被选中的ID
	 * @param string		$str		属性
	 * @param string		$default	默认选项
	 * @return string
	 */
	private function _select($data, $id = 0, $str = '', $default = ' -- ') {
	
		$tree = array();
		$string = '<select '.$str.'>';
		
		if ($default) $string.= "<option value='0'>$default</option>";
		
		if (is_array($data)) {
			foreach($data as $t) {
				$t['selected'] = ''; // 选中操作
				if (is_array($id)) {
					$t['selected'] = in_array($t['id'], $id) ? 'selected' : '';
				} elseif(is_numeric($id)) {
					$t['selected'] = $id == $t['id'] ? 'selected' : '';
				}
				
				$tree[$t['id']] = $t;
			}
		}
		
		$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
		$str2 = "<optgroup label='\$spacer \$name'></optgroup>";
		
		$this->load->library('dtree');
		$this->dtree->init($tree);
		
		$string.= $this->dtree->get_tree_category(0, $str, $str2);
		$string.= '</select>';
		
		return $string;
	}
}