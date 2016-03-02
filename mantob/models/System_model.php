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
	
class System_model extends CI_Model {

	public $config;

	/*
	 * 系统模型类
	 */
    public function __construct() {
        parent::__construct();
		$this->config = array(
			'SYS_LOG' => '后台操作日志开关',
			'SYS_KEY' => '安全密钥',
			'SYS_DEBUG'	=> '调试器开关',
			'SYS_HELP_URL' => '系统帮助url前缀部分',
			'SYS_EMAIL' => '系统收件邮箱，用于接收系统信息',
			'SYS_MEMCACHE' => 'Memcache缓存开关',
			'SYS_ATTACHMENT_DIR' => '系统附件目录名称',
			'SYS_CRON_QUEUE' => '任务队列方式',
			'SYS_CRON_NUMS' => '每次执行任务数量',
			'SYS_CRON_TIME' => '每次执行任务间隔',
			
			'SITE_EXPERIENCE' => '经验值名称',
			'SITE_SCORE' => '虚拟币名称',
			'SITE_MONEY' => '金钱名称',
			'SITE_CONVERT' => '虚拟币兑换金钱的比例',
			'SITE_ADMIN_CODE' => '后台登录验证码开关',
			'SITE_ADMIN_PAGESIZE' => '后台数据分页显示数量',
			
		);
    }
	
	/*
	 * 保存配置文件
	 *
	 * @param	array	$system	旧数据
	 * @param	array	$config	新数据
	 * @return	void
	 */
	public function save_config($system, $config) {
		
		$data = array();
		$this->load->library('dconfig');
		
		foreach ($this->config as $i => $note) {
			$value = isset($config[$i]) ? $config[$i] : $system[$i];
			if ($value == 'TRUE') {
                $value = 1;
            }
			if ($value == 'FALSE') {
                $value = 0;
            }
			if ($i == 'SYS_HELP_URL') {
                $value = $system['SYS_HELP_URL'];
            }
			if ($i == 'SYS_KEY' && strpos($value, '***') !== FALSE) {
                $value = $system['SYS_KEY'];
            }
			$data[$i] = $value;
		}
		
		$this->dconfig
			 ->file(FCPATH.'config/system.php')
			 ->note('系统配置文件')
			 ->space(32)
			 ->to_require_one($this->config, $data);
			 
		return $data;
	}
	
	/*
	 * 缓存表
	 *
	 * @return	array
	 */
	public function cache() {
	
		$table = array();
		
		// 主数据库表查询
		$_table = $this->db->query("SHOW TABLE STATUS FROM `{$this->db->database}`")->result_array();
		foreach ($_table as $t) {
			if (strpos($t['Name'], $this->db->dbprefix) === 0) {
				$_field = $this->db->query('SHOW FULL COLUMNS FROM '.$t['Name'])->result_array();
				foreach ($_field as $c) {
					$t['field'][$c['Field']] = array(
						'name' => $c['Field'],
						'type' => $c['Type'],
						'note' => $c['Comment']
					);
				}
				$table[$t['Name']]	= array(
					'name' => $t['Name'],
					'rows' => $t['Rows'],
					'note' => $t['Comment'],
					'free' => $t['Data_free'], // 多余空间
					'field' => $t['field'],
					'siteid' => 0, // 主数据库
					'update' => $t['Update_time'],
					'filesize' => $t['Data_length'] + $t['Index_length'],
					'collation'	=> $t['Collation'],
				);
			}
		}
		
		// 按站点查询
		if ($this->SITE) {
			foreach ($this->SITE as $sid => $x) {
				$db = $this->site[$sid];
				if ($db !== $this->db && $_table = $db->query("SHOW TABLE STATUS FROM {$db->database}")->result_array()) {
					foreach ($_table as $t) {
						if (strpos($t['Name'], $this->db->dbprefix) === 0) {
							$_field = $this->db->query('SHOW FULL COLUMNS FROM '.$t['Name'])->result_array();
							foreach ($_field as $c) {
								$t['field'][$c['Field']] = array(
									'name' => $c['Field'],
									'type' => $c['Type'],
									'note' => $c['Comment']
								);
							}
							$table[$t['Name']]	= array(
								'name' => $t['Name'],
								'rows' => $t['Rows'],
								'note' => $t['Comment'],
								'free' => $t['Data_free'], // 多余空间
								'field' => $t['field'],
								'siteid' => $sid, // 分站点数据库
								'update' => $t['Update_time'],
								'filesize' => $t['Data_length'] + $t['Index_length'],
								'collation'	=> $t['Collation'],
							);
						}
					}
				}
			}
		}
		
		$this->dcache->set('table', $table);
		
		return $table;
	}
	
