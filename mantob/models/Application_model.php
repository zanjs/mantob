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
	
class Application_model extends CI_Model {
	
	/**
	 * 应用模型类
	 */
    public function __construct() {
        parent::__construct();
	}
	
	/**
	 * 所有应用
	 *
	 * @return	array
	 */
	public function get_data() {
	
		$data = $this->db
					 ->order_by('id ASC')
					 ->get('application')
					 ->result_array();
		if (!$data) {
            return NULL;
        }
		
		$app = array();
		foreach ($data as $t) {
			$t['module'] = dr_string2array($t['module']);
			$t['setting'] = dr_string2array($t['setting']);
			$app[$t['dirname']] = $t;
		}
		
		return $app;
	}
	
	/**
	 * 应用数据
	 *
	 * @param	string	$dir
	 * @return	array
	 */
	public function get($dir) {
	
		$data = $this->db
					 ->limit(1)
					 ->where('dirname', $dir)
					 ->get('application')
					 ->row_array();
		if (!$data) {
            return NULL;
        }
		
		$data['module'] = dr_string2array($data['module']);
		$data['setting'] = dr_string2array($data['setting']);
		
		return $data;
	}
	
	/**
	 * 应用入库
	 *
	 * @param	string	$dir
	 * @return	intval
	 */
	public function add($dir) {
	
		if (!$dir) {
            return NULL;
        }
		
		$this->db->insert('application', array(
			'module' => '',
			'dirname' => $dir,
			'setting' => '',
			'disabled' => 0,
		));
		$id = $this->db->insert_id();
		if (!$id) {
            return NULL;
        }
		
		return $id;
	}
	
	/**
	 * 修改应用配置
	 *
	 * @param	intval	$id
	 * @param	array	$data
	 * @return	bool
	 */
	public function edit($id, $data) {
	
		if (!$id) {
            return FALSE;
        }
		
		$this->db
			 ->where('id', (int)$id)
			 ->update('application', $data);
		
		return TRUE;
	}
	
	/**
	 * 删除应用
	 *
	 * @param	intval	$id
	 * @return	bool
	 */
	public function del($id) {
	
		if (!$id) {
            return FALSE;
        }
		
		$this->db
			 ->where('id', (int)$id)
			 ->delete('application');
		$this->cache();
			 
		return TRUE;
	}
	
	/**
	 * 应用缓存
	 */
	public function cache() {

        $cache = array();

        // 删除应用缓存
		$this->dcache->delete('app');

        // 搜索本地应用
        $local = dr_dir_map(FCPATH.'app/', 1);
        if ($local) {
            foreach ($local as $dir) {
                if (is_file(FCPATH.'app/'.$dir.'/config/app.php')
                    && $this->db
                            ->where('dirname', $dir)
                            ->where('disabled', 0)
                            ->count_all_results('application')
                ) {
                    // 保存缓存
                    $cache[] = $dir;
                } else {
                    // 删除菜单
                    $this->db->where('mark', 'app-'.$dir)->delete('admin_menu');
                    $this->db->where('mark', 'app-'.$dir)->delete('member_menu');
                }
            }
        }

		$this->dcache->set('app', $cache);

        return $cache;
	}
}