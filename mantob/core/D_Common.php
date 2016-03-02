<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**  表单提交修改 0903
 * Omweb Website Management System
 *
 * @since	    version 2.3.3
 * @author	    mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
class D_Common extends CI_Controller {

    public $post; // 校验器与过滤器调用
    public $data; // 校验器与过滤器返回的值

    public $uid; // 当前登录会员id
    public $site; // 站点数据库对象
    public $SITE; // 站点信息数组
    public $admin; // 管理员信息
    public $member; // 当前登录会员信息
    public $pagesize; // 会员中心分页数量
    public $markrule; // 会员权限规则标识
    public $member_rule; // 会员权限规则
    public $module_rule; // 会员模块权限规则

    public $mobile; // 是否手机端

    /**
     * 构造函数
     */

    public function __construct() {
        parent::__construct();
        // 检测到未安装
        if (!is_file(FCPATH . 'cache/install.lock')) {
            redirect('http://' . strtolower($_SERVER['HTTP_HOST']) . '/index.php?c=install&m=index', 'refresh');
        }
        $this->lang->load('my');
        $this->load->database();
        $this->load->library('user_agent');
        $this->load->helper('vip');
        $this->_init_variable();
        $this->template->ci = $this;
        $this->template->mobile = $this->mobile;
        $this->template->assign(array(
            'get' => $this->input->get(NULL, TRUE),
            'SITE' => $this->SITE,
            'member' => $this->member,
            'dirname' => APP_DIR,
            'markrule' => $this->markrule,
            'is_mobile' => $this->template->mobile,
            'member_rule' => $this->member_rule,
            'module_rule' => $this->module_rule
        ));
    }

    /**
     * 清除系统缓存
     *
     * @param	string	$name	缓存名称
     * @return  void
     */
    public function clear_cache($name) {

        $name = strtolower($name);

        // 模块缓存时，清除所有相关文件
        if ($name == 'module') {
            $data = $this->get_cache('module');
            if ($data) {
                foreach ($data as $site => $t) {
                    if ($t) {
                        foreach ($t as $m) {
                            $tname = 'module-'.$site.'-'.$m;
                            $this->dcache->delete($tname);
                            if (SYS_MEMCACHE && $this->cache->memcached->is_supported()) {
                                $this->cache->memcached->delete(SYS_KEY.$tname);
                            }
                        }
                    }
                }
            }
        }

        // 删除memcached缓存
        if (SYS_MEMCACHE && $this->cache->memcached->is_supported()) {
            $this->cache->memcached->delete(SYS_KEY.$name);
        }

        // 删除文件缓存
        $this->dcache->delete($name);
        $this->cache->file->delete($name);
    }

    /**
     * 系统缓存读取
     *
     * @param	string	$name	缓存名称
     * @param	string	$var1	缓存数组的参数变量1
     * @param	string	$var2	缓存数组的参数变量2
     * @param	string	$varN	缓存数组的参数变量N
     * @return  array
     */
    public function get_cache() {

        $param = func_get_args();
        if (!$param) {
            return NULL;
        }

        // 取第一个参数作为缓存变量名称
        $data = NULL;
        $name = strtolower(array_shift($param));

        // memcached缓存
        if (SYS_MEMCACHE && $this->cache->memcached->is_supported()) {
            $data = $this->cache->memcached->get(SYS_KEY.$name);
            if (!$data) {
                $data = $this->dcache->get(SYS_KEY.$name);
                $this->cache->memcached->save(SYS_KEY.$name, $data, 7200);
            }
        }

        // 不存在memcahed缓存时
        if (!$data) {
            $var = 'cache-'.$name;
            if (isset($this->$var) && $this->$var) {
                // 读取全局变量
                $data = $this->$var;
            } else {
                // 读取本地文件缓存数据
                $data = $this->$var = $this->dcache->get($name);
                // 当缓存不存在时，尝试生成缓存！
                if (!$data) {
                    if (strpos($name, 'navigator') === 0) {
                        // 网站导航缓存
                        $this->load->model('navigator_model');
                        $data = $this->navigator_model->cache(SITE_ID);
                    } elseif (strpos($name, 'module-') === 0) {
                        // 模块数据缓存
                        list($a, $siteid, $dirname) = explode('-', $name);
                        $this->load->model('module_model');
                        $this->module_model->cache($dirname);
                        $data = $this->dcache->get('module-'.$siteid.'-'.$dirname);
                    } elseif (strpos($name, 'module') === 0) {
                        // 模块缓存
                        $this->load->model('site_model');
                        $this->site_model->cache();
                        $data = $this->dcache->get('module');
                    } elseif (strpos($name, 'urlrule') === 0) {
                        // URL 规则
                        $this->load->model('system_model');
                        $data = $this->system_model->urlrule();
                    } elseif (strpos($name, 'space-model') === 0) {
                        // 会员空间模型缓存
                        $this->load->model('space_model_model');
                        $data = $this->space_model_model->cache();
                    } elseif (strpos($name, 'table') === 0) {
                        // 表结构缓存
                        $this->load->model('system_model');
                        $data = $this->system_model->cache();
                    } elseif (strpos($name, 'email') === 0) {
                        // 邮件缓存
                        $this->load->model('system_model');
                        $data = $this->system_model->email();
                    } elseif (strpos($name, 'verify') === 0) {
                        // 审核流程缓存
                        $this->load->model('system_model');
                        $data = $this->system_model->verify();
                    } elseif (strpos($name, 'downservers') === 0) {
                        // 下载镜像缓存
                        $this->load->model('system_model');
                        $data = $this->system_model->downservers();
                    } elseif (strpos($name, 'member-menu') === 0) {
                        // 会员菜单缓存
                        $this->load->model('member_menu_model');
                        $data = $this->member_menu_model->cache();
                    } elseif (strpos($name, 'member') === 0) {
                        // 会员缓存
                        $data = $this->member_model->cache();
                    } elseif (strpos($name, 'form-') === 0) {
                        // 表单缓存
                        list($a, $site) = explode('-', $name);
                        $this->load->model('form_model');
                        $this->form_model->link = $this->site[$site];
                        $this->form_model->prefix = $this->db->dbprefix($site.'_form');
                        $data = $this->form_model->cache($site);
                    } elseif (strpos($name, 'page-field') === 0) {
                        // 单网页字段缓存
                        list($a, $b, $site) = explode('-', $name);
                        $this->load->model('page_model');
                        $this->page_model->link = $this->site[$site];
                        $this->page_model->tablename = $this->link->dbprefix($site.'_page');
                        $this->page_model->cache($site);
                        $data = $this->dcache->get('page-field-'.$site);
                    } elseif (strpos($name, 'page-') === 0) {
                        // 单网页缓存
                        list($a, $site) = explode('-', $name);
                        $this->load->model('page_model');
                        $this->page_model->link = $this->site[$site];
                        $this->page_model->tablename = $this->link->dbprefix($site.'_page');
                        $this->page_model->cache($site);
                        $data = $this->dcache->get('page-'.$site);
                    } elseif (strpos($name, 'linklevel-') === 0) {
                        // 联动菜单级别缓存
                        list($a, $site) = explode('-', $name);
                        $this->load->model('linkage_model');
                        $this->linkage_model->cache($site);
                        $data = $this->dcache->get('linklevel-'.$site);
                    } elseif (strpos($name, 'linkage-') === 0) {
                        // 联动菜单
                        list($a, $site, $code) = explode('-', $name);
                        $this->load->model('linkage_model');
                        $this->linkage_model->cache($site);
                        $data = $this->dcache->get('linkage-'.$site.'-'.$code);
                    } elseif (strpos($name, 'app') === 0 && $name == 'app') {
                        // 应用缓存
                        list($a, $app) = explode('-', $name);
                        $this->load->model('application_model');
                        $data = $this->application_model->cache();
                    } elseif (strpos($name, 'block-') === 0) {
                        // 文字块缓存
                        list($a, $site) = explode('-', $name);
                        $this->load->model('system_model');
                        $data = $this->system_model->block($site);
                    } else {
                        //var_dump($name);
                    }

                    $this->$var = $data;
                }
            }
        }

        if (!$param) {
            return $data;
        }

        $var = '';
        foreach ($param as $v) {
            $var.= '[\'' . $v . '\']';
        }

        eval('$return = $data' . $var . ';');

        return $return;
    }

    /**
     * 临时数据缓存读取
     *
     * @param	string	$name	缓存名称
     * @return  array
     */
    public function get_cache_data($name) {

        if (!$name) {
            return NULL;
        }

        if (SYS_MEMCACHE && $this->cache->memcached->is_supported()) {
            $data = $this->cache->memcached->get(SYS_KEY . $name); // memcached缓存
        } else {
            $data = @$this->cache->file->get($name); // 无任何缓存
        }

        return $data;
    }

    /**
     * 临时数据缓存
     *
     * @param	string	$name	缓存名称
     * @param	array	$data	缓存数据
     * @param	intval	$ttl	时间（秒）
     * @return  array
     */
    public function set_cache_data($name, $data, $ttl = 3600) {

        if (!$name || !$ttl) {
            return $data;
        }

        if (SYS_MEMCACHE && $this->cache->memcached->is_supported()) {
            $this->cache->memcached->save(SYS_KEY . $name, $data, $ttl); // memcached缓存
        } else {
            $this->cache->file->save($name, $data, $ttl); // 无任何缓存时采用文件缓存
        }

        return $data;
    }

