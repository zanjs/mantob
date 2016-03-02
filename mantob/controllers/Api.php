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
require FCPATH.'mantob/helpers/system_helper.php';
class Api extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 自定义信息JS调用
     */
    public function template() {
        $this->api_template();
    }

    /**
	 * 更新浏览数
	 */
	public function hits() {
	
	    $id = (int)$this->input->get('id');
	    $dir = $this->input->get('module', TRUE);
		$name = 'hits'.$dir.SITE_ID.$id;
		$hits = (int)$this->get_cache_data($name);
        if (!is_file(FCPATH.'cache/data/module-'.SITE_ID.'-'.$dir.'.cache.php')) {
            exit;
        }
		
		if (!$hits) {
			$data = $this->site[SITE_ID]
						 ->where('id', $id)
						 ->select('hits')
						 ->limit(1)
						 ->get($this->db->dbprefix(SITE_ID.'_'.$dir))
						 ->row_array();
			$hits = (int)$data['hits'];
		}
		
		$hits++;
		$this->set_cache_data($name, $hits, (int)$this->get_cache('module-'.SITE_ID.'-'.$dir, 'setting', 'show_cache'));
		
		$this->site[SITE_ID]
			 ->where('id', $id)
			 ->update($this->db->dbprefix(SITE_ID.'_'.$dir), array('hits' => $hits));

		exit("document.write('$hits');");
	}
	
	/**
	 * 发送桌面快捷方式
	 */
	public function desktop() {
		
		$site = (int)$this->input->get('site');
		$module = $this->input->get('module');
		
		if ($site && !$module) {
			$url = $this->SITE[$site]['SITE_URL'];
			$name = $this->SITE[$site]['SITE_NAME'].'.url';
		} elseif ($site && $module) {
			$mod = $this->get_cache('module-'.$site.'-'.$module);
			$url = $mod['url'];
			$name = $mod['name'].'.url';
		}  else {
			$url = $this->SITE[SITE_ID]['SITE_URL'];
			$name = $this->SITE[SITE_ID]['SITE_NAME'].'.url';
		}
		
		$data = "
		[InternetShortcut]
		URL={$url}
		IconFile={$url}favicon.ico
		Prop3=19,2
		IconIndex=1
		";
		$mime = 'application/octet-stream';
		
		header('Content-Type: "' . $mime . '"');
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header("Content-Transfer-Encoding: binary");
		header('Expires: 0');
		header('Pragma: no-cache');
		header("Content-Length: " . strlen($data));
		echo $data;
	}
	
	/**
	 * 伪静态测试
	 */
	public function test() {
		header('Content-Type: text/html; charset=utf-8');
		echo '服务器支持伪静态';
	}
	
	/**
	 * 自定义数据调用（老版本）
	 */
	public function data() {
	
		// 安全认证码
		$auth = $this->input->get('auth');
		if ($auth != SYS_KEY) {
			// 安全认证码不正确
			$data = array('error' => '安全认证码不正确');
		} else {
			// 解析数据
			$data = $this->template->list_tag($this->input->get('param'));
		}
		
		// 接收参数
		$format = $this->input->get('format');
		$callback = $this->input->get('callback');
		
		// 页面输出
		if ($format == 'xml') {
			header('Content-Type: text/xml');
			echo dr_array2xml($data, FALSE);
		} else {
			echo json_encode($data);
		}
	}
	
	/**
	 * 自定义数据调用（新版本）
	 */
	public function data2() {
	
		// 安全认证码
		$auth = $this->input->get('auth');
		if ($auth != md5(SYS_KEY)) {
			// 安全认证码不正确
			$data = array('error' => '安全认证码不正确');
		} else {
			// 解析数据
			$data = $this->template->list_tag($this->input->get('param'));
		}
		$title=$this->input->get('title');
		$description=$this->input->get('description');
		// 接收参数
		$format = $this->input->get('format');
		$data=arrayToObject($data);
		$data=object_array($data->return);
		foreach($data as $k=>$d){
			$s[]=$d;
			if($title){
				$s[$k][title]=mb_substr($data[$k][title],0,$title);
			}
			if($description){
					$s[$k][description]=mb_substr($data[$k][description],0,$description);
			}
			$s[$k][newthumb]=dr_file_info($data[$k][thumb]);
			$s[$k][newthumb]=$s[$k][newthumb][attachment];
		}
		// 页面输出
		if ($format == 'xml') {
			header('Content-Type: text/xml');
			echo dr_array2xml($data, FALSE);
		} elseif ($format == 'jsonp') {
			echo $this->input->get('callback').'('.json_encode($s).')';
		} else {
			echo json_encode($data);
		}
	}
}


