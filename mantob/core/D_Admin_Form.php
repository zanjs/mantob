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

class D_Admin_Form extends M_Controller {

	public $ids; // 可操作的所有内容id
	public $fid; // 表单id
	public $cid; // 内容id
	protected $form; // 表单信息
	protected $cdata; // 内容数据
	protected $field; // 全部字段
	protected $sysfield; // 系统字段
	protected $cache_file; // 缓存文件名
	
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		// 表单验证
		$this->fid = (int)trim(strrchr($this->router->class, '_'), '_');
		$this->form = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'form', $this->fid);
		if (!$this->form) {
            $this->admin_msg(lang('247'));
        }
		// 内容验证
		$this->cid = (int)$this->input->get('cid');
		$this->cdata = $this->link
							->where('id', $this->cid)
							->get(SITE_ID.'_'.APP_DIR)
							->row_array();
		if ($this->cid && !$this->cdata) {
            $this->admin_msg(lang('019'));
        }
		if ($this->admin['adminid'] > 1) {
			// 判断角色权限
		}
		// 系统字段
		$this->load->library('Dfield', array(APP_DIR));
		$this->sysfield = array(
			'author' => array(
				'name' => lang('101'),
				'ismain' => 1,
				'fieldtype' => 'Text',
				'fieldname' => 'author',
				'setting' => array(
					'option' => array(
						'width' => 157,
						'value'	=> $this->admin['username']
					),
					'validate' => array(
						'tips' => lang('102'),
						'check' => '_check_member',
						'required' => 1,
						'formattr' => ' disabled /><input type="button" class="button" value="'.lang('103').'" onclick="dr_dialog_member(\'author\')" name="user"',
					)
				)
			),
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
						'required' => 1,
						'formattr' => '',
					)
				)
			),
			'inputip' => array(
				'name' => lang('106'),
				'ismain' => 1,
				'fieldtype' => 'Text',
				'fieldname' => 'inputip',
				'setting' => array(
					'option' => array(
						'width' => 157,
						'value' => $this->input->ip_address()
					),
					'validate' => array(
						'formattr' => ' /><input type="button" class="button" value="'.lang('107').'" onclick="dr_dialog_ip(\'inputip\')" name="ip"',
					)
				)
			)
		);
		$this->field = $this->form['field'] ? array_merge($this->form['field'], $this->sysfield) : $this->sysfield;
		$this->load->model('mform_model');
        if ($this->cid) {
            $menu = $this->get_menu(array(
                lang('back') => APP_DIR.'/admin/home/index/catid/'.$this->cdata['catid'],
                $this->form['name'] => APP_DIR.'/admin/'.$this->router->class.'/index/cid/'.$this->cid,
                lang('331') => MODULE_URL.'index.php?c='.$this->router->class.'&cid='.$this->cid.'" target="_blank',
            ));
        } else {
            $menu = $this->get_menu(array(
                $this->form['name'] => APP_DIR.'/admin/'.$this->router->class.'/index',
            ));
        }
        // 判断栏目权限，如果数据量大时可以注释此判断
        if (IS_ADMIN && $this->admin['adminid'] > 1) {
            $category = $this->get_cache('module-' . SITE_ID . '-' . APP_DIR, 'category');
            if ($category) {
                $catid = array();
                foreach ($category as $c) {
                    // 具有管理权限的栏目id集合
                    if (!$c['child']
                        && $c['setting']['admin'][$this->admin['adminid']]['show'] == 1) {
                        $catid[] = $c['id'];
                    }
                }
                unset($category);
                if ($catid) {
                    $data = $this->link
                                 ->select('id')
                                 ->where_in('catid', $catid)
                                 ->get(SITE_ID.'_'.APP_DIR.'_index')
                                 ->result_array();
                    if ($data) {
                        foreach ($data as $t) {
                            $this->ids[] = (int)$t['id'];
                        }
                    }
                }

            }
        }
		$this->template->assign(array(
            'cid' => $this->cid,
			'menu' => $menu,
			'field' => $this->field,
			'_class' => $this->router->class,
		));
		$this->cache_file = md5($this->duri->uri(1).$this->uid.SITE_ID.$this->input->ip_address().$this->input->user_agent()); // 缓存文件名称
	}
	
	/**
	 * 条件查询
	 *
	 * @param	object	$select	查询对象
	 * @param	intval	$where	是否搜索
	 * @return	intval	
	 */
	protected function _where(&$select, $where) {
	
		// 存在POST提交时，重新生成缓存文件
		if (IS_POST) {
			$data = $this->input->post('data');
			$this->cache->file->save($this->cache_file, $data, 3600);
			$where = 1;
		}

        // 相对于内容
        if ($this->cid) {
		    $select->where('cid', $this->cid);
        }

        // 权限筛选
        if ($this->ids) {
            $select->where_in('cid', $this->ids);
        }
		
		// 存在search参数时，读取缓存文件
		if ($where) {
			$data = $this->cache->file->get($this->cache_file);
			if (isset($data['keyword']) && $data['keyword'] && $data['field']) {
				$select->like($data['field'], $data['keyword']);
			}
            // 时间搜索
            if (isset($data['start']) && $data['start']) {
                $data['end'] = strtotime(date('Y-m-d 23:59:59', $data['end'] ? $data['end'] : SYS_TIME));
                $data['start'] = strtotime(date('Y-m-d 00:00:00', $data['start']));
                $select->where('inputtime BETWEEN ' . $data['start'] . ' AND ' . $data['end']);
            } elseif (isset($data['end']) && $data['end']) {
                $data['end'] = strtotime(date('Y-m-d 23:59:59', $data['end']));
                $data['start'] = 0;
                $select->where('inputtime BETWEEN ' . $data['start'] . ' AND ' . $data['end']);
            }
		}
		
		return $where;
	}
	
	/**
	 * 数据分页显示
	 *
	 * @return	array	
	 */
	protected function limit_page() {

        if (IS_POST) {
            $page = 1;
            $total = 0;
        } else {
            $page = max(1, (int)$this->input->get('page'));
            $total = (int)$this->input->get('total');
        }

		$where = (int)$this->input->get('where');
		$table = SITE_ID.'_'.APP_DIR.'_form_'.$this->fid;
		
		if (!$total) {
			$select	= $this->db->select('count(*) as total');
			$where = $this->_where($select, $where);
			$data = $select->get($table)->row_array();
			unset($select);
			$total = (int)$data['total'];
			if (!$total) {
                return array(array(), $total, $where);
            }
		}
		
		$select	= $this->db->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1));
		$where = $this->_where($select, $where);
		$data = $select->order_by('inputtime DESC')->get($table)->result_array();
					   
		return array($data, $total, $where);
	}

    /**
     * 管理
     */
    public function index() {
	
		if ($this->input->post('action') == 'del') {
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
			// 删除表对应的附件
			$table = SITE_ID.'_'.APP_DIR.'_form_'.$this->fid;
			$this->load->model('attachment_model');
			foreach ($ids as $id) {
				$this->link->where('id', $id)->delete($table);
                $this->attachment_model->delete_for_table($table.'-'.$id);
			}
			exit(dr_json(1, lang('000')));
		}
		
		// 数据库中分页查询
		list($data, $total, $where)	= $this->limit_page();
		$tpl = APPPATH.'templates/admin/mform_listc_'.SITE_ID.'_'.$this->fid.'.html';

		$this->template->assign(array(
			'tpl' => str_replace(FCPATH, '/', $tpl),
			'list' => $data,
			'total' => $total,
			'pages'	=> $this->get_pagination(dr_url(APP_DIR.'/'.$this->router->class.'/index', array('cid' => $this->cid, 'total' => $total, 'where' => $where)), $total),
			'param' => $where ? $this->cache->file->get($this->cache_file) : array(),
		));
		$this->template->display(is_file($tpl) ? basename($tpl) : 'mform_listc.html');
    }
    
	/**
     * 修改
     */
    public function edit() {
	
		$id = (int)$this->input->get('id');
		$data = $this->mform_model->get($id, $this->fid);
		$error = array();
		$result = '';
		if (!$data) {
            $this->admin_msg(lang('019'));
        }
        // 无权限操作
        if ($this->ids && !in_array($data['cid'], $this->ids)) {
            $this->admin_msg(dr_lang('049', $data['id']));
        }
		
		if (IS_POST) {
			// 设置uid便于校验处理
			$_POST['data']['id'] = $id;
			$_POST['data']['uid'] = $data['uid'];
			$_POST['data']['author'] = $data['author'];
			$post = $this->validate_filter($this->field, $data);
			if (isset($data['error'])) {
				$error = $data;
				$data = $this->input->post('data', TRUE);
			} else {
				$post[1]['uid'] = $data['uid'];
				$post[1]['author'] = $data['author'];
				$table = $this->db->dbprefix(SITE_ID.'_'.APP_DIR.'_form_'.$this->fid);
				$this->link
					 ->where('id', $id)
					 ->update($table, $post[1]);
				// 操作成功处理附件
				$this->attachment_handle($data['uid'], $table.'-'.$id, $this->field, $post);
				$this->admin_msg(lang('000'), dr_url(APP_DIR.'/'.$this->router->class.'/index', array('cid' => $this->cid)), 1, 0);
			}
		}
		
		$tpl = APPPATH.'templates/admin/mform_editc_'.SITE_ID.'_'.$this->fid.'.html';
		$this->template->assign(array(
			'tpl' => str_replace(FCPATH, '/', $tpl),
			'data' => $data,
			'error' => $error,
			'result' => $result,
			'myfield' => $this->field_input($this->field, $data, TRUE)
		));
		$this->template->display(is_file($tpl) ? basename($tpl) : 'mform_editc.html');
    }
    
}