    // 模块缓存互数据
    public function get_module($siteid) {

        $mod = array();
        $MOD = $this->get_cache('module', $siteid);

        if ($MOD) {
            foreach ($MOD as $dir) {
                $mod[$dir] = $this->get_cache('module-'.$siteid.'-'.$dir);
            }
        }

        return $mod;
    }

    /**
     * 初始化网站全局变量
     */
    private function _init_variable() {

        define('IS_AJAX', $this->input->is_ajax_request());
        define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) ? TRUE : FALSE);
        define('SYS_TIME', $_SERVER['REQUEST_TIME'] ? $_SERVER['REQUEST_TIME'] : time());
        define('SITE_PATH', '/');

        $this->load->library('session');
        $host = strtolower($_SERVER['HTTP_HOST']); // 当前网站的域名
        $domain = require FCPATH.'config/domain.php'; // 加载站点域名配置文件
        $sitecfg = directory_map(FCPATH.'config/site/'); // 加载全部站点的配置文件
        foreach ($sitecfg as $file) {
            $id = (int)basename($file);
            if (is_file(FCPATH.'config/site/'.$file)) {
                $this->SITE[$id] = require FCPATH.'config/site/'.$file;
                $this->SITE[$id]['SITE_ID'] = (int) $id;
                $this->SITE[$id]['SITE_URL'] = 'http://'.($this->SITE[$id]['SITE_DOMAIN'] ? $this->SITE[$id]['SITE_DOMAIN'] : $host) . SITE_PATH;
            }
        }
        unset($sitecfg);

        // 分析站点信息
        $siteid = NULL;
        if (IS_ADMIN && $this->session->userdata('siteid')) {
            $siteid = (int)$this->session->userdata('siteid');
        }
        if ($siteid && isset($this->SITE[$siteid]) && isset($domain[$host])) {
            // 通过session来获取siteid
            define('SITE_ID', $siteid);
        } elseif (isset($domain[$host]) && isset($this->SITE[$domain[$host]])) {
            // 通过域名来获取siteid
            $siteid = (int)$domain[$host];
            // 当前网站存在移动端域名时
            if (isset($this->SITE[$siteid]['SITE_MOBILE'])
                && $this->SITE[$siteid]['SITE_MOBILE']
                && APP_DIR != 'weixin') {
                if ($host == $this->SITE[$siteid]['SITE_MOBILE']) {
                    // 当此域名是移动端域名时重新赋值给主站URL
                    $this->SITE[$siteid]['SITE_PC'] = $this->SITE[$siteid]['SITE_URL'];
                    $this->SITE[$siteid]['SITE_URL'] = 'http://'.$host.'/';
                    $this->SITE[$siteid]['SITE_MOBILE'] = TRUE;
                } elseif ($this->agent->is_mobile()
                    && $this->SITE[$siteid]['SITE_MOBILE_OPEN']
                    && $this->SITE[$siteid]['SITE_MOBILE']) {
                    // 当网站开启强制定向时
                    redirect('http://'.$this->SITE[$siteid]['SITE_MOBILE'], '301');
                    exit;
                }
            }
            define('SITE_ID', $siteid);
        } else {
            // 默认站点id
            define('SITE_ID', 1);
        }

        // 全局化站点变量
        $config1 = require FCPATH.'config/system.php'; // 加载网站系统配置文件
        $config2 = require FCPATH.'config/version.php'; // 加载系统版本更新文件
        $config3 = array_merge($config1, $config2); // 合并配置数组
        if ($this->SITE[SITE_ID]) {
            $config3 = array_merge($config3, $this->SITE[SITE_ID]);
        }

        // 将配置文件数组转换为常量
        foreach ($config3 as $var => $value) {
            define($var, $value);
        }
        unset($config3, $config2, $config1);

        // 判断手机端与PC端模板
        $this->mobile = SITE_MOBILE === TRUE ? TRUE : FALSE;

        // 首页静态文件过期检查
        if (!$this->mobile
            && SITE_HOME_INDEX
            && !IS_ADMIN
            && !IS_MEMBER
            && APP_DIR == ''
            && basename(APPPATH) == 'mantob'
            && $this->router->class == 'home'
            && $this->router->method == 'index') {
            $file = FCPATH.'cache/index/home-'.SITE_ID.'.html';
            if (!is_file($file) || (is_file($file) && filemtime($file) + SITE_HOME_INDEX < SYS_TIME)) {
                @unlink($file);
            } else {
                echo file_get_contents($file);
                exit;
            }
        }

        // 模块静态文件过期检查
        if (!$this->mobile
            && SITE_MODULE_INDEX
            && !IS_ADMIN
            && !IS_MEMBER
            && APP_DIR
            && $this->router->class == 'home'
            && $this->router->method == 'index') {
            $file = FCPATH.'cache/index/'.APP_DIR.'-'.SITE_ID.'.html';
            if (!is_file($file) || (is_file($file) && filemtime($file) + SITE_MODULE_INDEX < SYS_TIME)) {
                @unlink($file);
            } else {
                echo file_get_contents($file);
                exit;
            }
        }

        // 显示错误提示
        if (SYS_DEBUG) {
            error_reporting(E_ALL ^ E_NOTICE);
        }

        $this->config->set_item('language', SITE_LANGUAGE); // 网站语言
        date_default_timezone_set('Etc/GMT'.(SITE_TIMEZONE > 0 ? '-' : '+').abs(SITE_TIMEZONE)); // 设置时区
        $this->lang->load('member');
        $this->load->model('member_model'); // 加载会员模型

        $MEMBER = $this->get_cache('member');
        if ($MEMBER['setting']['ucenter'] && is_file(FCPATH.'member/ucenter/config.inc.php')) {
            include FCPATH.'member/ucenter/config.inc.php';
            include FCPATH.'member/ucenter/uc_client/client.php';
        }

        define('MEMBER_OPEN_SPACE', $MEMBER['setting']['space']['open']);
        require ENVIRONMENT.'/database.php';
        foreach ($this->SITE as $sid => $t) {
            $this->site[$sid] = isset($db[$sid]) ? $this->load->database((string) $sid, TRUE) : $this->db; // 站点数据库对象
        }

        // 当前登录会员信息
        $this->uid = (int)$this->member_model->member_uid();
        $this->member = $this->member_model->get_member($this->uid);

        // 会员不存在时，uid设置为0
        if (!$this->member) {
            $this->uid = 0;
        }

        // 管理员登陆才显示数据库错误提示
        if ($this->member['adminid'] || IS_ADMIN) {
            $this->db->db_debug = $this->site[SITE_ID]->db_debug = TRUE;
        } else {
            $this->db->db_debug = $this->site[SITE_ID]->db_debug = FALSE;
        }

        // 当前会员组的权限信息
        $this->markrule = $this->member ? $this->member['mark'] : 0;
        $this->member_rule = isset($MEMBER['setting']['permission'][$this->markrule]) ? $MEMBER['setting']['permission'][$this->markrule] : NULL; // 当前会员权限规则

        // 当前会员的模块栏目权限规则
        $MOD = $this->get_module(SITE_ID);
        if ($MOD && APP_DIR && $MOD[APP_DIR]['category']) {
            foreach ($MOD[APP_DIR]['category'] as $c) {
                $this->module_rule[$c['id']] = $c['permission'][$this->markrule];
            }
        }

        // 会员域名
        $url = SITE_URL.'member/';
        if (!$this->mobile
            && isset($MEMBER['setting']['domain'][SITE_ID])
            && $MEMBER['setting']['domain'][SITE_ID]) {
            // 当非移动端时且存在当前站点的会员域名就采用指定的域名
            $url = 'http://'.$MEMBER['setting']['domain'][SITE_ID].'/';
        }
        define('MEMBER_URL', $url);

        // 会员目录
        $url = '/member/';
        if (IS_MEMBER
            && isset($MEMBER['setting']['domain'][SITE_ID])
            && $MEMBER['setting']['domain'][SITE_ID]
            && $_SERVER['HTTP_HOST'] == $MEMBER['setting']['domain'][SITE_ID]) {
            // 当是会员中心时且存在当前站点的会员域名就采用绝对根目录
            $url = '/';
        } elseif (APP_DIR
            && isset($MOD[APP_DIR]['site'][SITE_ID]['domain'])
            && $MOD[APP_DIR]['site'][SITE_ID]['domain']
            && $_SERVER['HTTP_HOST'] == $MOD[APP_DIR]['site'][SITE_ID]['domain']) {
            // 如果当前是模块且模块绑定了域名那么就采用绝对URL
            $url = MEMBER_URL.'/member/';
        }
        define('MEMBER_PATH', $url);

        // 会员风格和模板目录
        $theme = $MEMBER['group'][($this->member ? $this->member['groupid'] : 1)]['theme'];
        $template = $MEMBER['group'][($this->member ? $this->member['groupid'] : 1)]['template'];

        // 风格和模板常量
        define('MEMBER_THEME', $theme ? $theme : 'default');
        // define('MEMBER_TEMPLATE', $template ? $template : 'default');
          if($template){
        if($siteid==2){
            if(MEMBER_URL=="http://".$_SERVER['HTTP_HOST'].'/member/'){
                define('MEMBER_TEMPLATE',  'english');
            }else{
                 define('MEMBER_TEMPLATE', $template ? $template : 'default');
            }
            
        }else{
             define('MEMBER_TEMPLATE', $template ? $template : 'default');
        }
       
        }else{
        define('MEMBER_TEMPLATE', $template ? $template : 'default');
        }

        define('MEMBER_THEME_PATH', MEMBER_URL.'statics/'.MEMBER_THEME.'/');
        define('MEMBER_MOBILE_PATH', MEMBER_URL.'mobiles/'.SITE_THEME.'/');

        define('HOME_THEME_PATH', SITE_URL.'mantob/statics/'.SITE_THEME.'/');
        define('HOME_MOBILE_PATH', SITE_URL.'mantob/mobiles/'.SITE_THEME.'/');
        unset($url, $theme, $template);

        $this->load->library('template');
        $this->load->model('cron_model');
        $this->load->model('system_model');

        if (IS_MEMBER) {
            // 会员部分
            $this->load->helper(array('system', 'url'));
            $uri = str_replace('/member/', '/', $this->duri->uri(NULL, TRUE));
            $menu = $this->get_cache('member-menu');
            $this->pagesize = $MEMBER['setting']['pagesize'];
            if ($menu['data']) {
                foreach ($menu['data'] as $i => $t) {
                    if ($t['mark']) {
                        list($a, $dir) = explode('-', $t['mark']);
                        if (!isset($MOD[$dir])
                            || $MOD[$dir]['setting']['member'][$this->markrule]) {
                            unset($menu['data'][$i]);
                            continue;
                        }
                        // 第一个分组菜单
                        $one = @reset($menu['data'][$i]['left']);
                        $pid = $one['id'];
                        // 判断发布权限
                        if (!$this->_module_post_catid($MOD[$dir])) {
                            if ($one['link']) {
                                foreach ($one['link'] as $o) {
                                    // 过滤无发布权相关的菜单
                                    if (in_array($o['uri'], array(
                                        $dir.'/home/index',
                                        $dir.'/home/flag',
                                        $dir.'/back/index',
                                        $dir.'/verify/index',
                                        $dir.'/everify/index',
                                        $dir.'/eback/index'))) {
                                        unset($menu['data'][$i]['left'][$pid]['link'][$o['id']]);
                                    }
                                }
                            }
                        }
                        // 为模块增加表单菜单
                        if ($MOD[$dir]['form'] && $pid) {
                            foreach ($MOD[$dir]['form'] as $f) {
                                $furi = APP_DIR.'/form_'.SITE_ID.'_'.$f['id'].'/index';
                                $link = array(
                                    'id' => $t['id'].'-'.$f['id'],
                                    'tid' => $i,
                                    'pid' => $pid,
                                    'uri' => $furi,
                                    'name' => dr_lang('m-097', $f['name']),
                                );
                                $menu['data'][$i]['left'][$pid]['link'][] = $menu['uri'][$furi] = $link;
                            }
                        }
                    }
                    if ($menu['data'][$i]['left']) {
                        $left = @reset($menu['data'][$i]['left']);
                        if ($left['link']) {
                            $link = @reset($left['link']);
                            if ($link) {
                                $menu['data'][$i]['uri'] = $link['uri'];
                                $menu['data'][$i]['url'] = $link['url'];
                            } else {
                                unset($menu['data'][$i]);
                            }
                        } else {
                            unset($menu['data'][$i]);
                        }
                    } else {
                        unset($menu['data'][$i]);
                    }
                }
            }

            // 当前会员组不允许使用空间时，删除空间菜单
            if ((!MEMBER_OPEN_SPACE || !$this->member['allowspace'] )
                && isset($menu['data'][3])) {
                unset($menu['data'][3]);
            }

            // 增加内容扩展菜单
            if (strpos($uri, APP_DIR.'/extend/') !== FALSE
                || preg_match('/^'.APP_DIR.'\/form_[0-9]+_[0-9]+\/listc/Ui', $uri)) {
                $cur = $menu['uri'][APP_DIR.'/home/index'];
            } else {
                $cur = isset($menu['uri'][$uri]) ? $menu['uri'][$uri] : $menu['uri'][str_replace(strrchr($uri, '/'), '/index', $uri)];
            }

            // 当当前顶级菜单是应用菜单时统计通知及消息数量
            if ( $menu['data'][$cur['tid']]['left']) {
                $find = 0;
                foreach ($menu['data'][$cur['tid']]['left'] as $lid => $left) {
                    if ($left['link']) {
                        foreach ($left['link'] as $mid => $link) {
                            if (strpos($link['uri'], 'pm/index') !== FALSE) {
                                // 统计未读短消息
                                $total = $this->db
                                              ->where('uid', (int)$this->uid)
                                              ->where('isnew', 1)
                                              ->count_all_results('pm_members');
                                $menu['data'][$cur['tid']]['left'][$lid]['link'][$mid]['name'].= ' ('.$total.')';
                                $find ++;
                            } elseif (strpos($link['uri'], 'notice/index') !== FALSE) {
                                // 统计未读系统提醒
                                $total = $this->db
                                              ->where('uid', (int) $this->uid)
                                              ->where('type', 1)
                                              ->where('isnew', 1)
                                              ->count_all_results('member_notice_'.(int)$this->member['tableid']);
                                $menu['data'][$cur['tid']]['left'][$lid]['link'][$mid]['name'].= ' ('.$total.')';
                                $find ++;
                            } elseif (strpos($link['uri'], 'notice/member') !== FALSE) {
                                // 统计未读会员提醒
                                $total = $this->db
                                              ->where('uid', (int) $this->uid)
                                              ->where('type', 2)
                                              ->where('isnew', 1)
                                              ->count_all_results('member_notice_'.(int)$this->member['tableid']);
                                $menu['data'][$cur['tid']]['left'][$lid]['link'][$mid]['name'].= ' ('.$total.')';
                                $find ++;
                            } elseif (strpos($link['uri'], 'notice/module') !== FALSE) {
                                // 统计未读模块提醒
                                $total = $this->db
                                              ->where('uid', (int) $this->uid)
                                              ->where('type', 3)
                                              ->where('isnew', 1)
                                              ->count_all_results('member_notice_'.(int)$this->member['tableid']);
                                $menu['data'][$cur['tid']]['left'][$lid]['link'][$mid]['name'].= ' ('.$total.')';
                                $find ++;
                            } elseif (strpos($link['uri'], 'notice/app') !== FALSE) {
                                // 统计未读应用提醒
                                $total = $this->db
                                              ->where('uid', (int) $this->uid)
                                              ->where('type', 4)
                                              ->where('isnew', 1)
                                              ->count_all_results('member_notice_'.(int)$this->member['tableid']);
                                $menu['data'][$cur['tid']]['left'][$lid]['link'][$mid]['name'].= ' ('.$total.')';
                                $find ++;
                            }
                            if ($find >= 5) {
                                break;
                            }
                        }
                        if ($find >= 5) {
                            break;
                        }
                    }
                }
            }

            $this->template->assign(array(
                'uid' => $this->uid,
                'menu' => $menu['data'],
                'menu_id' => (int)$cur['id'], // 当前URI对应的菜单id
                'menu_pid' => (int)$cur['pid'], // 当前URI对应的父级菜单id
                'menu_tid' => (int)$cur['tid'], // 当前URI对应的顶级菜单id
                'meta_name' => $cur['name'], // 当前菜单名称作为标题名称
                'regverify' => $MEMBER['setting']['regverify'],
                'member_rule' => $this->member_rule,
            ));

            // 登录判断
            if (!in_array($this->router->class, array('register', 'login', 'api'))) {
                // 不验证标识
                $verify = TRUE;
				// 支付回调页面
				if (defined('DR_PAY_ID') && DR_PAY_ID) {
					$verify = FALSE;
				}
                // 会员空间
                if (MEMBER_OPEN_SPACE
                    && $this->router->class == 'home'
                    && $this->router->method == 'index'
                    && isset($_GET['uid'])) {
                    $verify = FALSE;
                }
                // 游客发布权限验证
                $uri = $this->router->class.'-'.$this->router->method;
                if (APP_DIR
                    && in_array($uri, array('home-add', 'home-field'))
                    && !$this->member) {
                    $verify = FALSE;
                }
                // 待审核会员组
                if ($this->member['groupid'] == 1
                    && ($this->router->class != 'home' || $this->router->method != 'index')) {
                    $this->member_msg(lang('m-085'));
                }
                $url = MEMBER_URL.SELF.'?c=login&m=index&backurl='.urlencode(dr_now_url());
                // 没有登录时
                if ($verify && !$this->uid) {
                    $this->member_msg(lang('m-039').$this->member_model->logout(), $url);
                }
                // 会员不存在时
                if ($verify && !$this->member) {
                    $this->member_msg(lang('m-040').$this->member_model->logout(), $url);
                }
                if ($this->uid) {
                    $this->member_model->init_member();
                    // 会员验证
                    if (APP_DIR
                        || ($uri != 'home-index' && $uri != 'pm-webchat')) {
                        if ($MEMBER['setting']['complete']
                            && !isset($this->member['complete'])
                            && $this->router->class != 'account') {
                            // 是否强制完善资料
                            $this->member_msg(lang('m-154'), dr_url('account/index'), 2, 2);
                        } elseif ($MEMBER['setting']['avatar']
                            && !$this->member['avatar']
                            && $this->router->class != 'account') {
                            // 是否强制上传头像
                            $this->member_msg(lang('m-153'), dr_url('account/avatar'), 2, 2);
                        } elseif ($MEMBER['setting']['mobile']
                            && !$this->member['ismobile']
                            && $this->router->class != 'account') {
                            // 是否强制手机认证
                            $this->member_msg(lang('m-094'), dr_url('account/index'), 2, 2);
                        }
                    }
                }
            }
        } elseif (IS_ADMIN) {
            // 后台部分
            $this->lang->load('admin');
            $this->lang->load('member');
            $this->lang->load('template');
            $this->load->helper(array('system', 'url'));
            $this->load->model('auth_model');
            $uri = $this->duri->uri();
            $this->admin = $this->is_admin_login();
            if (!$this->is_auth($uri)) {
                if (IS_AJAX) {
                    exit('<img src='.SITE_URL.'member/statics/js/skins/icons/error.png>'.dr_lang('049', $uri));
                } else {
                    $this->admin_msg(dr_lang('049', $uri));
                }
            }
            $this->template->assign('admin', $this->admin);
            // 后台日志
            if (SYS_LOG) {
                $uri = $this->duri->uri(); // 当前uri
                $data = $this->duri->uri2ci($uri);
                $data['uri'] = $uri;
                $data['url'] = $this->duri->uri2url($uri);
                $data['admin'] = $this->admin['username'];
                $data['time'] = SYS_TIME;
                $data['ip'] = $this->input->ip_address();
                $path = FCPATH.'cache/optionlog/'.date('Ym', SYS_TIME).'/';
                $file = $path.date('d', SYS_TIME).'.log';
                if (!is_dir($path)) {
                    dr_mkdirs($path);
                }
                $log = is_file($file) ? @explode(PHP_EOL, file_get_contents($file)) : array();
                if (IS_POST
                    && !in_array($uri, array('admin/system/index', 'admin/home/index'))
                    && $data['class'] != 'api'
                    && $data['class'] != 'login') {
                    if ($log) {
                        $end = dr_string2array(end($log));
                        if ($end
                            && $end['uri'] == $data['uri']
                            && $data['admin'] == $end['admin']
                            && $data['time'] - $end['time'] < 10) {
                            // 10s内的重复操作不记录
                            unset($data);
                        }
                    }
                    // 记录日志
                    if ($data) {
                        file_put_contents($file, PHP_EOL.dr_array2string($data), FILE_APPEND);
                    }
                }
                unset($data, $uri, $path, $file, $log, $end);
            }

            // 后台性能分析
            if (SYS_DEBUG
                && !IS_AJAX
                && $this->router->class != 'api') {
                $this->output->enable_profiler(TRUE);
            }

            // 销毁变量
            unset($MOD, $MEMBER);
        }
    }

    /**
     * 判断是否具有操作权限
     *
     * @param	string	$uri
     * @return	bool	有权限返回TRUE，否则返回FALSE
     */
    public function is_auth($uri) {

        // 管理员组不验证,表示通过
        if (!$this->admin || $this->admin['adminid'] == 1) {
            return TRUE;
        }

        $uri = trim(substr_count($uri, '/') == 1 ? 'admin/'.$uri : $uri, '/');

        // 后台首页不验证
        if ($uri == 'admin/home/main'
            || $uri == 'admin/home/index'
            || $uri == 'admin/root/my') {
            return TRUE;
        }

        // 判断对模块的栏目是否可管理
        if (!$this->_is_module_admin($uri)) {
            return FALSE;
        }

        $role = array_merge(
            $this->admin['role']['system'],
            $this->admin['role']['module'],
            $this->admin['role']['application']
        );
        if (!$role) {
            return FALSE;
        }

        // uri在当前角色的权限列表中就验证通过
        if (in_array($uri, $role)) {
            return TRUE;
        }

        $route = '/'; // 把uri转为标准路由
        $data = $this->duri->uri2ci($uri);
        $data['dir'] = $data['app'] ? $data['app'] : ($data['path'] ? $data['path'] : '');
        $data['dir'] && $route .= $data['dir'].'/';
        $data['directory'] && $route .= $data['directory'].'/';
        $data['class'] && $route .= $data['class'].'/';
        $data['method'] && $route .= $data['method'].'/';
        $route = trim($route, '/');

        // 标准路由在当前角色的权限列表中就验证通过
        if (in_array($route, $role)) {
            return TRUE;
        }

        $auth = $this->auth_model->get_auth_all();

        // uri不在权限所有列表中就不验证,表示通过
        if (!in_array($uri, $auth) && !in_array($route, $auth)) {
            return TRUE;
        }

        return FALSE;
    }

    // 服务器ip地址
    protected function _get_server_ip() {

        if (isset($_SERVER['SERVER_ADDR'])
            && $_SERVER['SERVER_ADDR']
            && $_SERVER['SERVER_ADDR'] != '127.0.0.1') {
            return $_SERVER['SERVER_ADDR'];
        }

        return gethostbyname($_SERVER['HTTP_HOST']);
    }

    /**
     * 后台操作界面中的顶部导航菜单
     *
     * @param	array	$menu
     * @return	string
     */
    protected function get_menu($menu) {

        if (!$menu) {
            return NULL;
        }

        $_i = 0;
        $_str = '';
        $_uri = $this->duri->uri(1); // 当前uri
        $_mark = TRUE;

        foreach ($menu as $name => $uri) {
            $uri = trim($uri, '/');
            if (!$name && !$uri) {
                continue;
            }
            $class = '';
            if (strpos($uri, '_js') !== FALSE) {
                $uri = substr($uri, 0, -3);
                $url = dr_dialog_url($this->duri->uri2url($uri), 'add');
            } else {
                $url = $this->duri->uri2url($uri);
                $class = ' class="onloading"';
            }
            if (!$this->is_auth($uri)) {
                continue;
            }
            $mark = $_i == 0 ? '{MARK}' : '';
            // 判断选中
            if ($this->get_menu_calss($menu, $uri, $_uri)) {
                $_mark = FALSE;
                $class = ' class="onloading on"';
            }
            $_str.= '<a href="'.$url.'" '.$class.$mark.'><em>'.$name.'</em></a><span>|</span>';
            $_i ++;
        }

        if ($_mark && $this->router->method == 'edit') {
            $_str.= '<a href="javascript:;" class="on"><em>'.lang('edit').'</em></a><span>|</span>';
            $_mark = FALSE;
        }

        return $_mark ? str_replace('{MARK}', ' class="on"', $_str) : str_replace('{MARK}', '', $_str);
    }

    private function get_menu_calss($menu, $uri, $_uri) {

        if ($uri == $_uri) {
            return TRUE;
        }

        if (!in_array($_uri, $menu)) {
            if (@strpos($_uri, $uri) === FALSE) {
                return FALSE;
            }
            $uri_arr = explode('/', $_uri);
            $uri_arr = array_slice($uri_arr, 0, -2);
            $__uri = implode('/', $uri_arr);
            if (in_array($__uri, $menu) && $__uri == $uri) {
                return TRUE;
            }
            return $this->get_menu_calss($menu, $uri, $__uri);
        }
    }

    /**
     * 分页
     * 
     * @param	
     * @return
     */
    protected function get_pagination($url, $total) {
        $this->load->library('pagination');
        $config['base_url'] = $url;
        $config['per_page'] = SITE_ADMIN_PAGESIZE;
        $config['next_link'] = lang('m-108');
        $config['prev_link'] = lang('m-107');
        $config['last_link'] = lang('m-109');
        $config['first_link'] = lang('m-110');
        $config['total_rows'] = $total;
        $config['cur_tag_open'] = '<span>';
        $config['cur_tag_close'] = '</span>';
        $config['use_page_numbers'] = TRUE;
        $config['query_string_segment'] = 'page';
        $this->pagination->initialize($config);
        return $this->pagination->create_links();
    }

    /**
     * 后台登录判断，返回当前登录用户信息
     *
     * @return void
     */
    protected function is_admin_login() {

        if (IS_ADMIN
            && ($this->router->class == 'login' || $this->router->class == 'api')) {
            return FALSE;
        }

        $uid = (int) $this->session->userdata('uid');
        $admin = (int) $this->session->userdata('admin');
        if ($this->uid == FALSE
            || $uid != $this->uid
            || $admin != $uid) {
            if (IS_AJAX) {
                exit('<img src='.SITE_URL.'member/statics/js/skins/icons/error.png>'.lang('040'));
            }
            if (IS_ADMIN) {
                redirect(SITE_URL . dr_url('login/index', array('backurl' => urlencode(dr_now_url()))), 'refresh');
            }
            return FALSE;
        }

        $data = $this->member_model->get_admin_member($this->uid, 1);
        if (!is_array($data)) {
            if (IS_ADMIN) {
                if ($data == 0) {
                    IS_AJAX ? exit(dr_json(0, '<img src='.SITE_URL.'member/statics/js/skins/icons/error.png>'.lang('043'))) : $this->admin_msg(lang('043'));
                } elseif ($data == -3) {
                    IS_AJAX ? exit(dr_json(0, '<img src='.SITE_URL.'member/statics/js/skins/icons/error.png>'.lang('045'))) : $this->admin_msg(lang('045'));
                } elseif ($data == -4) {
                    IS_AJAX ? exit(dr_json(0, '<img src='.SITE_URL.'member/statics/js/skins/icons/error.png>'.lang('046'))) : $this->admin_msg(lang('046'));
                }
            } else {
                return $data;
            }
        }

        return $data;
    }

    /**
     * 后台提示消息显示
     *
     * @param	string	$msg	提示信息
     * @param	string	$url	转向地址
     * @param	int		$mark	标示符号1：成功；0：失败；2：等待
     * @param	int		$time	等待时间
     * @return  void
     */
    public function admin_msg($msg, $url = '', $mark = 0, $time = 1) {
        $this->template->assign(array(
            'msg' => $msg,
            'url' => $url,
            'time' => $time,
            'mark' => $mark
        ));
        $this->template->display('msg.html', 'admin');
        exit;
    }

    /**
     * 会员提示消息显示
     *
     * @param	string	$msg	提示信息
     * @param	string	$url	转向地址
     * @param	int		$mark	标示符号1：成功；0：失败；2：等待
     * @param	int		$time	等待时间
     * @param	bool	$ajax	是否ajax提交显示
     * @return  void
     */
    public function member_msg($msg, $url = '', $mark = 0, $time = 1, $ajax = FALSE) {
        // 当指定为ajax提交或者系统提交状态为ajax则返回json数据
        if ($ajax || IS_AJAX) {
            exit(dr_json(($mark ? 1 : 0), $msg, $url));
        } else {
            $this->template->assign(array(
                'msg' => $msg,
                'url' => $url,
                'time' => $time,
                'mark' => $mark,
                'meta_name' => lang('m-030')
            ));
            $this->template->display('msg.html', 'member');
        }
        exit;
    }

    /**
     * 前端提示消息显示
     *
     * @param	string	$msg	提示信息
     * @param	string	$url	转向地址
     * @param	int		$mark	标示符号1：成功；0：失败；2：等待
     * @param	int		$time	等待时间
     * @return  void
     */
    public function msg($msg, $url = '', $mark = 0, $time = 1) {
        $this->template->assign(array(
            'msg' => $msg,
            'url' => $url,
            'time' => $time,
            'mark' => $mark
        ));
        $this->template->display('msg.html', '/');
        exit;
    }

    /**
     * 迷你提示消息显示
     *
     * @param	string	$msg	提示信息
     * @param	string	$url	转向地址
     * @param	int		$mark	标示符号1：成功；0：失败；2：等待
     * @param	int		$time	等待时间
     * @return  void
     */
    public function mini_msg($msg, $url = '', $mark = 0, $time = 1) {
        $this->template->assign(array(
            'msg' => $msg,
            'url' => $url,
            'time' => $time,
            'mark' => $mark
        ));
        $this->template->display('mini_msg.html', 'admin');
        exit;
    }

    /**
     * 付款提示消息显示
     *
     * @param	string	$msg	提示信息
     * @param	string	$url	转向地址
     * @param	intval	$mark	标示符号1：成功；0：失败；2：等待
     * @return  void
     */
    protected function pay_msg($msg, $url = '', $mark = 0) {
        $this->template->assign(array(
            'url' => $url,
            'msg' => $msg,
            'mark' => $mark,
        ));
        $this->template->display('pay_msg.html');
        exit;
    }

    /**
     * 验证码验证
     *
     * @param	string	$id	验证码表单的name
     * @return  bool
     */
    protected function check_captcha($id) {

        $data = $this->input->post($id);
        $data = $data ? $data : $this->input->get($id);
        $code = $this->session->userdata('captcha');

        if (strtolower($data) == $code) {
            $this->session->unset_userdata('captcha');
            return TRUE;
        }

        return FALSE;
    }

    /**
     * 验证码
     */
    public function captcha() {
        $this->load->library('captcha');
        $this->captcha->width = $this->input->get('width') ? $this->input->get('width') : 80;
        $this->captcha->height = $this->input->get('height') ? $this->input->get('height') : 30;
        $this->session->unset_userdata('captcha');
        $this->session->set_userdata('captcha', $this->captcha->get_code());
        $this->captcha->doimage();
    }

    /**
     * 获取系统运行信息
     *
     * @return  string
     */
    public function get_system_run_info() {
        return dr_lang(
            '314',
            $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end'),
            count($this->db->queries),
            str_replace(' ', '', dr_format_file_size(memory_get_usage() / 4))
        );
    }

    /**
     * 表单提交数据验证和过滤
     *
     * @param	array	$_field	字段
     * @param	array	$_data	修改前的数据
     * @return  array
     */
    protected function validate_filter($_field, $_data = array()) {

        $this->_data = $_data;
        $this->syn_content = $this->post = $this->data = array();
        $this->load->library('Dfield', array(APP_DIR));
        $this->load->library('Dfilter');
        $this->load->library('Dvalidate');

        foreach ($_field as $field) {
            // 前端字段筛选
            if (!IS_ADMIN && !$field['ismember']) {
				continue;
			}
            // 验证字段对象的有效性
            $obj = $this->dfield->get($field['fieldtype']);
            if (!$obj) {
				continue;
			}
            $name = $field['fieldname'];
            $validate = $field['setting']['validate'];
            // 禁止修改字段筛选
            if (IS_MEMBER && $validate['isedit']
                && isset($_data[$name]) && $_data[$name]) {
                $this->post[$name] = $_data[$name];
                $obj->insert_value($field); // 获取入库值
				continue;
			}
            // 从表单获取值
            // $this->post[$name] = $value = trim($this->input->post("data[$name]", $validate['xss'] ? FALSE : TRUE));
            // 
            $this->post[$name] = $value = $this->input->post("data[$name]", $validate['xss'] ? FALSE : TRUE);

            // 验证必填字段
            if ($field['fieldtype'] != 'Group'
                && isset($validate['required'])
                && $validate['required']) {
                // 验证值为空
                if ($value == '') {
                    return array('error' => $name, 'msg' => dr_lang('m-197', $field['name']));
                }
                // 当类别为联动时判定0值
                if ($field['fieldtype'] == 'Linkage' && !$value) {
                    return array('error' => $name, 'msg' => dr_lang('m-197', $field['name']));
                }
                // 正则验证
                if (isset($validate['pattern'])
                    && $validate['pattern']
                    && !preg_match($validate['pattern'], $value)) {
                    return array('error' => $name, 'msg' => $field['name'].'：'.($validate['errortips'] ? $validate['errortips'] : lang('m-198')));
                }
            }
            // 函数/方法校验
            if (isset($validate['check']) && $validate['check']) {
                if (strpos($validate['check'], '_') === 0) {
                    // 方法格式：_方法名称[:现存数据字段,参数2...]
                    list($method, $_param) = explode(':', str_replace('::', ':', $validate['check']));
                    $method = substr($method, 1);
                    if (method_exists($this->dvalidate, $method)) {
                        $param['value'] = $value;
                        if ('check_member' == $method && $value == 'guest') {
                            // 游客不验证
                        } else {
                            if ($_param && $_value = explode(',', $_param)) {
                                foreach ($_value as $t) {
                                    $param[$t] = isset($_POST['data'][$t]) ? $this->input->post("data[$t]") : $t;
                                }
                            }
                            if (call_user_func_array(array($this->dvalidate, $method), $param)) {
                                return array('error' => $name, 'msg' => $field['name'].'：'.lang('m-199'));
                            }
                        }
                    } else {
                        log_message('error', "校验方法 $method 不存在！");
                    }
                } else {
                    // 函数格式：函数名称[:现存数据字段,参数2...]
                    list($func, $_param) = explode(':', str_replace('::', ':', $validate['check']));
                    if (function_exists($func)) {
                        $param['value'] = $value;
                        if ($_param && $_value = explode(',', $_param)) {
                            foreach ($_value as $t) {
                                $param[$t] = isset($_POST['data'][$t]) ? $this->input->post("data[$t]") : $t;
                            }
                        }
                        if (call_user_func_array($func, $param)) {
                            return array('error' => $name, 'msg' => $field['name'].'：'.lang('m-199'));
                        }
                    } else {
                        log_message('error', "校验函数 $func 不存在！");
                    }
                }
            }
            // 过滤函数
            if (isset($validate['filter']) && $validate['filter']) {
                if (strpos($validate['filter'], '_') === 0) {
                    // 方法格式：_方法名称[:现存数据字段,参数2...]
                    list($method, $_param) = explode(':', str_replace('::', ':', $validate['filter']));
                    $method = substr($method, 1);
                    if (method_exists($this->dfilter, $method)) {
                        $param['value'] = $value;
                        if ($_param && $_value = explode(',', $_param)) {
                            foreach ($_value as $t) {
                                $param[$t] = isset($_POST['data'][$t]) ? $this->input->post("data[$t]") : $t;
                            }
                        }
                        // 开始过滤
                        $this->post[$name] = call_user_func_array(array($this->dfilter, $method), $param);
                    } else {
                        log_message('error', "过滤方法 $method 不存在！");
                    }
                } else {
                    // 函数格式：函数名称[:现存数据字段,参数2...]
                    list($func, $_param) = explode(':', str_replace('::', ':', $validate['filter']));
                    if (function_exists($func)) {
                        $param['value'] = $value;
                        if ($_param && $_value = explode(',', $_param)) {
                            foreach ($_value as $t) {
                                $param[$t] = isset($_POST['data'][$t]) ? $this->input->post("data[$t]") : $t;
                            }
                        }
                        // 开始过滤
                        $this->post[$name] = call_user_func_array($func, $param);
                    } else {
                        log_message('error', "过滤函数 $func 不存在！");
                    }
                }
            }
            // 判断表字段值的唯一性
            if ($field['ismain']
                && isset($field['setting']['option']['unique'])
                && $field['setting']['option']['unique']) {
                if ($this->site[SITE_ID]
                         ->where('id<>', (int)$_data['id'])
                         ->where($name, $this->post[$name])
                         ->count_all_results(SITE_ID.'_'.APP_DIR)) {
                    return array('error' => $name, 'msg' => dr_lang('m-201', $field['name']));
                }
            }
            $obj->insert_value($field); // 获取入库值
            if ($field['fieldtype'] == 'Baidumap') {
                $this->data[$field['ismain']][$name.'_lng'] = (double)$this->data[$field['ismain']][$name.'_lng'];
                $this->data[$field['ismain']][$name.'_lat'] = (double)$this->data[$field['ismain']][$name.'_lat'];
            } elseif ($field['fieldtype'] == 'Syn') {
                $temp = dr_string2array($this->data[$field['ismain']][$name]);
                $temp['name'] = $name;
                $this->syn_content = $temp;
                unset($temp);
            } else {
                if (strpos($field['setting']['option']['fieldtype'], 'INT') !== FALSE) {
                    $this->data[$field['ismain']][$name] = (int)$this->data[$field['ismain']][$name];
                } elseif ($field['setting']['option']['fieldtype'] == 'DECIMAL'
                    || $field['setting']['option']['fieldtype'] == 'FLOAT') {
                    $this->data[$field['ismain']][$name] = (double)$this->data[$field['ismain']][$name];
                }
            }
        }
        //echo '<pre>'; print_r($this->data);exit;
        unset($this->post, $this->_data);
        return $this->data;
    }

    /**
     * 附件处理
     *
     * @param	intval	$uid		会员uid
     * @param	array	$field		表字段
     * @param	string	$related	使用源的标识
     * @param	array	$_data		原数据
     * @param	bool	$update		是否更新原附件
     * @return	NULL
     */
    protected function attachment_handle($uid, $related, $field, $_data = NULL, $update = TRUE) {

        if (!$field) {
            return NULL;
        }
    
        // 当前POST的数据
        $attach = array();
        $attach_id = array();
        $this->load->model('attachment_model');
        $this->load->library('Dfield', array(APP_DIR));

        // 查询使用的附件
      
        foreach ($field as $f) {
            // 加载字段处理对象
            
            $obj = $this->dfield->get($f['fieldtype']);
      		
            if (!$obj) {
                continue;
            }
            $files = $obj->get_attach_id($this->data[$f['ismain']][$f['fieldname']]);
            if ($files) {
                $attach_id = $attach_id ? array_merge($attach_id, $files) : $files;
            }
        }
		
        // 筛选删除的附件
        foreach ($field as $f) {
            // 加载字段处理对象
            $obj = $this->dfield->get($f['fieldtype']);
            if (!$obj) {
                continue;
            }
            // 通过附件处理方法获得增加和删除的附件
            list($add_id, $del_id) = $obj->attach($this->data[$f['ismain']][$f['fieldname']], $_data[$f['fieldname']]);
			// 删除附件
            if ($del_id) {
                foreach ($del_id as $id) {
                    if ($id && $update && !in_array($id, $attach_id)) {
                        $this->attachment_model->delete_for_handle($uid, $related, $id);
                    }
                }
            }
            // 无新增附件时跳过
            if (!$add_id) {
                continue;
            }
            $attach = $attach ? array_merge($attach, $add_id) : $add_id;
        }

        $attach = $attach ? array_merge($attach, $attach_id) : $attach_id;
        if (count($attach) == 0) {
            return NULL;
        }
	
        // 更新至已用附件表
        $this->attachment_model->replace_attach($uid, $related, array_unique($attach));
		
		unset($this->data);
		
        return NULL;
    }

    

    /**
     * 附件归属替换
     *
     * @param	intval	$uid		会员uid
     * @param	intval	$id			使用源id
     * @param	string	$related	使用源表名称
     * @return	NULL
     */
    protected function attachment_replace($uid, $id, $related) {

        if (!$uid || !$id || !$related) {
			return NULL;
		}
		
        $data = $this->db
                ->where('related', $related.'_verify-'.$id)
                ->select('id,tableid')
                ->get('attachment')
                ->result_array();
        if (!$data) {
            return NULL;
        }

        foreach ($data as $t) {
            $this->db->where('id', $t['id'])->update('attachment', array('related' => $related.'-'.$id));
            $this->db->where('id', $t['id'])->update('attachment_'.(int)$t['tableid'], array('related' => $related.'-'.$id));
        }

        return NULL;
    }

    /**
     * 判断站点表是否重复
     *
     * @param	string	$tablename
     * @return	bool
     */
    public function site_table_exitis($tablename) {

        // 目录
        if (is_dir(FCPATH . $tablename)) {
            return TRUE;
        }

        // 数据来源表
        $num = $this->db->where('tablename', $tablename)->count_all_results('source');
        if ($num) {
            return TRUE;
        }

        // 模块表
        $num = $this->db->where('dirname', $tablename)->count_all_results('module');
        if ($num) {
            return TRUE;
        }

        // 库中筛选
        $table = $this->system_model->cache();
        if (isset($table[$this->db->dbprefix($tablename)])) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * 字段输出表单
     *
     * @param	array	$field	字段数组
     * @param	array	$data	表单值
     * @param	bool	$cat	是否显示栏目附加字段
     * @param	string	$id		id字符串
     * @return	string
     */
    public function field_input($field, $data = NULL, $cat = FALSE, $id = 'id') {


        $group = array();
        $myfield = $mygroup = $mycat = $mark = '';
        if ($cat == TRUE) {
            $mycat = '<tbody id="dr_category_field"></tbody>';
        }

        if (!$field) {
            return $mycat;
        }

        // 分组字段筛选
        foreach ($field as $t) {
            if ($t['fieldtype'] == 'Group'
                && preg_match_all('/\{(.+)\}/U', $t['setting']['option']['value'], $value)) {
                foreach ($value[1] as $v) {
                    $group[$v] = $t['fieldname'];
                }
            }
        }

        // 字段类
        $this->load->library('dfield', array(APP_DIR));
        $pchtml = $this->get_cache('member', 'setting', 'field');
        $mbhtml = $this->get_cache('member', 'setting', 'mbfield');
        if (!IS_ADMIN) {
            if ($this->mobile && $mbhtml) {
                // 移动端格式
                A_Field::set_input_format($mbhtml);
                unset($mbhtml);
            } elseif ($pchtml) {
                // Pc端格式
                A_Field::set_input_format($pchtml);
                unset($pchtml);
            }
        }

        // 主字段
        foreach ($field as $t) {
            if (!IS_ADMIN
                && !$t['ismember']) {
                continue;
            }
            $obj = $this->dfield->get($t['fieldtype']);
            if (is_object($obj)) {
                // 百度地图特殊字段
                $value = $t['fieldtype'] == 'Baidumap' ? ($data[$t['fieldname'].'_lng'] && $data[$t['fieldname'].'_lat'] ? $data[$t['fieldname'].'_lng'].','.$data[$t['fieldname'].'_lat'] : $data[$t['fieldname']]) : $data[$t['fieldname']];
                $input = $obj->input($t['name'], $t['fieldname'], $t['setting'], $value, isset($data[$id]) ? $data[$id] : 0);
                if (isset($group[$t['fieldname']])) {
                    $input = preg_replace('/(<tr id=.*<td>)/Usi', '', $input);
                    $input = str_replace(array('</td>', '</tr>'), '', $input);
                    $mygroup[$t['fieldname']] = $input;
                } else {
                    // 将栏目附加字段放在内容或者作者上面一行
                    if ($cat == TRUE
                        && $mark == ''
                        && in_array($t['fieldname'], array('content', 'hits'))) {
                        $mark = 1;
                        $myfield.= $mycat;
                    }
                    $myfield.= $input;
                }
            }
        }

        if ($cat == TRUE && $mark == '') {
            $myfield.= $mycat;
        }

        if ($mygroup) {
            foreach ($mygroup as $name => $t) {
                $myfield = str_replace('{'.$name.'}', $t, $myfield);
            }
        }

        return $myfield;
    }

    /**
     * 字段输出格式化
     *
     * @param	array	$fields 	可用字段集
     * @param	array	$data		数据
     * @param	intval	$curpage	分页id
     * @param	string	$dirname	模块目录
     * @return	string
     */
    public function field_format_value($fields, $data, $curpage = 1, $dirname = NULL) {

        if (!$fields
            || !$data
            || !is_array($data)) {
            return $data;
        }

        foreach ($data as $n => $value) {
            if (isset($fields[$n])) {
                $format = dr_get_value($fields[$n]['fieldtype'], $value, $fields[$n]['setting']['option'], $dirname);
                if ($format !== $value) {
                    $data['_'.$n] = $value;
                    $data[$n] = $format;
                } elseif (SITE_MOBILE !== TRUE
                    && $n == 'content' && $fields[$n]['fieldtype'] == 'Ueditor'
                    && strpos($value, '<div name="dr_page_break" class="pagebreak">') !== FALSE
                    && preg_match_all('/<div name="dr_page_break" class="pagebreak">(.*)<\/div>/Us', $value, $match)
                    && preg_match('/(.*)<div name="dr_page_break"/Us', $value, $frist)) {
                    // 编辑器分页 老版本
                    $page = 1;
                    $content = $title = array();
                    $data['_'.$n] = $value;
                    $content[$page]['title'] = dr_lang('m-131', $page);
                    $content[$page]['body'] = $frist[1];
                    foreach ($match[0] as $i => $t) {
                        $page ++;
                        $value = str_replace($content[$page - 1]['body'].$t, '', $value);
                        if (preg_match('/(.*)<div name="dr_page_break"/Us', $value, $match_body)) {
                            $body = $match_body[1];
                        } else {
                            $body = $value;
                        }
                        $title[$page] = trim($match[1][$i]);
                        $content[$page]['title'] = trim($match[1][$i]) ? trim($match[1][$i]) : dr_lang('m-131', $page);
                        $content[$page]['body'] = $body;
                    }
                    $page = max(1, min($page, $curpage));
                    $data[$n] = $content[$page]['body'];
                    $data[$n.'_page'] = $content;
                    $data[$n.'_title'] = $title[$page];
                } elseif (SITE_MOBILE !== TRUE
                    && $n == 'content' && $fields[$n]['fieldtype'] == 'Ueditor'
                    && strpos($value, '<p class="pagebreak">') !== FALSE
                    && preg_match_all('/<p class="pagebreak">(.*)<\/p>/Us', $value, $match)
                    && preg_match('/(.*)<p class="pagebreak">/Us', $value, $frist)) {
                    // 编辑器分页 新版
                    $page = 1;
                    $content = $title = array();
                    $data['_'.$n] = $value;
                    $content[$page]['title'] = dr_lang('m-131', $page);
                    $content[$page]['body'] = $frist[1];
                    foreach ($match[0] as $i => $t) {
                        $page ++;
                        $value = str_replace($content[$page - 1]['body'].$t, '', $value);
                        if (preg_match('/(.*)<p class="pagebreak"/Us', $value, $match_body)) {
                            $body = $match_body[1];
                        } else {
                            $body = $value;
                        }
                        $title[$page] = trim($match[1][$i]);
                        $content[$page]['title'] = trim($match[1][$i]) ? trim($match[1][$i]) : dr_lang('m-131', $page);
                        $content[$page]['body'] = $body;
                    }
                    $page = max(1, min($page, $curpage));
                    $data[$n] = $content[$page]['body'];
                    $data[$n.'_page'] = $content;
                    $data[$n.'_title'] = $title[$page];
                }
            } elseif (strpos($n, '_lng') !== FALSE) {
                // 百度地图
                $name = str_replace('_lng', '', $n);
                if (isset($data[$name.'_lat'])) {
                    $data[$name] = '';
                    if ($data[$name.'_lng'] > 0 || $data[$name.'_lat'] > 0) {
                        $data[$name] = $data[$name.'_lng'].','.$data[$name.'_lat'];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 当前管理角色组审核权限
     *
     * @return	array|NULL
     */
    protected function _get_verify() {

        $data = $this->get_cache('verify');
        if (!$data) {
            return NULL;
        }

        foreach ($data as $id => $t) {
            foreach ($t['verify'] as $status => $v) {
                if (in_array($this->admin['adminid'], $v)) {
                    $data[$id]['status'][] = $status;
                }
            }
            if (!isset($data[$id]['status'])) {
                unset($data[$id]);
            }
        }

        return $data;
    }

    /**
     * 当前会员对模块的可用栏目发布权限
     *
     * @param	array	$module		模块缓存数据
     * @param	string	$markrule	权限标识
     * @return  array	可用栏目id
     */
    protected function _module_post_catid($module, $markrule = NULL) {

        // 当模块没有添加栏目数据时标识为禁用状态
        if (!$module['category']) {
            return NULL;
        }

        $catid = array();
        $markrule = $markrule ? $markrule : $this->markrule;

        foreach ($module['category'] as $cat) {
            // 跳过有下级栏目的判断
            if ($cat['child']) {
                continue;
            }
            // 当栏目中存在一项是非禁用就标识为非禁用状态
            if (isset($cat['permission'][$markrule]['add'])
                && $cat['permission'][$markrule]['add'] == 1) {
                $catid[] = (int) $cat['id'];
            }
        }

        return $catid;
    }

    /**
     * 判断当前管理角色权限 (管理)
     *
     * @param	string	$uri	模块缓存数据
     * @return  bool	TRUE可以管理 | FALSE不能管理
     */
    public function _is_module_admin($uri) {

        $MOD = $this->get_module(SITE_ID);
        list($dir, $directory, $class, $method) = explode('/', $uri);

        // 非模块时跳出不判断
        if (!isset($MOD[$dir])) {
            return TRUE;
        }

        // 非内容控制器跳过
        if ($class != 'home' || $method != 'index') {
            return TRUE;
        }

        // 当模块没有添加栏目数据时标识为不可以管理
        if (!$MOD[$dir]['category']) {
            return FALSE;
        }

        foreach ($MOD[$dir]['category'] as $cat) {
            // 跳过有下级栏目的判断
            if ($cat['child']) {
                continue;
            }
            // 当栏目中存在一项是管理就标识为管理状态
            if (isset($cat['setting']['admin'][$this->admin['adminid']]['show'])
                && $cat['setting']['admin'][$this->admin['adminid']]['show'] == 1) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * 邮件直接发送
     *
     * @param	string	$tomail
     * @param	string	$subject
     * @param	string	$message
     * @return  bool
     */
    public function sendmail($tomail, $subject, $message) {
        return $this->member_model->sendmail($tomail, $subject, $message);
    }

    /**
     * 邮件队列发送
     *
     * @param	string	$tomail
     * @param	string	$subject
     * @param	string	$message
     * @return  bool
     */
    public function sendmail_queue($tomail, $subject, $message) {

        if (!$tomail || !$subject || !$message) {
            return FALSE;
        }

        $this->cron_model->add(1, array(
            'tomail' => $tomail,
            'subject' => $subject,
            'message' => $message,
        ));

        return TRUE;
    }

    /**
     * 检查目录可写
     *
     * @param	string	$pathfile
     * @return	boolean
     */
    protected function _check_write_able($pathfile) {

        if (!$pathfile) {
            return FALSE;
        }

        $isDir = in_array(substr($pathfile, -1), array('/', '\\')) ? TRUE : FALSE;
        if ($isDir) {
            if (is_dir($pathfile)) {
                mt_srand((double) microtime() * 1000000);
                $pathfile = $pathfile.'dr_'.uniqid(mt_rand()).'.tmp';
            } elseif (@mkdir($pathfile)) {
                return self::_checkWriteAble($pathfile);
            } else {
                return FALSE;
            }
        }

        @chmod($pathfile, 0777);
        $fp = @fopen($pathfile, 'ab');
        if ($fp === FALSE) {
            return FALSE;
        }

        fclose($fp);
        $isDir && @unlink($pathfile);

        return TRUE;
    }

    // 执行sql
    public function sql_query($sql, $db = NULL) {

        if (!$sql) {
            return NULL;
        }

        $db = $db ? $db : $this->db;
        $sql_data = explode(';SQL_mantob_EOL', trim(str_replace(array(PHP_EOL, chr(13), chr(10)), 'SQL_mantob_EOL', $sql)));

        foreach ($sql_data as $query) {
            if (!$query) {
                continue;
            }
            $ret = '';
            $queries = explode('SQL_mantob_EOL', trim($query));
            foreach ($queries as $query) {
                $ret.= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
            }
            if (!$ret) {
                continue;
            }
            $db->query($ret);
        }
    }

    ////////////////////////////////////////////////////////////////

    /**
     * 站点间的同步登录
     */
    protected function api_synlogin() {

        $code = $this->encrypt->decode(str_replace(' ', '+', $this->input->get('code')));
        if (!$code) {
            exit('code is null');
        }

        list($uid, $salt) = explode('-', $code);

        if (!$uid || !$salt) {
            exit('data is null');
        }
        if (!$this->db->where('uid', $uid)->where('salt', $salt)->count_all_results('member')) {
            exit('check error');
        }
        
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        $expire = $this->input->get('expire') ? $this->input->get('expire') : 86400;
        $this->input->set_cookie('member_uid', $uid, $expire);
        $this->input->set_cookie('member_cookie', substr(md5(SYS_KEY.$uid), 5, 20), $expire);

        exit('ok');
    }

    /**
     * 站点间的同步退出
     */
    protected function api_synlogout() {
        if ($this->session->userdata('member_auth_uid')) {
            $this->session->unset_userdata('member_auth_uid');
        } else {
            $this->input->set_cookie('member_uid', 0, -1);
            $this->input->set_cookie('member_cookie', '', -1);
            if ($this->uid) {
                $this->db->delete('member_session', 'user_id='.$this->uid);
            }
        }
    }

    /**
     * 自定义信息JS调用
     */
    protected function api_template() {
        ob_start();
        $name = $this->input->get('name');
        $_GET['page'] = max(1, (int)$this->input->get('page'));
        $get = @json_decode(urldecode($this->input->get('get')), TRUE);
        $params = @json_decode(urldecode($this->input->get('params')), TRUE);
        $this->template->assign(array(
            'get' => $get,
            'params' => $params,
            'dirname' => $this->input->get('dirname')
        ));
        $this->template->assign($get);
        $this->template->assign($params);
        $this->template->display(strpos($name, '.html') ? $name : $name.'.html');
        $html = ob_get_contents();
        ob_clean();
        exit($html);
    }

    /**
     * 收藏文档
     */
    protected function api_favorite() {

        if (!$this->uid) {
            exit('1'); // 未登录
        }

        $id = (int)$this->input->get('id');
        $cid = (int)$this->input->get('cid');
        $mid = $cid ? $cid : $id; // 内容表id
        $eid = $cid ? $id : 0; // 扩展表id
        $data = $this->link
                     ->where('id', $mid)
                     ->select('url,title')
                     ->limit(1)
                     ->get(SITE_ID.'_'.APP_DIR)
                     ->row_array();
        if (!$data) {
            exit('2'); // 文档不存在
        }

        $table = SITE_ID.'_'.APP_DIR.'_favorite_'.(int)substr((string)$this->uid, -1, 1);
        $favorite = $this->link
                         ->where('cid', $mid)
                         ->where('uid', $this->uid)
                         ->where('eid', $eid)
                         ->select('id')
                         ->limit(1)
                         ->get($table)
                         ->row_array();
        if ($eid) {
            // 收藏扩展表
            $data2 = $this->link
                          ->where('cid', $mid)
                          ->get(SITE_ID.'_'.APP_DIR.'_extend')
                          ->row_array();
            if ($favorite) {
                $this->link
                     ->where('id', $mid)
                     ->where('eid', $eid)
                     ->update($table, array(
                        'url' => $data2['url'],
                        'title' => $data['title'].' - '.$data2['name']
                     )
                );
                exit('3'); // 更新成功
            } else {
                $this->link->insert($table, array(
                    'eid' => $eid,
                    'cid' => $mid,
                    'uid' => $this->uid,
                    'url' => $data2['url'],
                    'title' => $data['title'].' - '.$data2['name'],
                    'inputtime' => SYS_TIME,
                ));
                exit('4'); // 添加成功
            }
        } else {
            // 收藏主表
            if ($favorite) {
                $this->link->where('id', $mid)->update($table, array(
                    'url' => $data['url'],
                    'title' => $data['title']
                ));
                exit('3'); // 更新成功
            } else {
                $this->link->insert($table, array(
                    'eid' => 0,
                    'cid' => $mid,
                    'uid' => $this->uid,
                    'url' => $data['url'],
                    'title' => $data['title'] ? $data['title'] : '',
                    'inputtime' => SYS_TIME,
                ));
                exit('4'); // 添加成功
            }
        }
    }

    /**
     * SiteMap url段格式
     */
    private function _url_format($url, $time) {
        $xml = '    <url>'.PHP_EOL;
        $xml.= '        <loc>'.htmlspecialchars($url).'</loc>'.PHP_EOL;
        $xml.= '        <lastmod>'.$time.'</lastmod>'.PHP_EOL;
        $xml.= '        <changefreq>daily</changefreq>'.PHP_EOL;
        $xml.= '        <priority>1.0</priority>'.PHP_EOL;
        $xml.= '    </url>'.PHP_EOL;
        return $xml;
    }

    /**
     * SiteMap
     */
    public function sitemap() {
        header('Content-Type: text/xml');
        $module = $this->get_cache('module', SITE_ID);
        $cache_name = SITE_ID.'-'.(APP_DIR && in_array(APP_DIR, $module) ? APP_DIR.'-sitemap' : 'sitemap');
        $cache_data = $this->get_cache_data($cache_name);
        // 缓存不存在时重新生成缓存文件
        if (!$cache_data) {
            $db = $this->site[SITE_ID];
            $xml = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;
            $xml.= '<urlset>'.PHP_EOL;
            $page = $this->get_cache('page-'.SITE_ID);
            if (APP_DIR) {
                if (in_array(APP_DIR, $module) && $module[APP_DIR]['sitemap']) {
                    // 模块内容
                    $xml.= $this->_url_format(MODULE_URL, date('Y-m-d'));
                    $cat = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category');
                    if ($cat) {
                        foreach ($cat as $t) {
                            $xml.= $this->_url_format($t['url'], date('Y-m-d'));
                        }
                    }
                    $data = $db->where('status', 9)
                               ->select('url,updatetime,inputtime')
                               ->order_by('updatetime DESC')
                               ->limit(100)
                               ->get(SITE_ID.'_'.APP_DIR)
                               ->result_array();
                    if ($data) {
                        foreach ($data as $t) {
                            $xml.= $this->_url_format($t['url'], date('Y-m-d', $t['updatetime'] ? $t['updatetime'] : $t['inputtime']));
                        }
                        if ($data < 50) {
                            // 模块单页
                            if (isset($page['data'][APP_DIR])) {
                                foreach ($page['data'][APP_DIR] as $t) {
                                    if (!$t['urllink']) {
                                        $xml.= $this->_url_format($t['url'], date('Y-m-d'));
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // 首页
                $xml.= $this->_url_format(SITE_URL, date('Y-m-d'));
                // 模块
                if ($module) {
                    foreach ($module as $dir) {
                        // 模块内容
                        $mod = $this->get_cache('module-'.SITE_ID.'-'.$dir);
                        if ($mod['sitemap']) {
                            $xml.= $this->_url_format($mod['url'], date('Y-m-d'));
                            if ($mod['category']) {
                                foreach ($mod['category'] as $t) {
                                    $xml.= $this->_url_format($t['url'], date('Y-m-d'));
                                }
                            }
                            $data = $db->where('status', 9)
                                       ->select('url,updatetime,inputtime')
                                       ->order_by('updatetime DESC')
                                       ->limit(100)
                                       ->get(SITE_ID.'_'.$dir)
                                       ->result_array();
                            if ($data) {
                                foreach ($data as $t) {
                                    $xml.= $this->_url_format($t['url'], date('Y-m-d', $t['updatetime'] ? $t['updatetime'] : $t['inputtime']));
                                }
                                if ($data < 50) {
                                    // 模块单页
                                    if (isset($page['data'][$dir])) {
                                        foreach ($page['data'][$dir] as $t) {
                                            if (!$t['urllink']) {
                                                $xml.= $this->_url_format($t['url'], date('Y-m-d'));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // 首页单页
                if (isset($page['data']['index'])) {
                    foreach ($page['data']['index'] as $t) {
                        if (!$t['urllink']) {
                            $xml.= $this->_url_format($t['url'], date('Y-m-d'));
                        }
                    }
                }
            }
            $xml.= '</urlset>'.PHP_EOL;
            $cache_data = $this->set_cache_data($cache_name, $xml, 86400); // 网站地图缓存24小时
        }
        echo $cache_data;
    }

    /**
     * 手机版与电脑版切换
     */
    public function select_template() {
        $this->admin_msg('此功能已经取消，请在后台绑定移动端域名！');
    }

    /**
     * 引用404页面
     */
    public function goto_404_page($msg) {

        $root = FCPATH.'mantob/'.($this->template->mobile ? 'mobiles' : 'templates').'/'.SITE_TEMPLATE.'/404.html';

        if (APP_DIR) {
            $t = APPPATH.($this->template->mobile ? 'mobiles' : 'templates').'/'.SITE_TEMPLATE.'/404.html';
            $tpl = is_file($t) ? $t : (is_file($root) ? $root : NULL);
        } else {
            $tpl = is_file($root) ? $root : NULL;
        }

        if ($tpl) {
            header('HTTP/1.1 404 Not Found');
            $this->template->assign(array(
                'msg' => $msg,
                'meta_title' => $msg
            ));
            $this->template->display('404.html','/');
        } else {
            $this->msg($msg);
        }

        exit;
    }

    /**
     * 伪静态404页面
     */
    public function s404() {
        $this->goto_404_page('mantob无法找到对应的页面('.DR_URI.'),可能您没有配置好路由规则');
    }
}
