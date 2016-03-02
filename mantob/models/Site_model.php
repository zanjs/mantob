<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.2
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Site_model extends CI_Model {

	public $config;

    public function __construct() {
        parent::__construct();
		$this->config = array(
			'SITE_NAME'					=> '网站的名称',
			'SITE_DOMAIN'				=> '网站的域名',
			'SITE_MOBILE'				=> '移动端域名',
			'SITE_LANGUAGE'				=> '网站的语言',
			'SITE_THEME'				=> '网站的主题风格',
			'SITE_TEMPLATE'				=> '网站的模板目录',
			'SITE_TIMEZONE'				=> '所在的时区常量',
			'SITE_TIME_FORMAT'			=> '时间显示格式，与date函数一致，默认Y-m-d H:i:s',
			'SITE_TITLE'				=> '网站首页SEO标题',
			'SITE_SEOJOIN'				=> '网站SEO间隔符号',
			'SITE_KEYWORDS'				=> '网站SEO关键字',
			'SITE_DESCRIPTION'			=> '网站SEO描述信息',
			'SITE_NAVIGATOR'			=> '网站导航信息，多个导航逗号分开',
			'SITE_HOME_INDEX'			=> '站点首页静态化有效期',
			'SITE_MODULE_INDEX'			=> '站点模块静态化有效期',
            'SITE_ATTACH_REMOTE'		=> '是否开启远程附件',
            'SITE_MOBILE_OPEN'		    => '是否自动识别移动端并强制定向到移动端域名',
            'SITE_QUERY_CACHE'		    => '页面查询的默认缓存时间',

		);
    }
	
	/**
	 * 创建站点
	 *
	 * @return	id
	 */
	public function add_site($data) {
	
		if (!$data) {
            return NULL;
        }
		
		$data['setting']['SITE_NAVIGATOR'] = '主导航,首页幻灯,底部导航';
		
		$this->db->insert('site', array(
			'name' => $data['name'],
			'domain' => $data['domain'],
			'setting' => dr_array2string($data['setting'])
		));
		
		$id = $this->db->insert_id();
        $this->db->query("DROP TABLE IF EXISTS `".$this->db->dbprefix($id.'_page')."`");
		$this->db->query(trim("
		CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix($id.'_page')."` (
		  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `module` varchar(20) NOT NULL COMMENT '模块dir',
		  `pid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '上级id',
		  `pids` varchar(255) NOT NULL COMMENT '所有上级id',
		  `name` varchar(255) NOT NULL COMMENT '单页名称',
		  `dirname` varchar(30) NOT NULL COMMENT '栏目目录',
		  `pdirname` varchar(100) NOT NULL COMMENT '上级目录',
		  `child` tinyint(1) unsigned NOT NULL COMMENT '是否有子类',
		  `childids` varchar(255) NOT NULL COMMENT '下级所有id',
		  `thumb` varchar(255) NOT NULL COMMENT '缩略图',
		  `title` varchar(255) NOT NULL COMMENT 'seo标题',
		  `keywords` varchar(255) NOT NULL COMMENT 'seo关键字',
		  `description` varchar(255) NOT NULL COMMENT 'seo描述',
		  `content` mediumtext DEFAULT NULL COMMENT '单页内容',
		  `attachment` text DEFAULT NULL COMMENT '附件信息',
		  `template` varchar(30) NOT NULL COMMENT '模板文件',
		  `urlrule` smallint(5) unsigned DEFAULT NULL COMMENT 'url规则id',
		  `urllink` varchar(255) NOT NULL COMMENT 'url外链',
		  `getchild` tinyint(1) unsigned NOT NULL COMMENT '将下级第一个菜单作为当前菜单',
		  `show` tinyint(1) unsigned NOT NULL COMMENT '是否显示在菜单',
		  `url` varchar(255) NOT NULL COMMENT 'url地址',
		  `setting` mediumtext NOT NULL COMMENT '自定义内容',
		  `displayorder` tinyint(2) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `mid` (`module`),
		  KEY `pid` (`pid`),
		  KEY `show` (`show`),
		  KEY `displayorder` (`displayorder`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='单页表';
		"));

        $this->db->query("DROP TABLE IF EXISTS `".$this->db->dbprefix($id.'_block')."`");
		$this->db->query(trim("
		CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix($id.'_block')."` (
		  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL COMMENT '文本块名称',
		  `content` text NOT NULL COMMENT '内容',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文本块表';
		"));

        $this->db->query("DROP TABLE IF EXISTS `".$this->db->dbprefix($id.'_form')."`");
		$this->db->query(trim("
		CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix($id.'_form')."` (
		  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(50) NOT NULL COMMENT '名称',
		  `table` varchar(50) NOT NULL COMMENT '表名',
		  `setting` text DEFAULT NULL COMMENT '配置信息',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `table` (`table`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='表单模型表';
		"));

        $this->db->query("DROP TABLE IF EXISTS `".$this->db->dbprefix($id.'_navigator')."`");
		$this->db->query(trim("
		CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix($id.'_navigator')."` (
		  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
		  `pid` smallint(5) unsigned NOT NULL COMMENT '上级id',
		  `pids` text DEFAULT NULL COMMENT '所有上级id数据项',
		  `type` tinyint(1) unsigned NOT NULL COMMENT '导航类型',
		  `name` varchar(255) NOT NULL COMMENT '导航名称',
		  `title` varchar(255) NOT NULL COMMENT 'seo标题',
		  `description` varchar(255) NOT NULL COMMENT '描述内容',
		  `url` varchar(255) NOT NULL COMMENT '导航地址',
		  `thumb` varchar(255) NOT NULL COMMENT '图片标示',
		  `show` tinyint(1) unsigned NOT NULL COMMENT '显示',
		  `mark` varchar(255) DEFAULT NULL COMMENT '类型标示',
		  `extend` tinyint(1) unsigned DEFAULT NULL COMMENT '是否继承下级',
		  `child` tinyint(1) unsigned NOT NULL COMMENT '是否有下级',
		  `childids` text DEFAULT NULL COMMENT '所有下级数据项',
		  `target` tinyint(1) unsigned NOT NULL COMMENT '是否站外链接',
		  `displayorder` tinyint(3) NOT NULL COMMENT '显示顺序',
		  PRIMARY KEY (`id`),
		  KEY `list` (`id`,`type`,`show`,`displayorder`),
		  KEY `mark` (`mark`),
		  KEY `extend` (`extend`),
		  KEY `pid` (`pid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='网站导航表';
		"));

        $this->db->query('INSERT INTO `'.$this->db->dbprefix('field').'` VALUES(NULL, \'相关附件\', \'attachment\', \'Files\', '.$id.', \'page\', 1, 1, 1, 1, 0, 0, \'a:2:{s:6:\\"option\\";a:5:{s:5:\\"width\\";s:3:\\"80%\\";s:4:\\"size\\";s:1:\\"2\\";s:5:\\"count\\";s:2:\\"10\\";s:3:\\"ext\\";s:31:\\"jpg,gif,png,ppt,doc,xls,rar,zip\\";s:10:\\"uploadpath\\";s:0:\\"\\";}s:8:\\"validate\\";a:9:{s:8:\\"required\\";s:1:\\"0\\";s:7:\\"pattern\\";s:0:\\"\\";s:9:\\"errortips\\";s:0:\\"\\";s:6:\\"isedit\\";s:1:\\"0\\";s:3:\\"xss\\";s:1:\\"0\\";s:5:\\"check\\";s:0:\\"\\";s:6:\\"filter\\";s:0:\\"\\";s:4:\\"tips\\";s:0:\\"\\";s:8:\\"formattr\\";s:0:\\"\\";}}\', 0)');
        $this->db->query('INSERT INTO `'.$this->db->dbprefix('field').'` VALUES(NULL, \'单页内容\', \'content\', \'Ueditor\', '.$id.', \'page\', 1, 1, 1, 1, 0, 0, \'a:2:{s:6:\\"option\\";a:7:{s:5:\\"width\\";s:3:\\"90%\\";s:6:\\"height\\";s:3:\\"400\\";s:4:\\"mode\\";s:1:\\"1\\";s:4:\\"tool\\";s:0:\\"\\";s:5:\\"mode2\\";s:1:\\"1\\";s:5:\\"tool2\\";s:0:\\"\\";s:5:\\"value\\";s:0:\\"\\";}s:8:\\"validate\\";a:9:{s:8:\\"required\\";s:1:\\"1\\";s:7:\\"pattern\\";s:0:\\"\\";s:9:\\"errortips\\";s:0:\\"\\";s:6:\\"isedit\\";s:1:\\"0\\";s:3:\\"xss\\";s:1:\\"1\\";s:5:\\"check\\";s:0:\\"\\";s:6:\\"filter\\";s:0:\\"\\";s:4:\\"tips\\";s:0:\\"\\";s:8:\\"formattr\\";s:0:\\"\\";}}\', 0)');

        return $id;
	}
	
	/**
	 * 修改站点
	 *
	 * @return	void
	 */
	public function edit_site($id, $data) {
	
		if (!$data || !$id) {
            return NULL;
        }
		
		$this->db->where('id', $id)->update('site', array(
			'name' => $data['name'],
			'domain' => $data['domain'],
			'setting' => dr_array2string($data['setting'])
		));
	}
	
	/**
	 * 站点
	 *
	 * @return	array|NULL
	 */
	public function get_site_data() {
	
		$_data = $this->db
					  ->order_by('id ASC')
					  ->get('site')
					  ->result_array();
		if (!$_data) {
            return NULL;
        }
		
		$data = array();
		foreach ($_data as $t) {
			$t['setting'] = dr_string2array($t['setting']);
			$t['setting']['SITE_NAME'] = $t['name'];
			$t['setting']['SITE_DOMAIN'] = $t['domain'];
			$data[$t['id']]	= $t;
		}
		
		return $data;
	}

	/**
	 * 站点信息
	 *
	 * @return	array|NULL
	 */
	public function get_site_info($id) {

		$data = $this->db
					 ->where('id', $id)
					 ->get('site')
					 ->row_array();
		if (!$data) {
            return NULL;
        }

        $data['setting'] = dr_string2array($data['setting']);
        $data['setting']['SITE_NAME'] = $data['name'];
        $data['setting']['SITE_DOMAIN'] = $data['domain'];

		return $data['setting'];
	}

    // 站点缓存
    public function cache() {

        $data = $this->get_site_data();
        $oldfile = directory_map(FCPATH.'config/site/');
        foreach ($oldfile as $file) {
            @unlink(FCPATH.'config/site/'.$file);
        }

        $cache = $domain = array();
        $this->load->library('dconfig');
        $this->ci->dcache->delete('siteinfo');

        // 站点域名归类和写入配置文件
        foreach ($data as $id => $t) {
            // 站点域名归类
            if ($t['domain']) {
                $domain[$t['domain']] = $id;
            }
            // 移动端域名归类
            if ($t['setting']['SITE_MOBILE']) {
                $domain[$t['setting']['SITE_MOBILE']] = $id;
            }
            // 写入配置文件
            $this->dconfig
                 ->file(FCPATH.'config/site/'.$id.'.php')
                 ->note('站点配置文件')
                 ->space(32)
                 ->to_require_one($this->config, $t['setting']);
            // 写入缓存文件
            $cache[$id] = $t['setting'];
        }
        $this->ci->dcache->set('siteinfo', $cache);

        // 查询所有可用模块
        $data = $this->db
                     ->where('disabled', 0)
                     ->select('site,dirname')
                     ->order_by('displayorder ASC')
                     ->get('module')
                     ->result_array();
        if ($data) {
            $module = array();
            foreach ($data as $t) {
                // 排除不存在的模块
                if (!is_dir(FCPATH.$t['dirname'])) {
                    continue;
                }
                // 排除自定义数据的模块
                $cfg = require FCPATH.$t['dirname'].'/config/module.php';
                if (isset($cfg['mydb']) && $cfg['mydb']) {
                    continue;
                }
                // 模块域名归类
                $site = dr_string2array($t['site']);
                foreach ($site as $sid => $s) {
                    if ($s['use']) {
                        if ($s['domain']) {
                            $domain[$s['domain']] = $sid; // 更新模块域名
                        }
                        $module[$sid][] = $t['dirname']; // 将模块归类至站点
                    }
                }
            }
            $this->ci->dcache->set('module', $module);
        } else {
            $this->ci->dcache->delete('module');
        }

        // 会员域名归类
        $data = $this->db
                     ->where('name', 'domain')
                     ->limit(1)
                     ->get('member_setting')
                     ->row_array();
        if ($data) {
            $data = dr_string2array($data['value']);
            foreach ($data as $sid => $url) {
                $domain[$url] = $sid;
            }
        }

        // 生成站点域名归属
        $this->dconfig
             ->file(FCPATH.'config/domain.php')
             ->note('站点域名文件')
             ->space(32)
             ->to_require_one($domain);

    }
}