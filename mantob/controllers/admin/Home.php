<?php

/**
 * Omweb Website Management System
 *
 * @since		version 2.2.2
 * @author		Chunjie <chunjie@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Home extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->output->enable_profiler(FALSE);
    }
	
	/**
     * 重置
     */
    public function home() {
		$this->index();
	}
	
    /**
     * 首页
     */
    public function index() {
	
		$top = array();
		$smenu = $this->_get_menu();
		$topid = $id = 0;
		$mymenu = TRUE;
		$sitemap = $string = '';
		foreach ($smenu as $t) {
            $_left = $select = 0;
			$selurl	= '';
			$_first	= FALSE;
			$string.= '<div class="d_menu" id="D_M_'.$topid.'"'.($topid == 0 ? '' : 'style="display:none"').'>';
			$sitemap.= '<div class="d_top"><div class="d_name">'.$t['top']['name'].'</div><ul>';
			foreach ($t['data'] as $left) {
				$string.= '<div class="subnav '.($_left ? '' : 'subnav-hidden').'">';
				$string.= '<div class="subnav-title">';
				$string.= '<a href="#" class="toggle-subnav">';
                $string.= '<i class="icon-angle-'.($_left ? 'right' : 'down').'"></i>';
                $string.= '<span>'.$left['left']['name'].'</span></a>';
				$string.= '</div>';
				$string.= '<ul class="subnav-menu" style="'.($_left ? 'display:none' : '').'">';
                $_left = 1;
				foreach ($left['data'] as $link) {
					$id ++;
					if ($_first == FALSE) {
						$class	= 'dropdown';
						$select	= $id;
						$selurl	= $link['url'];
						$_first	= TRUE;
					} else {
						$class	= '';
					}
					$string.= '<li id="_MP_'.$id.'" class="'.$class.'"><a href="javascript:_MP(\''.$id.'\', \''.$link['url'].'\');" >'.$link['name'].'</a></li>';
					if ($mymenu == TRUE && $this->admin['usermenu']) {
						foreach ($this->admin['usermenu'] as $my) {
							$id ++;
							$string.= '<li id="_MP_'.$id.'"><a href="javascript:_MP(\''.$id.'\', \''.$my['url'].'\');">'.$my['name'].'</a></li>';
						}
						$mymenu = FALSE;
					}
					$sitemap.= '<li><a href="javascript:_MAP(\''.$topid.'\', \''.$id.'\', \''.$link['url'].'\');" >'.$link['name'].'</a></li>';
				}
				$string.= '</ul>';
				$string.= '</div>';
			}
			$string.= '</div>';
			$sitemap.= '</ul></div>';
			$sitemap = $topid == 0 ? '' : $sitemap;
			$t['top']['selurl'] = $selurl;
			$t['top']['select'] = $select;
			$top[$topid] = $t['top'];
			$topid ++;
		}
		
		$mysite = array();
		foreach ($this->SITE as $sid => $t) {
			if ($this->admin['adminid'] == 1
                || ($this->admin['role']['site'] && in_array($sid, $this->admin['role']['site']))) {
				$mysite[$sid] = $t['SITE_NAME'];
			}
		}
		
		$this->template->assign(array(
			'top' => $top,
			'left' => $string,
			'mysite' => $mysite,
			'sitemap' => $sitemap,
		));
        $this->template->display('index.html');
    }
	
	/**
     * 菜单缓存格式化
     */
	private function _get_menu() {
		$menu = $this->dcache->get('menu');
		$smenu = array();
		if (!$menu) {
			$this->load->model('menu_model');
			$menu = $this->menu_model->cache();
		}
		foreach ($menu as $t) {
			if (is_array($t['left'])) {
				$left = array();
				if ($t['mark'] && strpos($t['mark'], 'module-') === 0) {
					list($a, $dir) = explode('-', $t['mark']);
					if (!$this->get_cache('module-'.SITE_ID.'-'.$dir)) {
                        continue;
                    }
				}
				foreach ($t['left'] as $m) {
					$link = array();
					if (is_array($m['link'])) {
						foreach ($m['link'] as $n) {
							if ($n['mark'] && strpos($n['mark'], 'app-') === 0) {
								// 应用链接权限判断
								list($a, $dir) = explode('-', $n['mark']);
								$app = $this->get_cache('app-'.$dir);
								if ($this->admin['adminid'] > 1
                                    && !$app['setting']['admin'][$this->admin['adminid']]) {
                                    continue;
                                }
								$n['url'] = $this->duri->uri2url($n['uri']);
								$link[] = $n;
							} elseif ($this->is_auth($n['uri'])
                                && in_array($n['id'], array(74, 81, 73, 92))) {
                                // 空间开启权限判断，空间的各个属性菜单
                                if (!MEMBER_OPEN_SPACE) {
                                    continue;
                                }
                                $n['url'] = $this->duri->uri2url($n['uri']);
                                $link[] = $n;
							} elseif ($this->is_auth('member/admin/content/index')
                                && $n['mark'] && strpos($n['mark'], 'space-') === 0) {
								// 空间开启权限判断，模型的内容管理
								if (!MEMBER_OPEN_SPACE) {
                                    continue;
                                }
								$n['url'] = $this->duri->uri2url($n['uri']);
								$link[] = $n;
							} elseif (!$n['uri'] && $n['url']) {
								$link[] = $n;
							} elseif ($this->is_auth($n['uri'])) {
								// 空间开启权限判断，默认栏目不显示
								if ($n['uri'] == 'member/admin/space/init'
                                    && !MEMBER_OPEN_SPACE) {
                                    continue;
                                }
                                // 判断表单权限
                                if ($n['mark']
                                    && strpos($n['mark'], 'module-') === 0
                                    && strpos($n['uri'], 'admin/form_')
                                    && substr_count($n['mark'], '-') == 3) {
                                    list($a, $mod, $sid, $mid) = explode('-', $n['mark']);
                                    // 判断是否是当前站点
                                    if ($sid != SITE_ID) {
                                        continue;
                                    }
                                    // 判断是否具有内容管理权限
                                    if (!$this->is_auth($mod.'/admin/home/index')) {
                                        continue;
                                    }
                                }
								$n['url'] = $this->duri->uri2url($n['uri']);
								$link[] = $n;
							}
						}
					}
					if ($link) {
                        $left[] = array('left' => $m, 'data' => $link);
                    }
				}
				if ($left) {
                    $smenu[] = array('top' => $t, 'data' => $left);
                }
			}
		}
		return $smenu;
	}

    // 初始化系统
    public function init() {

        // 首次安装系统判断
        if (!$this->dcache->get('install')) {
            $this->admin_msg('本系统已经被初始化了', dr_url('home/main'), 0);
            exit;
        }

        // 搜索本地模块
        $local = @array_diff(dr_dir_map(FCPATH, 1), array('app', 'cache', 'config', 'mantob', 'member', 'space', 'player','book','fang','down','weixin','special','video'));
        $module = array();
        if ($local) {
            foreach ($local as $dir) {
                if (is_file(FCPATH.$dir.'/config/module.php')) {
                    $config = require FCPATH.$dir.'/config/module.php';
                    if ($config['key']) {
                        $module[$dir] = $config['name'];
                    }
                }
            }
            unset($local);
        }


        $this->template->assign(array(
            'step' => $this->_get_step(),
            'module' => $module,
        ));
        $this->template->display('init.html');
    }
	
	/**
     * 后台首页
     */
    public function main() {

        // 首次安装系统-跳转到欢迎界面-并安装默认模块
        if ($this->dcache->get('install')) {
            $this->admin_msg('首次安装系统，正在为您安装模块初始化数据', dr_url('home/init'), 2);
            exit;
        }
		
		$store = array();
		
		// 搜索本地模块
		$local = @array_diff(dr_dir_map(FCPATH, 1), array('app', 'cache', 'config', 'mantob', 'member', 'space', 'player')); 
		if ($local) {
			foreach ($local as $dir) {
				if (is_file(FCPATH.$dir.'/config/module.php')) {
					$config = require FCPATH.$dir.'/config/module.php';
					if ($config['key']) {
						if (isset($store[$config['key']])) {
							if (version_compare($config['version'], $store[$config['key']], '<')) {
                                $store[$config['key']] = $config['version'];
                            }
						} else {
							$store[$config['key']] = $config['version'];
						}
					}
				}
			}
		}
		
		// 搜索本地应用
		$local = dr_dir_map(FCPATH.'app/', 1); 
		if ($local) {
			foreach ($local as $dir) {
				if (is_file(FCPATH.'app/'.$dir.'/config/app.php')) {
					$config = require FCPATH.'app/'.$dir.'/config/app.php';
					if ($config['key']) {
						$store[$config['key']] = $config['version'];
					}
				}
			}
		}
		
		// 判断管理员ip状况
		$ip = '';
		$login = $this->db
					  ->where('uid', $this->uid)
					  ->order_by('logintime desc')
					  ->limit(2)
					  ->get('admin_login')
					  ->result_array();
		if ($login
            && count($login) == 2
            && $login[0]['loginip'] != $login[1]['loginip']) {
			$this->load->library('dip');
			$now = $this->dip->address($login[0]['loginip']);
			$last = $this->dip->address($login[1]['loginip']);
			if (@strpos($now, $last) === FALSE
                && @strpos($last, $now) === FALSE) {
				// Ip异常判断
				$ip = dr_lang('html-022', $login[1]['loginip'], $last, dr_url('root/log', array('uid' => $this->uid)));
			}
		}

        // 统计模块数据
        $total = array();
        $module = $this->get_module(SITE_ID);
        if ($module) {
            // 查询模块的菜单
            $top = array();
            $menu = $this->db->where('pid=0')->get('admin_menu')->result_array();
            if ($menu) {
                $i = 0;
                foreach ($menu as $t) {
                    list($a, $dir) = @explode('-', $t['mark']);
                    if ($dir && !$module[$dir] && $dir != 'weixin') {
                        continue;
                    }
                    $top[$dir] = $i;
                    $i++;
                }
            }
            // 判断审核权限
            if ($this->admin['adminid'] != 1) {
                $my = $this->_get_verify();
                $my = $my[$this->admin['adminid']];
            }
            foreach ($module as $dir => $mod) {
                // 判断模块表是否存在
                if (!$this->site[SITE_ID]
                    ->query("SHOW TABLES LIKE '%".$this->db->dbprefix(SITE_ID.'_'.$dir.'_verify')."%'")->row_array()) {
                    continue;
                }
                //
                $topid = intval($top[$dir]);
                $total[$dir] = array(
                    'name' => $mod['name'],
                    'today' => $this->_set_k_url($topid, dr_url($dir.'/home/index')),
                    'content' => $this->_set_k_url($topid, dr_url($dir.'/home/index')),
                    'content_verify' => $this->_set_k_url($topid, dr_url($dir.'/home/verify')),
                    'extend_verify' => 'javascript:;',
                    'add' => $this->_set_k_url($topid, dr_url($dir.'/home/add')),
                    'url' => $mod['url'],
                );
                if ($this->admin['adminid'] == 1) {
                    // 扩展审核数据
                    if (is_file(FCPATH.$dir.'/config/extend.main.table.php')) {
                        $total[$dir]['extend_verify'] = $this->_set_k_url($topid, dr_url($dir.'/verify/index'));
                    }
                } else {
                    if (!$my) {
                        continue;
                    }
                    if (is_file(FCPATH.$dir.'/config/extend.main.table.php')) {
                        $total[$dir]['extend_verify'] = $this->_set_k_url($topid, dr_url($dir.'/verify/index'));
                    }
                }
            }
        }

		$this->template->assign(array(
			'ip' => $ip,
            'sip' => $this->_get_server_ip(),
			'store' => dr_base64_encode(dr_array2string($store)),
            'mtotal' => $total,
			'sqlversion' => $this->db->version(),
		));
		$this->template->display('main.html');
	}
	
	/**
     * 更新全站缓存
     */
    public function cache() {
	
		$url = array(
			array(
				'url' => dr_url('site/cache', array('admin' => 1)),
				'name' => lang('006'),
			),
            array(
                'url' => dr_url('application/cache', array('admin' => 1)),
                'name' => lang('032'),
            ),
			array(
				'url' => dr_url('role/cache', array('admin' => 1)),
				'name' => lang('002'),
			),
			array(
				'url' => dr_url('menu/cache', array('admin' => 1)),
				'name' => lang('003'),
			),
			array(
				'url' => dr_url('mail/cache', array('admin' => 1)),
				'name' => lang('191'),
			),
			array(
				'url' => dr_url('verify/cache', array('admin' => 1)),
				'name' => lang('005'),
			),
			array(
				'url' => dr_url('urlrule/cache', array('admin' => 1)),
				'name' => lang('129'),
			),
            array(
                'url' => dr_url('downservers/cache', array('admin' => 1)),
                'name' => lang('341'),
            ),
			array(
				'url' => dr_url('member/menu/cache', array('admin' => 1)),
				'name' => lang('235'),
			),
			array(
				'url' => dr_url('member/model/cache', array('admin' => 1)),
				'name' => lang('241'),
			),
			array(
				'url' => dr_url('member/setting/cache', array('admin' => 1)),
				'name' => lang('010'),
			),
		);
		
		// 模块缓存
		$module = $this->db
					   ->select('disabled,dirname')
					   ->get('module')
					   ->result_array();
		if ($module) {
			foreach ($module as $mod) {
				if ($mod['disabled'] == 0) {
					$url[] = array(
						'url' => dr_url('module/cache', array('dir' => $mod['dirname'], 'admin' => 1)),
						'name' => dr_lang('009', $mod['dirname'])
					);
				}
			}
		}
		
		$i = 1;
		$count = count($this->SITE);
		foreach ($this->SITE as $sid => $t) { // 分站点缓存
			$url[] = array(
				'url' => dr_url('form/cache', array('site' => $sid, 'admin' => 1)),
				'name' => lang('248')."($i/$count)"
			);
			$url[] = array(
				'url' => dr_url('block/cache', array('site' => $sid, 'admin' => 1)),
				'name' => lang('204')."($i/$count)"
			);
			$url[] = array(
				'url' => dr_url('page/cache', array('site' => $sid, 'admin' => 1)),
				'name' => lang('164')."($i/$count)"
			);
			$url[] = array(
				'url' => dr_url('linkage/cache', array('site' => $sid, 'admin' => 1)),
				'name' => lang('189')."($i/$count)"
			);
			$url[] = array(
				'url' => dr_url('navigator/cache', array('site' => $sid, 'admin' => 1)),
				'name' => lang('007')."($i/$count)"
			);
			$i ++;
		}
		
		// 应用缓存
		$app = $this->db
				    ->select('disabled,dirname')
				    ->get('application')
				    ->result_array();
        $aurl = array();
		if ($app) {
			foreach ($app as $a) {
				if ($a['disabled'] == 0) {
					$aurl[] = dr_url($a['dirname'].'/home/cache', array('admin' => 1));
				}
			}
		}
		
		$this->load->helper('file');
		if (!IS_AJAX) {
            delete_files(FCPATH.'cache/data/');
        }
		$this->dcache->set('version', MAN_VERSION); // 生成版本标识符
		
		$this->template->assign(array(
            'app' => $aurl,
			'list' => $url,
		));
		$this->template->display('cache.html');
		
    }
	
	// 清除缓存数据
	public function clear() {
		if (IS_AJAX || $this->input->get('todo')) {
			$this->_clear_data();
			if (!IS_AJAX) {
                $this->admin_msg(lang('html-572'), '', 1);
            }
		} else {
			$this->admin_msg('Clear ... ', dr_url('home/clear', array('todo' => 1)), 2);
		}
	}

    // 域名检查
    public function domain() {
        $ip = $this->_get_server_ip();
        $domain = $this->input->get('domain');
        if (gethostbyname($domain) != $ip) {
            exit(dr_lang('html-731', $domain, $ip));
        }
        exit('');
    }
	
	// 清除缓存数据
	private function _clear_data() {
	
		// 删除全部缓存文件
		$this->load->helper('file');
		delete_files(FCPATH.'cache/sql/');
		delete_files(FCPATH.'cache/file/');
        delete_files(FCPATH.'cache/page/');
        delete_files(FCPATH.'cache/index/');
		delete_files(FCPATH.'cache/attach/');
		delete_files(FCPATH.'cache/templates/');
		delete_files(FCPATH.'member/uploadfile/thumb/');
		
		// 删除memcache缓存
		if (SYS_MEMCACHE && $this->cache->memcached->is_supported()) {
            $this->cache->memcached->clean();
        }
		
		// 模块缓存
		$module = $this->db
					   ->select('disabled,dirname')
					   ->get('module')
					   ->result_array();
		if ($module) {
			foreach ($module as $mod) {
				$site = dr_string2array($mod['site']);
				if ($site[SITE_ID]) {
                    $this->site[SITE_ID]
                         ->where('inputtime<>', 0)
                         ->delete(SITE_ID.'_'.$mod['dirname'].'_search');
                }
			}
		}
		
	}

    //
    private function _set_k_url($id, $url) {
        return 'javascript:parent._MAP(\''.$id.'\', \'0\', \''.$url.'\');';
    }

    // 统计数据
    public function mtotal() {

        // 统计模块数据
        $total = $this->get_cache_data('admin_mtotal');
        $module = $this->get_module(SITE_ID);
        if (!$module) {
            exit;
        }

        if (!$total) {
            // 查询模块的菜单
            $top = array();
            $menu = $this->db->where('pid=0')->get('admin_menu')->result_array();
            if ($menu) {
                $i = 0;
                foreach ($menu as $t) {
                    list($a, $dir) = @explode('-', $t['mark']);
                    if ($dir && !$module[$dir] && $dir != 'weixin') {
                        continue;
                    }
                    $top[$dir] = $i;
                    $i++;
                }
            }
            // 判断审核权限
            if ($this->admin['adminid'] != 1) {
                $my = $this->_get_verify();
                $my = $my[$this->admin['adminid']];
            }
            foreach ($module as $dir => $mod) {
                // 判断模块表是否存在
                if (!$this->site[SITE_ID]
                    ->query("SHOW TABLES LIKE '%".$this->db->dbprefix(SITE_ID.'_'.$dir.'_verify')."%'")->row_array()) {
                    continue;
                }
                //
                $topid = intval($top[$dir]);
                $total[$dir] = array(
                    'today' => $this->site[SITE_ID]->where('DATEDIFF(from_unixtime(inputtime),now())=0')->count_all_results(SITE_ID.'_'.$dir.'_index'),
                    'content' => $this->site[SITE_ID]->where('status=9')->count_all_results(SITE_ID.'_'.$dir.'_index'),
                    'content_verify' => 0,
                    'extend_verify' => 0,
                );
                if ($this->admin['adminid'] == 1) {
                    // 管理员显示审核全部流程数据
                    $total[$dir]['content_verify'] = $this->site[SITE_ID]->where('status<>0')->count_all_results(SITE_ID.'_'.$dir.'_verify');
                    // 扩展审核数据
                    if (is_file(FCPATH.$dir.'/config/extend.main.table.php')) {
                        $total[$dir]['extend_verify'] = $this->site[SITE_ID]->where('status<>0')->count_all_results(SITE_ID.'_'.$dir.'_extend_verify');
                    }
                } else {
                    if (!$my) {
                        continue;
                    }
                    $total[$dir]['content_verify'] = $this->site[SITE_ID]->where_in('status', $my)->count_all_results(SITE_ID.'_'.$dir.'_verify');
                    if (is_file(FCPATH.$dir.'/config/extend.main.table.php')) {
                        $total[$dir]['extend_verify'] = $this->site[SITE_ID]->where_in('status', $my)->count_all_results(SITE_ID.'_'.$dir.'_extend_verify');
                    }
                }
            }
            $this->set_cache_data('admin_mtotal', $total, 600);
        }

        if (!$total) {
            exit;
        }

        // AJAX输出
        foreach ($total as $dir => $t) {
            echo '$("#'.$dir.'_today").html('.$t['today'].');';
            echo '$("#'.$dir.'_content").html('.$t['content'].');';
            echo '$("#'.$dir.'_content_verify").html('.$t['content_verify'].');';
            echo '$("#'.$dir.'_extend_verify").html('.$t['extend_verify'].');';
        }

    }
}