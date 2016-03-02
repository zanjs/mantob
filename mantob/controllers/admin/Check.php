<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.0
 * @author		Chunjie <chunjie@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Check extends M_Controller {

	private $step;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		// 检测步骤
		$this->step = $this->_get_step();
    }

	/**
     * 系统体检
     */
    public function index() {
		$this->template->assign(array(
			'step' => $this->step,
		));
		$this->template->display('check_index.html');
	}
	
	/**
     * PHP环境
     */
    public function phpinfo() {
		phpinfo();
		$this->output->enable_profiler(TRUE);
	}
	
	/**
     * 执行检测
     */
    public function todo() {
		$step = max(1, (int)$this->input->get('step'));
		if (isset($this->step[$step]) && method_exists($this, $this->step[$step])) {
			echo @call_user_func_array(array($this, $this->step[$step]), array());
		}
		
	}
	
	/**
     * 版本检测
     */
    private function _version() {
		$id = (int)dr_catcher_data('http://www.mantob.com/index.php?c=sys&m=now');
		if ($id && MAN_VERSION_ID < $id) {
			return $this->halt("您的当前版本过低，为了您网站的安全性，请立即升级到官方最新版本，<a style='color:red' href='".dr_url('upgrade/index')."'><b>这里升级</b></a>", 0);
		}
	}
	
	/**
     * 上传参数检测
     */
    private function _upload() {

        $post = intval(@ini_get("post_max_size"));
        $file = intval(@ini_get("upload_max_filesize"));

        $str = '';
        if ($file >= $post) {
            $str.= $this->halt('系统配置不合理，post_max_size值必须大于upload_max_filesize值，否则会出现“进度条100%卡住”或者提示“游客不允许上传”，解决方案：<a style="color:red" target="_blank" href="http://www.mantob.net/forum.php?mod=viewthread&tid=2490">这里</a>', 0);
        }
        if ($file < 10) {
            $str.= $this->halt('系统环境只允许上传'.$file.'MB文件，可以设置upload_max_filesize值提升上传大小，解决方案：<a style="color:red" target="_blank" href="http://www.mantob.net/forum.php?mod=viewthread&tid=2490">这里</a>', 1);
        }
        if ($post < 10) {
            $str.= $this->halt('系统环境要求每次发布内容不能超过'.$post.'MB（含文件），可以设置post_max_size值提升发布大小，解决方案：<a style="color:red" target="_blank" href="http://www.mantob.net/forum.php?mod=viewthread&tid=2490">这里</a>', 1);
        }

        return $str;
	}

	/**
     * ini_get
     */
    private function _ini_get() {
		if (!function_exists('ini_get')) {
			return $this->halt('系统函数ini_get被禁用了，将无法获取到系统环境参数，解决方案：在php.ini中找到disable_functions并去掉ini_get', 0);
		}
	}

	/**
     * 模板名称检测
     */
    private function _template() {
		if (SITE_TEMPLATE == 'default') {
			return $this->halt('网站模板【default】未更换，建议正式站点不要采用系统默认模板【default】，默认模板仅用于学习', 0);
		}
	}


	/**
     * 解压函数检测
     */
    private function _unzip() {
		if (!function_exists('gzopen')) {
			return $this->halt('未开启zlib扩展，您将无法进行在线升级、无法下载模块/应用、无法进行模块/应用升级更新、无法上传头像、无法上传头像，解决方案：Google/百度一下“PHP开启zlib扩展”', 0);
		}
	}

	/**
     * 解压函数检测
     */
    private function _gzinflate() {
		if (!function_exists('gzinflate')) {
			return $this->halt('函数gzinflate被禁用了，您将无法进行在线升级、无法下载模块/应用、无法进行模块/应用升级更新、无法上传头像，解决方案：在php.ini中找到disable_functions并去掉gzinflate', 0);
		}
	}
	
	/**
     * 后台入口名称检测
     */
    private function _admin_file() {
		if (SELF == 'admin.php') {
			return $this->halt('如果管理帐号泄漏，后台容易遭受攻击，为了系统安全，请修改根目录admin.php的文件名', 0);
		}
	}
	
	/**
     * 目录是否可写
     */
    private function _dir_write() {
	
		$dir = array(
			FCPATH.'cache/' => '无法生成系统缓存文件',
			FCPATH.'config/' => '无法生成系统配置文件',
			FCPATH.'member/uploadfile/' => '无法上传附件',
			FCPATH.'member/uploadfile/' => '无法上传附件',
		);
		
		$str = '';
		foreach ($dir as $file => $note) {
			if (!$this->_check_write_able($file)) {
				$str.= $this->halt(str_replace(FCPATH, '/', $file).'无写入权限，'.$note, 0);
			}
		}
		
		return $str;
	}
	
	/**
     * 栏目数量检查
     */
    private function _category() {
		$module = $this->get_module(SITE_ID);
		if ($module) {
			$string = '';
			foreach ($module as $t) {
				if (count($t['category']) > 50) {
					$string.= $this->halt("当前站点模块【{$t[name]}】的栏目超过了50个，内存消耗会比较多，栏目数量建议控制在50个以内", 0);
				}
			}
		}
		return $string;
	}

    /**
     * 域名绑定检测
     */
    private function _domain() {

        $ip = $this->_get_server_ip();
        $string = '';
        $domain = array();
        $member = $this->get_cache('member');

        // 检测域名重复性和可用性
        foreach ($this->SITE as $sid => $site) {
            if (in_array($site['SITE_DOMAIN'], $domain)) {
                $string.= $this->halt("站点【{$site['SITE_NAME']}】的域名【{$site['SITE_DOMAIN']}】已经被其他地方绑定了，请更换", 0);
            }
            if (gethostbyname($site['SITE_DOMAIN']) != $ip) {
                $string.= $this->halt("请将站点【{$site['SITE_NAME']}】的域名【{$site['SITE_DOMAIN']}】解析到【{$ip}】", 0);
            }
            $module = $this->get_module($sid);
            if ($module) {
                foreach ($module as $m) {
                    foreach ($m['site'] as $t) {
                        if (!$t['domain']) {
                            continue;
                        }
                        if (in_array($t['domain'], $domain)) {
                            $string.= $this->halt("模块【{$m['name']}】的域名【{$t['domain']}】已经被其他地方绑定了，请更换", 0);
                        }
                        if (gethostbyname($t['domain']) != $ip) {
                            $string.= $this->halt("请将模块【{$m['name']}】的域名【{$t['domain']}】解析到【{$ip}】", 0);
                        }
                    }
                }
                unset($module);
            }

            if ($ym = $member['setting']['domain'][$site]) {
                if (in_array($ym, $domain)) {
                    $string.= $this->halt("会员中心域名【{$ym}】已经被其他地方绑定了，请更换", 0);
                }
                if (gethostbyname($ym) != $ip) {
                    $string.= $this->halt("请将会员中心域名【{$ym}】解析到【{$ip}】", 0);
                }
            }

        }
        if ($member['setting']['space']['domain']) {
            $ym = $member['setting']['space']['domain'];
            if (in_array($ym, $domain)) {
                $string.= $this->halt("空间聚合页面域名【{$ym}】已经被其他地方绑定了，请更换", 0);
            }
            if (gethostbyname($ym) != $ip) {
                $string.= $this->halt("请将空间聚合页面域名【{$ym}】解析到【{$ip}】", 0);
            }
        }



        return $string;
    }
	
	/**
     * 风格与模板是否重名
     */
    private function _template_theme() {
		if (SITE_TEMPLATE == SITE_THEME) {
			return $this->halt('模板和风格目录同名可能导致模板被下载，建议模板和风格使用不相同的目录名称', 0);
		}
	}
	
	/**
     * Cookie安全码验证
     */
    private function _cookie_code() {
		if (SYS_KEY == 'mantob') {
			return $this->halt("请重新生成安全密钥，否则网站数据有被盗的风险，解决方案：系统-配置-生成密钥", 0);
		}
	}
	
	/**
     * allow_url_fopen
     */
    private function _url_fopen() {
		if (!ini_get('allow_url_fopen')) {
			return $this->halt('远程图片无法保存、网络图片无法上传、一键登录无法登录、无法访问云商店、无法使用微信。解决方案：在php.ini文件中allow_url_fopen设置为On', 0);
		}
	}
	
	/**
     * curl_init
     */
    private function _curl_init() {
		if (!function_exists('curl_init')) {
			return $this->halt('PHP不支持CURL扩展，一键登录可能无法登录、无法访问云商店、无法使用微信。解决方案：将php.ini中的;extension=php_curl.dll中的分号去掉', 0);
		}
	}

	/**
     * openssl_open
     */
    private function _openssl_open() {
		if (!function_exists('openssl_open')) {
			return $this->halt('PHP不支持openssl，一键登录可能无法登录、无法访问云商店、无法使用微信。解决方案：将php.ini中的;extension=php_openssl.dll中的分号去掉', 0);
		}
	}
	
	/**
     * fsockopen
     */
    private function _fsockopen() {
		if (!function_exists('fsockopen')) {
			return $this->halt('PHP不支持fsockopen，可能充值接口无法使用、手机短信无法发送、电子邮件无法发送、一键登录无法登录、无法访问云商店、无法使用微信', 0);
		}
	}
	
	/**
     * php
     */
    private function _php() {
	
		if (version_compare(PHP_VERSION, '5.2.8', '<')) {
			return $this->halt('您的当前PHP版本是'.PHP_VERSION.'，会导致某些功能无法正常使用，建议PHP版本在5.3.0以上，最低支持5.2.8', 0);
		}
		
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			return $this->halt('您的当前PHP版本是'.PHP_VERSION.'，建议PHP版本在5.3.0以上，性能会大大提高', 1);
		}
	}
	
	/**
     * mysql
     */
    private function _mysql() {
		if ($this->db->dbdriver == 'mysql') {
			return $this->halt("建议将数据库驱动设置为 mysqli 或 pdo ，设置方式：config/database.php中的dbdriver选项", 1);
		}
	}
	
	/**
     * email
     */
    private function _email() {
		if (!$this->db->count_all_results($this->db->dbprefix('mail_smtp'))) {
			return $this->halt("邮件服务器尚未设置，可能系统无法发送邮件通知，设置方式：系统->系统功能->邮件系统->添加SMTP服务器", 0);
		}
	}
	
	/**
     * memcache
     */
    private function _memcache() {
	
		if (!extension_loaded('memcached') && !extension_loaded('memcache')) {
			return $this->halt("服务器不支持memcache，安装memcache可以大大提高缓存数据的读取速度", 1);
		}
		
		if (!$this->cache->memcached->is_supported()) {
			return $this->halt("无法连接Memcache服务器，配置方式：系统->系统功能->系统配置->Memcache缓存", 0);
		}
		
		$this->cache->memcached->save('memcache_test', 'ok', 10);
		if ($this->cache->memcached->get('memcache_test') != 'ok') {
			return $this->halt("memcache尚未生效，请检查服务器地址与端口号是否配置正确", 0);
		} else {
			return $this->halt("Memcache缓存已经生效，可以大大提高缓存数据的读取速度", 1);
		}
	}
	
	/**
     * mcryp
     */
    private function _mcryp() {
		if (!function_exists('mcrypt_encrypt')) {
			return $this->halt('PHP未开启Mcrypt扩展，邮件验证无法使用、密码找回不能使用，文件上传安全系数降低', 0);
		}
	}
	
	/**
     * 表结构检测
     */
    private function _tableinfo() {
	
		$sql = "SHOW TABLE STATUS FROM `{$this->db->database}`";
		$table = $this->db->query($sql)->result_array();
		if (!$table) {
            return $this->halt("无法通过( $sql )获取到数据表结构，系统模块无法使用，解决方案：为Mysql账号开启SHOW TABLE STATUS权限", 0);
        }
		
		$sql = 'SHOW FULL COLUMNS FROM `'.$this->db->dbprefix('admin').'`';
		$field = $this->db->query($sql)->result_array();
		if (!$field) {
            return $this->halt("无法通过( $sql )获取到数据表字段结构，系统模块无法使用，解决方案：为Mysql账号开启SHOW FULL COLUMNS权限", 0);
        }
	}
	
	/**
     * 检测结果
     */
    private function _result() {
		return $this->halt('系统检查完成', 1);
	}
	
	/**
     * 消息提示
     */
	private function halt($msg, $status = 1) {
	
		return $status ? "<tr><td align=\"left\"><font color=green><img width=\"16\" src=\"".SITE_URL."mantob/statics/images/ok.png\">&nbsp;&nbsp;".$msg."</font></td></tr>" : "<tr><td align=\"left\"><font color=red><img width=\"16\" src=\"".SITE_URL."mantob/statics/images/b_drop.png\">&nbsp;&nbsp;".$msg."</font></td></tr>";
	}
}