	/*
	 * 系统表
	 * 
	 * @return	array
	 */
	public function get_system_table() {
	
		$list = array();
		$data = $this->dcache->get('table');
		if (!$data) {
            $data = $this->cache();
        }
		
		foreach ($data as $t) {
			if (!preg_match('/'.$this->db->dbprefix.'[0-9]+_/', $t['name'])) {
                $list[] = $t;
            }
		}
		
		return $list;
	}
	
	/*
	 * 站点表
	 * 
	 * @param	intval	$siteid
	 * @return	array
	 */
	public function get_site_table($siteid) {
	
		$list = array();
		$data = $this->dcache->get('table');
		if (!$data) {
            $data = $this->cache();
        }
		
		foreach ($data as $t) {
			if (preg_match('/'.$this->db->dbprefix.$siteid.'_/', $t['name'])) {
                $list[] = $t;
            }
		}
		
		return $list;
	}


    // 更新URL缓存
    public function urlrule() {

        $this->ci->dcache->delete('urlrule');
        $data = $this->db->get('urlrule')->result_array();
        $cache = array();
        if ($data) {
            foreach ($data as $t) {
                $t['value'] = dr_string2array($t['value']);
                $cache[$t['id']] = $t;
            }
            $this->ci->dcache->set('urlrule', $cache);
        }

        $this->ci->clear_cache('urlrule');
        return $cache;
    }

    // 更新邮件缓存
    public function email() {

        $this->dcache->delete('email');

        $data = $this->db->get('mail_smtp')->result_array();
        if ($data) {
            $this->dcache->set('email', $data);
        }

        $this->ci->clear_cache('email');
        return $data;
    }

    // 更新审核流程缓存
    public function verify() {

        $data = array();
        $_data = $this->db
                      ->order_by('id ASC')
                      ->get('admin_verify')
                      ->result_array();
        if ($_data) {
            foreach ($_data as $t) {
                $t['num'] = count($t['verify']);
                $t['verify'] = dr_string2array($t['verify']);
                $data[$t['id']] = $t;
            }
            $this->ci->dcache->set('verify', $data);
        } else {
            $this->ci->dcache->delete('verify');
        }

        $this->ci->clear_cache('verify');

        return $data;
    }

    // 更新下载镜像缓存
    public function downservers() {

        $data = $this->db
                     ->order_by('displayorder asc')
                     ->get('downservers')
                     ->result_array();
        $this->ci->dcache->delete('downservers');
        $cache = array();
        if ($data) {
            foreach ($data as $t) {
                $cache[$t['id']] = $t;
            }
            $this->ci->dcache->set('downservers', $cache);
        }

        $this->ci->clear_cache('downservers');
        return $cache;
    }

    // 文字块缓存
    public function block($site) {

        $this->ci->clear_cache('block-'.$site);
        $this->ci->dcache->delete('block-'.$site);

        $data = $this->site[$site]->get($site.'_block')->result_array();
        $cache = array();
        if ($data) {
            foreach ($data as $t) {
                $cache[$t['id']] = array(
                    1 => $t['name'],
                    0 => stripos($t['content'], '</script>') ? $t['content'] : nl2br($t['content']),
                );
            }
            $this->ci->dcache->set('block-'.$site, $cache);
        }

        return $cache;
    }
}