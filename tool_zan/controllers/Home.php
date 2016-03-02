<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.0.2
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class Home extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		if (!$this->admin) {
			$this->admin_msg('登录超时，请重新登录', 'index.php?c=login', 0);
		}
		$this->template->assign('menu', $this->get_menu(array(
		    '首页' => 'home/index',
		    '网站搬家' => 'home/move',
		    '网站升级' => 'home/update',
		    '管理员密码' => 'home/password',
		    '执行SQL语句' => 'home/sql',
		)));
    }
	
	/**
     * 执行SQL语句
     */
    public function sql() {
		$this->load->database();
		if (IS_POST) {
			$one = 0;
			$sql = str_replace('{dbprefix}', $this->db->dbprefix, $this->input->post('sql'));
			$sql_data = explode(';SQL_FINECMS_EOL', trim(str_replace(array(PHP_EOL, chr(13), chr(10)), 'SQL_FINECMS_EOL', $sql)));
			foreach($sql_data as $query){
				if (!$query) continue;
				$queries = explode('SQL_FINECMS_EOL', trim($query));
				$ret = '';
				foreach($queries as $query) {
					$ret.= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query; 
				}
				if (!$ret) continue;
				$this->db->query($ret);
				$one++;
			}
			if ($one == 1 && stripos($ret, 'select') === 0) {
				$this->template->assign(array(
					'sql' => $ret,
					'result' => $this->db->query($ret)->result_array(),
				));
				$this->template->display('sql_result.html');
			} else {
				$this->admin_msg('执行完成', 'index.php?c=home&m=sql&page='.$code, 1);
			}
		} else {
			$this->template->assign(array(
				'dbprefix' => $this->db->dbprefix,
			));
			$this->template->display('sql.html');
		}
	}

    /**
     * 网站搬家
     */
    public function move() {
		if (IS_POST) {
			$p = (int)$this->input->post('todo');
			if ($p) {
				$this->load->database();
				$this->load->model('site_model');
				$this->load->library('dconfig');
				$data = $this->input->post('site');
				$site = $this->db->get('site')->result_array();
				foreach ($site as $t) {
					$domain = $data[$t['id']];
					$setting = dr_string2array($t['setting']);
					$setting['SITE_DOMAIN'] = $domain;
					$this->db->where('id', $t['id'])->update('site', array(
						'domain' => $domain,
						'setting' => dr_array2string($setting),
					));
					$this->dconfig
						 ->file(FCPATH.'config/site/'.$t['id'].'.php')
						 ->note('站点配置文件')
						 ->space(32)
						 ->to_require_one($this->site_model->config, $setting);
					// 更新导航数据
					$this->db->query('update '.$this->db->dbprefix($t['id'].'_navigator').' set url=REPLACE(url, "http://'.$t['domain'].'", "http://'.$domain.'")');
				}
				$this->admin_msg('配置成功，请登录后台再更新全站缓存。<br>还需要把各个模块的内容地址更新一下！', '', 1);
			} else {
				$ok = 0;
				$site = array();
				require FCPATH.'config/database.php';
				if (!@mysql_connect($db['default']['hostname'].':'.$db['default']['port'], $db['default']['username'], $db['default']['password'])) {
					$ok = '无法连接到数据库服务器，请检查用户名和密码是否正确';
				}
				if (!@mysql_select_db($db['default']['database'])) {
					$ok = '指定的数据库('.$db['default']['database'].')不存在，系统尝试创建失败，请通过其他方式建立数据库';
				}
				if ($ok == 0) {
					$this->load->database();
					$data = $this->db->get('site')->result_array();
					foreach ($data as $t) {
						$site[$t['id']] = array(
							'id' => $t['id'],
							'name' => $t['name'],
							'domain' => $t['domain'],
							'setting' => dr_string2array($t['setting']),
						);
					}
				}
				$this->template->assign(array(
					'ok' => $ok,
					'site' => $site,
					'back' => $_SERVER['HTTP_REFERER']
				));
				$this->template->display('move_2.html');
			}
		} else {
			$this->template->display('move_1.html');
		}
    }
	
    /**
     * 首页
     */
    public function index() {
		if (IS_POST) {
			$code = str_replace('"', '', $this->input->post('code'));
			if ($code) {
				$string = '<?php'.PHP_EOL.PHP_EOL.'return "'.$code.'";'.PHP_EOL.PHP_EOL.'?>';
				file_put_contents(APPPATH.'core/Pwd.php', $string);
				$this->admin_msg('修改成功', 'index.php?c=home&m=index&page='.$code, 1);
			}
		}
		$this->template->display('index.html');
    }
	
    /**
     * 升级
     */
    public function update() {
		$this->template->assign(array(
			'db' => 'index.php?c=home&m=updatedb&page='.$this->code,
		));
		$this->template->display('update.html');
    }
	
	/**
     * 升级数据导入
     */
    public function updatedb() {
		
		// 运行SQL语句
    	if (is_file(FCPATH.'update.sql')) {
			$this->load->database();
    		$sql = file_get_contents(FCPATH.'update.sql');
			$sql = str_replace('{dbprefix}', $this->db->dbprefix, $sql);
			$sql_data = explode(';SQL_FINECMS_EOL', trim(str_replace(array(PHP_EOL, chr(13), chr(10)), 'SQL_FINECMS_EOL', $sql)));
			foreach($sql_data as $query){
				if (!$query) continue;
				$queries = explode('SQL_FINECMS_EOL', trim($query));
				$ret = '';
				foreach($queries as $query) {
					$ret .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query; 
				}
				if (!$ret) continue;
				$this->db->query($ret);
			}
			@unlink(FCPATH.'update.sql');
    	} else {
			$this->admin_msg('网站根目录中没有update.sql文件');
		}
		
		$this->admin_msg('升级数据导入完成', $_SERVER['HTTP_REFERER'], 1);
		
    }
	
	/**
     * 初始化管理员密码
     */
    public function password() {
		
		if (IS_POST) {
			$this->load->database();
			$name = $this->input->post('username');
			$pass = $this->input->post('password');
			if ($data = $this->db->where('username', $name)->get('member')->row_array()) {
				if ($data['adminid']) {
					$pwd = md5(md5($pass).$data['salt'].md5($pass));
					$this->db->where('username', $name)->update('member', array('password' => $pwd));
					$this->admin_msg('修改成功', 'index.php?c=home&m=password&page='.$this->code, 1);
				} else {
					$this->admin_msg('此账号不是管理员账号');
				}
			} else {
				$this->admin_msg('账号不存在');
			}
		}
		
		$this->template->display('password.html');
    }
}