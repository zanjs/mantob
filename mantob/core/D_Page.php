<?php

 /**
 * mantob Website Management System
 *
 * @since		version 2.0.1
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class D_Page extends M_Controller {

    private $_id;
	private $field;
	private $nocache;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		if (IS_ADMIN) {
            $menu = array(
                lang('152') => APP_DIR.'/admin/page/index',
                lang('add') => APP_DIR.'/admin/page/add',
                lang('html-629') => APP_DIR.'/admin/field/index/rname/page/rid/'.SITE_ID,
            );
            if (APP_DIR) {
                unset($menu[lang('html-629')]);
            }
			$this->template->assign('menu', $this->get_menu($menu));
        }
        $this->field = array(
            'name' => array(
                'name' => IS_ADMIN ? lang('139') : '',
                'ismain' => 1,
                'fieldname' => 'name',
                'fieldtype' => 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 150,
                    ),
                    'validate' => array(
                        'required' => 1,
                        'formattr' => 'onblur="d_topinyin(\'dirname\',\'name\');"',
                    )
                )
            ),
            'dirname' => array(
                'name' => IS_ADMIN ? lang('140') : '',
                'ismain' => 1,
                'fieldname' => 'dirname',
                'fieldtype' => 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 150,
                    ),
                    'validate' => array(
                        'required' => 1,
                    )
                )
            ),
            'thumb' => array(
                'name' => IS_ADMIN ? lang('141') : '',
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
            'keywords' => array(
                'name' => IS_ADMIN ? lang('143') : '',
                'ismain' => 1,
                'fieldname' => 'keywords',
                'fieldtype'	=> 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 400
                    )
                )
            ),
            'title' => array(
                'name' => IS_ADMIN ? lang('142') : '',
                'ismain' => 1,
                'fieldname' => 'title',
                'fieldtype'	=> 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 400
                    )
                )
            ),
            'description' => array(
                'name' => IS_ADMIN ? lang('144') : '',
                'ismain' => 1,
                'fieldname' => 'description',
                'fieldtype'	=> 'Textarea',
                'setting' => array(
                    'option' => array(
                        'width' => 500,
                        'height' => 60
                    )
                )
            ),
            'template' => array(
                'name' => IS_ADMIN ? lang('147') : '',
                'ismain' => 1,
                'fieldname' => 'template',
                'fieldtype'	=> 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 200,
                        'value' => 'page.html'
                    )
                )
            ),
            'urllink' => array(
                'name' => IS_ADMIN ? lang('148') : '',
                'ismain' => 1,
                'fieldname' => 'urllink',
                'fieldtype'	=> 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 400,
                        'value' => ''
                    )
                )
            ),
            'urlrule' => array(
                'name' => IS_ADMIN ? lang('149') : '',
                'ismain' => 1,
                'fieldname' => 'urlrule',
                'fieldtype'	=> 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 300
                    )
                )
            ),
            'show' => array(
                'name' => IS_ADMIN ? lang('html-357') : '',
                'ismain' => 1,
                'fieldname' => 'show',
                'fieldtype'	=> 'Radio',
                'setting' => array(
                    'option' => array(
                        'value' => '1',
                        'options' => (IS_ADMIN ? lang('yes') : 'Yes').'|1'.PHP_EOL.(IS_ADMIN ? lang('no') : 'No').'|0',
                    )
                )
            ),
            'getchild' => array(
                'name' => IS_ADMIN ? lang('order') : '',
                'ismain' => 1,
                'fieldtype'	=> 'Radio',
                'fieldname' => 'getchild',
                'setting' => array(
                    'option' => array(
                        'value' => '1',
                        'options' => (IS_ADMIN ? lang('yes') : 'Yes').'|1'.PHP_EOL.(IS_ADMIN ? lang('no') : 'No').'|0',
                    )
                )
            ),
        );
		$this->load->model('page_model');
    }

    //
    protected function _get_page($id, $dir, $pid) {

        if (!$id && !$dir) {
            $this->goto_404_page(lang('m-195'));
        }

        // 单页缓存
        $PAGE = $this->dcache->get('page-'.SITE_ID);
        $page = APP_DIR ? $PAGE['data'][APP_DIR] : $PAGE['data']['index'];

        // 获取单页ID
        $id = !$id && $dir ? $PAGE['dir'][$dir] : $id;

        // 无法通过目录找到栏目时，尝试多及目录
        if (!$id && $dir && $page) {
            foreach ($page as $t) {
                if ($t['urlrule']) {
                    $rule = $this->get_cache('urlrule', $t['urlrule']);
                    if ($rule['value']['catjoin'] && strpos($dir, $rule['value']['catjoin'])) {
                        $dir = trim(strchr($dir, $rule['value']['catjoin']), $rule['value']['catjoin']);
                        if (isset($PAGE['dir'][$dir])) {
                            $id = $PAGE['dir'][$dir];
                            break;
                        }
                    }
                }
            }
        }
        unset($PAGE);

        // 当前单页的数据
        $data = $page[$id];
        if (!$data || !$data['show']) {
            $this->goto_404_page(dr_lang('m-196', $id));
        }

        // 单页验证是否存在子栏目
        if ($data['child'] && $data['getchild']) {
            $temp = explode(',', $data['childids']);
            if (isset($temp[1]) && $page[$temp[1]]) {
                $id = $temp[1];
                $data = $page[$id];
            }
        }

        $my = $this->dcache->get('page-field-'.SITE_ID);
        $my = $my ? array_merge($this->field, $my) : $this->field;
        $data = $this->field_format_value($my, $data, $pid); // 格式化输出自定义字段
        $join = SITE_SEOJOIN ? SITE_SEOJOIN : '_';
        $title = $data['title'] ? $data['title'] : dr_get_page_pname($id, $join);
        if (isset($data['content_title']) && $data['content_title']) {
            $title = $data['content_title'].$join.$title;
        }

        // 栏目下级或者同级栏目
        $related = $parent = array();
        if ($data['pid']) {
            foreach ($page as $t) {
                if (!$t['show']) {
                    continue;
                }
                if ($t['pid'] == $data['pid']) {
                    $related[] = $t;
                    if ($data['child']) {
                        $parent = $data;
                    } else {
                        $parent = $page[$t['pid']];
                    }
                }
            }
        } elseif ($data['child']) {
            $parent = $data;
            foreach ($page as $t) {
                if (!$t['show']) {
                    continue;
                }
                if ($t['pid'] == $data['id']) {
                    $related[] = $t;
                }
            }
        } else {
            $parent = $data;
            if ($page) {
                foreach ($page as $t) {
                    if (!$t['show']) {
                        continue;
                    }
                    $related[] = $t;
                }
            }
        }

        // 格式化配置
        $data['setting'] = dr_string2array($data['setting']);

        // 存储id和缓存参数
        $this->_id = $data['id'];
        $this->nocache = (int)$data['setting']['nocache'];

        $this->template->assign($data);
        $this->template->assign(array(
            'parent' => $parent,
            'related' => $related,
            'urlrule' => $this->mobile ? dr_mobile_page_url($data['module'], $data['id'], '{page}') : dr_page_url($data, '{page}'),
            'meta_title' => $title,
            'meta_keywords' => trim($data['keywords'].','.SITE_KEYWORDS, ','),
            'meta_description' => $data['description']
        ));
        $this->template->display($data['template'] ? $data['template'] : 'page.html');
    }
	
	/**
	 * 单网页输出
	 */
	protected function _page() {

        ob_start();
        $this->_get_page(
            (int)$this->input->get('id'),
            $this->input->get('dir'),
            max(1, (int)$this->input->get('page'))
        );
        $html = ob_get_clean();

        // 不被缓存
        if ($this->nocache) {
            echo $html;exit;
        }

        // 获取url对应表
        $file = FCPATH.'cache/page/url.php';
        $urls = @unserialize(@file_get_contents($file));
        $urls = $urls ? $urls : array();

        // 将当前文件存储到url表
        $urls[SYS_URL] = FCPATH.'cache/page/'.SITE_ID.'-'.$this->_id.'-'.md5(SYS_URL).'.html';
        file_put_contents($urls[SYS_URL], $html, LOCK_EX);

        // 存储url表
        file_put_contents($file, serialize($urls), LOCK_EX);
        echo $html;exit;
	}

	/*
	 * 删除
	 */
	protected function admin_delete($ids) {
	
		if (!$ids) {
            return NULL;
        }
		
		// 筛选栏目id
		$catid = '';
		foreach ($ids as $id) {
			$data = $this->page_model
						 ->link
						 ->select('childids')
						 ->where('id', $id)
						 ->limit(1)
						 ->get($this->page_model->tablename)
						 ->row_array();
			$catid.= ','.$data['childids'];
		}
		$catid = explode(',', $catid);
		$catid = array_flip(array_flip($catid));
		$this->load->model('attachment_model');
		
		// 逐一删除
		foreach ($catid as $id) {
			// 删除主表
			$this->page_model
				 ->link
				 ->where('id', $id)
				 ->delete($this->page_model->tablename);
			// 删除附件
			$this->attachment_model->delete_for_table($this->page_model->tablename.'-'.$id);
            // 删除导航数据
            $this->page_model
                 ->link
                 ->where('mark', 'page-'.$id)
                 ->delete(SITE_ID.'_navigator');
		}
		
		$this->page_model->cache(SITE_ID);
	}
	
    /**
     * 首页
     */
    protected function admin_index() {
		
		if (IS_POST) {
			
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
			
			if ($this->input->post('action') == 'order') {
				$data = $this->input->post('data');
				foreach ($ids as $id) {
					$this->page_model->link->where('id', $id)->update($this->page_model->tablename, $data[$id]);
				}
				$this->page_model->cache(SITE_ID);
				exit(dr_json(1, lang('000')));
			} else {
				if (!$this->is_auth(APP_DIR.'/admin/page/index')) {
                    exit(dr_json(0, lang('160')));
                }
				$this->admin_delete($ids);
				exit(dr_json(1, lang('000')));
			}
		}
		
		$this->page_model->repair();
		$this->load->library('dtree');
		$this->dtree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$this->dtree->nbsp = '&nbsp;&nbsp;&nbsp;';
		
		$tree = array();
		$data = $this->page_model->get_data();
		
		if ($data) {
			foreach($data as $t) {
				$t['option'] = '<a href="'.$t['url'].'" target="_blank">'.lang('go').'</a>&nbsp;&nbsp;&nbsp;';
				if ($this->is_auth(APP_DIR.'/admin/page/add')) {
					$t['option'].= '<a href='.dr_url(APP_DIR.'/page/add', array('id' => $t['id'])).'>'.lang('252').'</a>&nbsp;&nbsp;&nbsp;';
				}
				if ($this->is_auth(APP_DIR.'/admin/page/edit')) {
					$t['option'].= '<a href='.dr_url(APP_DIR.'/page/edit', array('id' => $t['id'])).'>'.lang('253').'</a>&nbsp;&nbsp;&nbsp;';
				}
                $t['cache'] = $t['setting']['nocache'] ? '<img src="/mantob/statics/images/0.gif">' : '<img src="/mantob/statics/images/1.gif">';
				$tree[$t['id']] = $t;
			}
		}
		
		$str = "<tr class='\$class'>";
		$str.= "<td align='right'><input name='ids[]' type='checkbox' class='dr_select' value='\$id' />&nbsp;</td>";
		$str.= "<td align='left'><input class='input-text displayorder' type='text' name='data[\$id][displayorder]' value='\$displayorder' /></td>";
		$str.= "<td align='left'>\$id</td>";
		if ($this->is_auth(APP_DIR.'/admin/page/edit')) {
			$str.= "<td>\$spacer<a href='".dr_url(APP_DIR.'/page/edit')."&id=\$id'>\$name</a>  \$parent</td>";
		} else {
			$str.= "<td>\$spacer\$name  \$parent</td>";
		}
		$str.= "<td align='left'>\$dirname</td>";
        $str.= "<td align='center'>\$cache</td>";
		$str.= "<td align='left'>\$option</td>";
		$str.= "</tr>";
		$this->dtree->init($tree);
		
		$this->template->assign(array(
			'list' => $this->dtree->get_tree(0, $str),
            'page' => (int)$this->input->get('page')
		));
		$this->template->display('page_index.html');
    }
	
	/**
     * 添加
     */
    protected function admin_add() {
		
		$pid = (int)$this->input->get('id');
		$data = $error = $result = NULL;
        $field = $this->dcache->get('page-field-'.SITE_ID);

		if (IS_POST) {
            $my = $field ? array_merge($this->field, $field) : $this->field;
			$data = $this->validate_filter($my);
            if (isset($data['error'])) {
                $error = $data;
                $data = $this->input->post('data');
            } else {
                $data[1]['pid'] = $this->input->post('pid');
                $data[1]['urlrule'] = $this->input->post('urlrule');
                $page = $this->page_model->add($data[1]);
                if (is_numeric($page)) {
                    // 更新至网站导航
                    $this->load->model('navigator_model');
                    $this->navigator_model->syn_value($data[1], $page);
                    $this->page_model->cache(SITE_ID);
                    $this->attachment_handle($this->uid, $this->page_model->tablename.'-'.$page, $my);
                    if ($this->input->post('action') == 'back') {
                        $this->admin_msg(lang('000'), dr_url(APP_DIR.'/page/index'), 1, 0);
                    } else {
                        $pid = $data[1]['pid'];
                        unset($data);
                        $result = lang('000');
                    }
                } else {
                    $data = $this->input->post('data');
                    $error = array('msg' => $page);
                }
            }
		} else {
            // 调用父属性
            if ($pid && ($row = $this->db->where('id', $pid)->get(SITE_ID.'_page')->row_array())) {
                $data['urlrule'] = $row['urlrule'];
                $data['setting'] = dr_string2array($row['setting']);
                $data['template'] = $row['template'];
            }
        }
		
		$this->template->assign(array(
			'page' => 0,
			'data' => $data,
			'error' => $error,
			'field' => $this->field,
			'result' => $result,
            'select' => $this->_select($this->page_model->get_data(), $pid, 'name=\'pid\'', lang('150')),
            'myfield' => $this->field_input($field, $data, FALSE),
		));
		$this->template->display('page_add.html');
	}
	
	/**
     * 修改
     */
    protected function admin_edit() {
	
		$id = (int)$this->input->get('id');
		$data = $this->page_model->get($id);
		if (!$data)	{
            $this->admin_msg(lang('019'));
        }

        $error = $result = NULL;
        $field = $this->dcache->get('page-field-'.SITE_ID);
        $data['setting'] = dr_string2array($data['setting']);

		if (IS_POST) {
            $my = $field ? array_merge($this->field, $field) : $this->field;
            $post = $this->validate_filter($my);
            if (isset($post['error'])) {
                $error = $post;
            } else {
                $post[1]['pid'] = $this->input->post('pid');
                $post[1]['pid'] = $post[1]['pid'] == $id ? $data['pid'] : $post[1]['pid'];
                $post[1]['urlrule'] = $this->input->post('urlrule');
                $post[1]['displayorder'] = $data['displayorder'];
                $page = $this->page_model->edit($id, $post[1]);
                if (is_numeric($page)) {
                    // 更新至网站导航
                    $this->load->model('navigator_model');
                    $this->navigator_model->syn_value($post[1], $page);
                    $this->page_model->syn($this->input->post('synid'), $post[1]['urlrule']);
                    $this->attachment_handle($this->uid, $this->page_model->tablename.'-'.$page, $field);
                    $this->page_model->cache(SITE_ID);
                    $this->admin_msg(lang('000'), dr_url(APP_DIR.'/page/index'), 1, 0);
                } else {
                    $error = array('msg' => $page);
                }
            }
		}

		$page = $this->page_model->get_data();
		$this->template->assign(array(
			'id' => $id,
			'data' => $data,
			'page' => (int)$this->input->post('page'),
            'error' => $error,
			'field' => $this->field,
			'result' => $result,
            'select' => $this->_select($page, $data['pid'], 'name=\'pid\'', lang('150')),
            'myfile' => is_file(APPPATH.'templates/admin/page_'.SITE_ID.'_'.$id.'.html') ? 'page_'.SITE_ID.'_'.$id.'.html' : '',
            'myfield' => $this->field_input($field, $data, FALSE),
			'select_syn' => $this->_select($page, 0, 'id="dr_synid" name=\'synid[]\' multiple style="height:200px;"', '')
		));
		$this->template->display('page_add.html');
	}

	
	/**
     * 缓存
     */
    protected function admin_cache() {

        $this->page_model->cache(isset($_GET['site']) ? (int)$_GET['site'] : SITE_ID);

        $this->load->helper('file');
        delete_files(FCPATH.'cache/page/');

        (int)$_GET['admin'] or $this->admin_msg(lang('000'), isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', 1);
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
		
		if ($default) {
            $string.= "<option value='0'>$default</option>";
        }
		
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