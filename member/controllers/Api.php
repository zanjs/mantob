<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');
	
/**
 * Api调用类
 * Omweb Website Management System
 *
 * @since		version 2.3.3
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
 
class Api extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    // 注销授权，进入会员中心
    public function member() {
        $this->session->set_userdata('member_auth_uid', 0);
        redirect(MEMBER_URL, 'refresh');
    }

    // 登录授权
    public function ologin() {

        $uid = (int)$this->input->get('uid');

        // 注销上一个会员
        if ($this->session->userdata('member_auth_uid')) {
            $this->session->set_userdata('member_auth_uid', 0);
            redirect(MEMBER_URL.'index.php?c=api&m=ologin&uid='.$uid, 'refresh');
        }

        // 非管理员无权操作
        if ($this->member['adminid'] != 1) {
            $this->admin_msg($this->member['username'].'：'.lang('160'));
        }

        if ($this->uid != $uid) {
            $this->session->set_userdata('member_auth_uid', $uid);
        }
        $this->template->assign('meta_name', lang('m-002'));
        $this->admin_msg(lang('m-123'), MEMBER_URL, 2);
    }
	
	/**
     * 内容关联字段数据读取
     */
	public function related() {
		
		// 强制将模板设置为后台
		$this->template->admin();
		
		// 登陆判断
		if (!$this->uid) {
            $this->admin_msg(lang('m-039'));
        }
		
		// 参数判断
		$dirname = $this->input->get('module');
		if (!$dirname) {
            $this->admin_msg(lang('m-101'));
        }

        // 站点选择
        $site = (int)$_GET['site'];
        $site = $site ? $site : SITE_ID;

		// 模块缓存判断
		$module = $this->get_cache('module-'.$site.'-'.$dirname);
		if (!$module) {
            $this->admin_msg(dr_lang('m-102', $dirname));
        }
		
		// 加载后台用到的语言包
		$this->lang->load('admin');
		$this->lang->load('template');
		
		$db = $this->site[$site];
		$field = $module['field'];
		$category = $module['category'];
		
		$field['id'] = array(
			'name' => 'Id',
			'ismain' => 1,
			'fieldtype' => 'Text',
			'fieldname' => 'id',
		);
		
		if ($this->member['adminid']) {
			$field['author'] = array(
				'name' => lang('101'),
				'ismain' => 1,
				'fieldtype' => 'Text',
				'fieldname' => 'author',
			);
		} else {
			$db->where('uid', $this->uid);
		}
		
		if (IS_POST) {
			$data = $this->input->post('data');
			$catid = (int)$this->input->post('catid');
			if ($catid) {
                $db->where_in('catid', $category[$catid]['catids']);
            }
			if (isset($data['keyword']) && $data['keyword']
                && $data['field'] && isset($field[$data['field']])) {
				if ($data['field'] == 'id') {
					$id = array();
					$ids = explode(',', $data['keyword']);
					foreach ($ids as $i) {
						$id[] = (int)$i;
					}
					$db->where_in('id', $id);
				} else {
					$db->like($data['field'], urldecode($data['keyword']));
				}
			}
		}
		
		sort($field);
		$list = $db->limit(30)
				   ->order_by('updatetime DESC')
				   ->select('id,title,updatetime,url')
				   ->get($site.'_'.$dirname)
				   ->result_array();
		
		// 栏目选择
		$tree = array();
		$select = '<select name="catid">';
		$select.= "<option value='0'> -- </option>";
		if (is_array($category)) {
			foreach($category as $t) {
				$t['selected'] = $catid == $t['id'] ? 'selected' : '';
				$t['html_disabled'] = 0;
				unset($t['permission'], $t['setting'], $t['catids'], $t['url']);
				$tree[$t['id']] = $t;
			}
		}
		$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
		$str2 = "<optgroup label='\$spacer \$name'></optgroup>";
		$this->load->library('dtree');
		$this->dtree->init($tree);
		$select.= $this->dtree->get_tree_category(0, $str, $str2);
		$select.= '</select>';
		
		$this->template->assign(array(
			'list' => $list,
			'param' => $data,
			'field' => $field,
			'select' => $select,
		));
		$this->template->display('related.html', 'admin');
	}
	
	/**
     * 检查新提醒
     */
	public function notice() {
		if ($this->uid) {
			$value = $this->db->where('uid', (int)$this->uid)->count_all_results('member_new_notice');
		} else {
			$value = 0;
		}
		$callback = isset($_GET['callback']) ? $_GET['callback'] : 'callback';
		exit($callback . '(' . json_encode(array('status' => $value)) . ')');
	}
	
	/**
     * 检测会员在线情况
     */
	public function online() {
		
		$uid = (int)$this->input->get('uid');
		$type = (int)$this->input->get('type');
		$icon = MEMBER_THEME_PATH.'images/';
		
		if ($this->db->where('user_id', $uid)->count_all_results('member_session')) {
			$icon.= 'web'.$type.'.gif';
			$online = 1;
		} else {
			$icon.= 'web'.$type.'-off.gif';
			$online = 0;
		}
		
		$member = $this->db
					   ->select('username')
					   ->where('uid', $uid)
					   ->limit(1)
					   ->get('member')
					   ->row_array();
		
		$string = '<img src="'
            .$icon.'" align="absmiddle" style="cursor:pointer" onclick="dr_chat(this)" username="'
            .$member['username'].'" uid='.$uid.' online='.$online.'>';
		
		exit("document.write('$string');");
	}

	/**
     * 站点间的同步登录
     */
	public function synlogin() {
		$this->api_synlogin();
	}
	
	/**
     * 站点间的同步退出
     */
	public function synlogout() {
		$this->api_synlogout();
	}
	
	/**
     * 自定义信息JS调用
     */
	public function template() {
		$this->api_template();
	}
	
	/**
	 * 伪静态测试
	 */
	public function test() {
		header('Content-Type: text/html; charset=utf-8');
		echo '服务器支持伪静态';
	}
	
	/**
	 * 联动栏目分类调用
	 */
	public function category() {
	    
		$dir = $this->input->get('module');
	    $pid = (int)$this->input->get('parent_id');
	    $json = array();
		$category = $this->get_cache('module-'.SITE_ID.'-'.$dir, 'category');
		
		foreach ($category as $k => $v) {
			if ($v['pid'] == $pid) {
				if (!$v['child'] && !$v['permission'][$this->markrule]['add']) {
                    continue;
                }
				$json[] = array(
					'region_id' => $v['id'],
					'region_name' => $v['name'],
					'region_child' => $v['child']
				);
			}
		}
		
		echo json_encode($json);	
	}
	
	/**
	 * 联动菜单调用
	 */
	public function linkage() {

	    $pid = (int)$this->input->get('parent_id');
        $json = array();
	    $code = $this->input->get('code');
		$linkage = $this->get_cache('linkage-'.SITE_ID.'-'.$code);

		foreach ($linkage as $v) {
			if ($v['pid'] == $pid) {
				$json[] = array('region_id' => $v['id'], 'region_name' => $v['name']);
			}
		}

		echo json_encode($json);	
	}
	
	/**
	 * 会员登录信息JS调用
	 */
	public function userinfo() {
	    ob_start();
		$this->template->display('api.html');
		$html = ob_get_contents();
		ob_clean();
		$html = addslashes(str_replace(array("\r", "\n", "\t", chr(13)), array('', '', '', ''), $html));
	    echo 'document.write("'.$html.'");';
	}
	
	/**
     * Ajax调用字段属性表单
	 *
	 * @return void
     */
	public function field() {
	
		$id = (int)$this->input->post('id');
		$type = $this->input->post('type');
		
		$this->load->model('field_model');
		$this->relatedid = $this->input->post('relatedid');
		$this->relatedname = $this->input->post('relatedname');
		
		$data = $this->field_model->get($id);
		$fields = $this->field_model->get_data();
        $related = $this->input->post('relatedname');
		if ($data) {
			$value = dr_string2array($data['setting']);
			$value = $value['option'];
		} else {
			$value = array();
		}

		$this->lang->load('admin');
		$this->lang->load('template');
		$this->load->library('Dfield', array($this->input->post('module')));

        define('TEXT_UNIQUE', $related == 'module' ? 1 : 0);
		$return	= $this->dfield->option($type, $value, $fields);
		
		if ($return !== 0) {
            echo $return;
        }
	}
	
	/**
     * 百度地图调用
	 *
	 * @return void
     */
	public function baidumap() {

		$list = $this->input->get('city') ? explode(',', urldecode($this->input->get('city'))) : NULL;
		$city = isset($list[0]) ? $list[0] : '';
		$value = $this->input->get('value');
		$value = strlen($value) > 10 ? $value : '';

		$this->template->assign(array(
			'city' => $city,
			'value' => $value,
			'list' => $list,
			'name' => $this->input->get('name'),
			'level'	=> (int)$this->input->get('level'),
			'width' => $this->input->get('width'),
			'height' => $this->input->get('height') - 30,
		));
		$this->template->display('baidumap.html', 'admin');

	}
	
	/**
     * 文件上传
	 *
	 * @return void
     */
	public function upload() {

        $this->load->model('attachment_model');
		$code = str_replace(' ', '+', $this->input->get('code'));
		list($size, $ext, $path) = explode('|', dr_authcode($code, 'DECODE'));

        $uid = $this->uid;
        if ($this->session->userdata('member_auth_uid')) {
            // 附件上传时采用后台登陆会员
            $uid = $this->member_model->member_uid(1);
        }
        $notused = $this->attachment_model->get_unused($uid, $ext);

		$this->template->assign(array(
			'ext' => str_replace(',', '|', $ext),
			'code' => $code,
			'page' => $notused ? 3 : 0,
			'size' => (int)$size * 1024,
			'name' => $this->input->get('name'),
			'types' => '*.'.str_replace(',', ';*.', $ext),
			'fileid' => $this->input->get('filename'),
			'fcount' => (int)$this->input->get('count'),
            'notused' => $notused,
            'session' => dr_authcode($uid, 'ENCODE'),
		));
		$this->template->display('upload.html', 'admin');
	}


    // sns上传图片
    public function sns_upload() {

        $uid = (int)dr_authcode(str_replace(' ', '+', $this->input->post('PHPSESSID')), 'DECODE');
        if (!$uid) {
            exit(json_encode(array('status' => 0, 'data' => lang('m-142'))));
        }

        // 根据页面传入的session来获取当前登录uid，未获取到uid时提示游客无法上传
        $this->member = $this->member_model->get_member($uid); // 获取会员信息

        // 游客不允许上传，未获取到会员信息时提示游客无法上传
        if (!$this->member) {
            exit(json_encode(array('status' => 0, 'data' => lang('m-142'))));
        }
        // 会员组权限
        $member_rule = $this->get_cache('member', 'setting', 'permission', $this->member['mark']);

        // 是否允许上传附件
        if (!$this->member['adminid'] && !$member_rule['is_upload']) {
            exit(json_encode(array('status' => 0, 'data' => lang('m-143'))));
        }

        // 附件总大小判断
        if (!$this->member['adminid'] && $member_rule['attachsize']) {
            $data = $this->db
                ->select_sum('filesize')
                ->where('uid', $uid)
                ->get('attachment')
                ->row_array();
            $filesize = (int)$data['filesize'];
            if ($filesize > $member_rule['attachsize'] * 1024 * 1024) {
                exit(json_encode(array('status' => 0, 'data' => dr_lang('m-147', $member_rule['attachsize'].'MB', dr_format_file_size($filesize)))));
            }
        }

        $path = FCPATH.'member/uploadfile/'.date('Ym', SYS_TIME).'/';
        if (!is_dir($path)) {
            dr_mkdirs($path);
        }
        $this->load->library('upload', array(
            'max_size' => 10240,
            'overwrite' => FALSE,
            'file_name' => substr(md5(time()), rand(0, 20), 10),
            'upload_path' => $path,
            'allowed_types' => 'gif|jpg|png',
            'file_ext_tolower' => TRUE,
        ));
        if ($this->upload->do_upload('Filedata')) {
            $info = $this->upload->data();
            $this->load->model('attachment_model');
            $result = $this->attachment_model->upload($uid, $info);
            if (!is_array($result)) {
                exit(json_encode(array('status' => 0, 'data' => $result)));
            }
            list($id, $file, $_ext) = $result;
            echo json_encode(array(
                'status' => 1,
                'data' => array(
                    'src' => dr_file($file),
                    'extension' => $_ext,
                    'attach_id' => $id
                )
            ));exit;
        } else {
            exit(json_encode(array('status' => 0, 'data' => $this->upload->display_errors('', ''))));
        }
    }

	/**
     * 文件上传处理
	 *
	 * @return void
     */
	public function swfupload() {

		$uid = (int)dr_authcode(str_replace(' ', '+', $this->input->post('session')), 'DECODE');
		if (!$uid) {
            exit('0,'.lang('m-142'));
        }

        // 根据页面传入的session来获取当前登录uid，未获取到uid时提示游客无法上传
		$this->member = $this->member_model->get_member($uid); // 获取会员信息

        // 游客不允许上传，未获取到会员信息时提示游客无法上传
		if (!$this->member) {
            exit('0,'.lang('m-142'));
        }
        // 会员组权限
		$member_rule = $this->get_cache('member', 'setting', 'permission', $this->member['mark']);

        // 是否允许上传附件
		if (!$this->member['adminid'] && !$member_rule['is_upload']) {
            exit('0,'.lang('m-143'));
        }

        // 附件总大小判断
		if (!$this->member['adminid'] && $member_rule['attachsize']) {
			$data = $this->db
						 ->select_sum('filesize')
						 ->where('uid', $uid)
						 ->get('attachment')
						 ->row_array();
			$filesize = (int)$data['filesize'];
			if ($filesize > $member_rule['attachsize'] * 1024 * 1024) {
                exit('0,'.dr_lang('m-147', $member_rule['attachsize'].'MB', dr_format_file_size($filesize)));
            }
		}

		if (IS_POST) {
			$code = str_replace(' ', '+', $this->input->post('code'));
			list($size, $ext, $path) = explode('|', dr_authcode($code, 'DECODE'));
			if ($path) {
				$path = FCPATH.'member/uploadfile/'.$path.'/';
			} else {
				$path = FCPATH.'member/uploadfile/'.date('Ym', SYS_TIME).'/';
			}
			if (!is_dir($path)) {
                dr_mkdirs($path);
            }
			$this->load->library('upload', array(
				'max_size' => (int)$size * 1024,
				'overwrite' => FALSE,
				'file_name' => substr(md5(time()), rand(0, 20), 10),
				'upload_path' => $path,
				'allowed_types' => str_replace(',', '|', $ext),
				'file_ext_tolower' => TRUE,
			));
			if ($this->upload->do_upload('Filedata')) {
				$info = $this->upload->data();
				$this->load->model('attachment_model');
				$result = $this->attachment_model->upload($uid, $info);
				if (!is_array($result)) {
                    exit('0,'.$result);
                }
				list($id, $file, $_ext) = $result;
				$icon = is_file(FCPATH.'mantob/statics/images/ext/'.$_ext.'.gif') ? SITE_URL.'mantob/statics/images/ext/'.$_ext.'.gif' : SITE_URL.'mantob/statics/images/ext/blank.gif';
				//唯一ID,文件全路径,图标,文件名称,文件大小,扩展名
				exit($id.','.dr_file($file).','.$icon.','.str_replace(array('|', '.'.$_ext), '', $info['client_name']).','.dr_format_file_size($info['file_size'] * 1024).','.$_ext);
			} else {
				exit('0,'.$this->upload->display_errors('', ''));
			}
        }
	}
	
	/**
	 * 删除附件
	 */
	public function swfdelete() {

		if (!$this->uid) {
            return NULL;
        }

        $id = (int)$this->input->post('id');
		$this->load->model('attachment_model');
		// 删除未使用
		$data = $this->db
					 ->where('id', $id)
					 ->where('uid', $this->uid)
					 ->get('attachment_unused')
					 ->row_array();
		if ($data) { // 删除附件
			$this->db->delete('attachment', 'id='.$id);
			$this->db->delete('attachment_unused', 'id='.$id);
			$this->attachment_model->_delete_attachment($data);
		}
	}
	
	/**
	 * 网站附件浏览
	 */
	public function myattach() {

		if (!$this->member['adminid']) {
            exit(lang('m-311'));
        }

        $this->load->helper('directory');
        $this->input->get('dir').PHP_EOL;
		$dir = trim(trim(str_replace('.', '', $this->input->get('dir')), '/'), DIRECTORY_SEPARATOR);
		$root = SYS_ATTACHMENT_DIR ? (FCPATH.trim(SYS_ATTACHMENT_DIR, '/').'/') : FCPATH;
        $root = SYS_ATTACHMENT_DIR == '/' ? FCPATH : $root;
		$path = $dir ? $root.$dir.'/' : $root;
		$list = array();
		$data = directory_map($path, 1);
		$fext = $this->input->get('ext');
		$exts = explode('|', $fext);
		$fcount = max(1, (int)$this->input->get('fcount'));
		$furl = dr_url('api/myattach', array('ext' => $fext, 'fcount' => $fcount));

		if ($data) {
			foreach ($data as $t) {
				if (is_dir($path.'/'.$t)) {
					$name = trim($t, DIRECTORY_SEPARATOR);
					$list[] = array(
						'type' => 'dir',
						'name' => $name,
						'icon' => SITE_URL.'mantob/statics/images/ext/dir.gif',
						'file' => $furl.'&dir='.str_replace($root, '', $path.$name),
					);
				} else {
					$ext = trim(strrchr($t, '.'), '.');
					if ($ext != 'php' && in_array($ext, $exts)) {
						$list[] = array(
							'type' => 'file',
							'name' => $t,
							'size' => dr_format_file_size(@filesize($path.$t)),
							'file' => SITE_URL.str_replace(FCPATH, '', $path).$t,
							'icon' => is_file(FCPATH.'mantob/statics/images/ext/'.$ext.'.gif') ? SITE_URL.'mantob/statics/images/ext/'.$ext.'.gif' : SITE_URL.'mantob/statics/images/ext/blank.gif',
						);
					}
				}
			}
		}

		$this->template->assign(array(
			'list' => $list,
			'path' => str_replace(FCPATH, '/', $path),
			'purl' => $furl.'&dir='.dirname(str_replace($root, '/', $path)),
			'parent' => $dir,
			'fcount' => $fcount,
		));
		$this->template->display('myattach.html', 'admin');
	}
	
	/**
     * Ueditor上传(图片)
	 * 向浏览器返回数据json数据
     * {
     *   'url'      :'a.jpg',   //保存后的文件路径
     *   'title'    :'hello',   //文件描述，对图片来说在前端会添加到title属性上
     *   'original' :'b.jpg',   //原始文件名
     *   'state'    :'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
     * }
	 * @return void
     */
	public function ueupload() {

		if (!$this->uid) {
            exit("{'url':'','title':'','original':'','state':'".lang('m-039')."'}");
        }

        // 是否允许上传附件
		if (!$this->member['adminid'] && !$this->member_rule['is_upload']) {
            exit("{'url':'','title':'','original':'','state':'".lang('m-143')."'}");
        }

		if (!$this->member['adminid'] && $this->member_rule['attachsize']) { // 附件总大小判断
			$data = $this->db
						 ->select_sum('filesize')
						 ->where('uid', $this->uid)
						 ->get('attachment')
						 ->row_array();
			$filesize = (int)$data['filesize'];
			if ($filesize > $this->member_rule['attachsize'] * 1024 * 1024) {
				exit("{'url':'','title':'','original':'','state':'".dr_lang('m-147', $this->member_rule['attachsize'].'MB', dr_format_file_size($filesize))."'}");
			}
		}
		$path = FCPATH.'member/uploadfile/'.date('Ym', SYS_TIME).'/';
		if (!is_dir($path)) {
            dr_mkdirs($path);
        }

        $type = $this->input->get('type');
        $_ext = $type == 'img' ? 'gif|jpg|png' :
        'gz|7z|tar|ppt|pptx|xls|xlsx|rar|doc|docx|zip|pdf|txt|swf|mkv|avi|rm|rmvb|mpeg|mpg|ogg|mov|wmv|mp4|webm';

		$this->load->library('upload', array(
			'max_size' => '999999',
			'overwrite' => FALSE, // 是否覆盖
			'file_name' => substr(md5(time()), 0, 10), // 文件名称
			'upload_path' => $path, // 上传目录
			'allowed_types' => $_ext,
		));
		if ($this->upload->do_upload('upfile')) {
			$info = $this->upload->data();
			$this->load->model('attachment_model');
			$result = $this->attachment_model->upload($this->uid, $info);
			if (!is_array($result)) {
                exit('0,'.$result);
            }
			list($id, $file, $_ext) = $result;
            $url = $type == 'file' ? dr_down_file($id) : dr_file($file);
			$title = htmlspecialchars($this->input->post('pictitle', TRUE), ENT_QUOTES);
			exit("{'id':'".$id."','fileType':'.".$_ext."', 'url':'".$url."','title':'".$title."','original':'" . str_replace('|', '_', $info['client_name']) . "','state':'SUCCESS'}");
		} else {
			exit("{'url':'','title':'','original':'','state':'".$this->upload->display_errors('', '')."'}");
		}
	}

    /**
     * Ueditor附件上传
     * 向浏览器返回数据json数据
     * {
     *   'url'      :'a.rar',        //保存后的文件路径
     *   'fileType' :'.rar',         //文件描述，对图片来说在前端会添加到title属性上
     *   'original' :'编辑器.jpg',   //原始文件名
     *   'state'    :'SUCCESS'       //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
     * }
     */
    public function uefile() {

    }
	
	/**
     * Ueditor下载远程图片
	 * 返回数据格式
	 * {
	 *   'id'   : '新图片id一ue_separate_ue新地址二ue_separate_ue新地址三',
	 *   'url'   : '新地址一ue_separate_ue新地址二ue_separate_ue新地址三',
	 *   'srcUrl': '原始地址一ue_separate_ue原始地址二ue_separate_ue原始地址三'，
	 *   'tip'   : '状态提示'
	 * }
	 * @return void
     */
	public function uecatcher() {

		
	}
	
	/**
     * Ueditor未使用的图片
	 * 图片id|地址一ue_separate_ue图片id|地址二ue_separate_ue图片id|地址三
	 * @return void
     */
	public function uemanager() {

		if (!$this->uid) {
            return NULL;
        }

		$this->load->model('attachment_model');
		$data = $this->attachment_model->get_unused($this->uid, 'jpg,png,gif');
		if (!$data) {
            return NULL;
        }

		$result = array();
		foreach ($data as $t) {
			$result[] = dr_file($t['attachment']).'?dr_image_id='.$t['id'];
		}
		echo implode('ue_separate_ue', $result);
	}
	
	/**
     * 汉字转换拼音
     */
	public function pinyin() {

		$name = $this->input->get('name', TRUE);
		if (!$name) {
            exit('');
        }

        $this->load->library('pinyin');
		exit($this->pinyin->result($name));
	}
	
	/**
     * 标题检查
     */
	public function checktitle() {

		$id = (int)$this->input->get('id');
		$title = $this->input->get('title', TRUE);
		$module = $this->input->get('module');
		if (!$title || !$module) {
            exit('');
        }

		$num = $this->site[SITE_ID]
					->where('id<>', $id)
					->where('title', $title)
					->count_all_results(SITE_ID.'_'.$module);
		if ($num) {
			exit(lang('m-146'));
		} else {
			exit('');
		}
	}
	
	/**
     * 提取关键字
     */
	public function getkeywords() {

		$kw = $this->input->get('kw', TRUE);
		$kw = $kw ? $kw : $this->input->get('title', TRUE);
		$data = @file_get_contents('http://keyword.discuz.com/related_kw.html?ics=utf-8&ocs=utf-8&title='.rawurlencode($kw).'&content='.rawurlencode($kw));

        if ($data) {
			$xml = xml_parser_create();
			$kws = array();
			xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($xml, XML_OPTION_SKIP_WHITE, 1);
			xml_parse_into_struct($xml, $data, $values, $index);
			xml_parser_free($xml);
			foreach ($values as $v) {
				$kw = trim($v['value']);
				if (strlen($kw) > 5 && ($v['tag'] == 'kw' || $v['tag'] == 'ekw')) {
                    $kws[] = $kw;
                }
			}
			echo @implode(',', $kws);
		}

		exit('');
	}
	
	/**
     * 文件信息
     */
	public function fileinfo() {

		$this->load->helper('system');
		$key = $this->input->get('name');
		$info = dr_file_info($key);
		$file = count($info) > 2 ? dr_get_file($info['attachment']) : $key;

		if (in_array(strtolower(trim(substr(strrchr($file, '.'), 1, 10))), array('jpg', 'jpeg', 'gif', 'png'))) {
			echo '<img src="'.$file.'" onload="if(this.width>$(window).width()/2)this.width=$(window).width()/2;">';
		} else {
			echo '<a href="'.$file.'" target=_blank>'.($info['filename'] ? $info['filename'] : $file).'</a><br>&nbsp;';
		}
	}
	
	/**
     * 图片处理2
     */
	public function thumb2() {

        $id = $this->input->get('id');
        $width = (int)$this->input->get('width');
        $height = (int)$this->input->get('height');
        $autocut = (int)$this->input->get('autocut');

        $this->load->library('dthumb');

        // 输出图片的名称
        $tfile = 'uploadfile/thumb/'.md5('index.php?c=api&m=thumb&id='.$id
                .'&width='.$width.'&height='.$height.'&autocut='.$autocut.'').'.jpg';
        $display = FCPATH.'member/'.$tfile;

        // 存在缩略图时就输出图片
        if (is_file($display)) {
            $this->dthumb->display($display);
            return;
        }

        // 是附件id时
        if (is_numeric($id)) {
            $info = get_attachment($id);
            // 远程图片下载到本地缓存目录
            if (isset($info['remote']) && $info['remote']) {
                $file = FCPATH.'cache/attach/'.time().'_'.basename($info['attachment']);
                file_put_contents($file, dr_catcher_data($info['attachment']));
            } else {
                $file = FCPATH.$info['attachment'];
            }
            unset($info);
        } else {
            $file = str_replace(SITE_URL, FCPATH, $id);
        }

        // 图片不存在时调用默认图片
        if (!is_file($file)) {
            $file = FCPATH.'mantob/statics/images/nopic.gif';
        }

        // 生成缩略图
        $this->dthumb->thumb($file, $display, $width, $height, '', $autocut);

        // 输出缩略图
        $this->dthumb->display($display);
    }

	/**
     * 图片处理
     */
	public function thumb() {

        $id = (int)$this->input->get('id');
		$info = get_attachment($id); // 图片信息
		$file = $info && in_array($info['fileext'], array('jpg', 'gif', 'png')) ? $info['attachment'] : 'mantob/statics/images/nopic.gif'; // 图片判断

		// 参数设置
		$water = (int)$this->input->get('water');
		$width = (int)$this->input->get('width');
		$height = (int)$this->input->get('height');
		$thumb_file = FCPATH.'member/uploadfile/thumb/'.md5("index.php?c=api&m=thumb&id=$id&width=$width&height=$height&water=$water").'.jpg';

        if (!is_dir(FCPATH.'member/uploadfile/thumb/')) {
            @mkdir(FCPATH.'member/uploadfile/thumb/');
        }

		// 远程图片下载到本地缓存目录
		if (isset($info['remote']) && $info['remote']) {
			$file = FCPATH.'cache/attach/'.time().'_'.basename($info['attachment']);
			file_put_contents($file, dr_catcher_data($info['attachment']));
		} else {
			$file = FCPATH.$file;
		}

		// 处理宽高
		list($_width, $_height) = getimagesize($file);
		$width = $width ? $width : $_width;
		$height = $height ? $height : $_height;

		// 站点配置信息
        $site = $this->get_cache('siteinfo', SITE_ID);
		$iswater = (bool)$site['SITE_IMAGE_WATERMARK'];
		$config['width'] = $width;
		$config['height'] = $height;
		$config['create_thumb'] = TRUE;
		$config['source_image'] = $file;
		$config['is_watermark'] = $iswater && $water ? TRUE : FALSE; // 开启水印
        $config['is_watermark'] = isset($info['remote']) && $info['remote'] && !$site['SITE_IMAGE_REMOTE'] ? FALSE : $config['is_watermark']; // 远程附件图片水印关闭
		$config['image_library'] = 'gd2';
		$config['dynamic_output'] = TRUE; // 输出到浏览器
		$config['maintain_ratio'] = (bool)SITE_IMAGE_RATIO; // 使图像保持原始的纵横比例

		// 水印参数
		$config['wm_type'] = $site['SITE_IMAGE_TYPE'] ? 'overlay' : 'text';
		$config['wm_vrt_offset'] = $site['SITE_IMAGE_VRTOFFSET'];
		$config['wm_hor_offset'] = $site['SITE_IMAGE_HOROFFSET'];
		$config['wm_vrt_alignment'] = $site['SITE_IMAGE_VRTALIGN'];
		$config['wm_hor_alignment'] = $site['SITE_IMAGE_HORALIGN'];

		// 文字模式
		$config['wm_text'] = $site['SITE_IMAGE_TEXT'];
		$config['wm_font_size'] = $site['SITE_IMAGE_SIZE'];
		$config['wm_font_path'] = FCPATH.'mantob/statics/watermark/'.($site['SITE_IMAGE_FONT'] ? $site['SITE_IMAGE_FONT'] : 'default.ttf');
		$config['wm_font_color'] = $site['SITE_IMAGE_COLOR'] ? str_replace('#', '', $site['SITE_IMAGE_COLOR']) : '#000000';

		// 图片模式
		$config['wm_opacity'] = $site['SITE_IMAGE_OPACITY'] ? $site['SITE_IMAGE_OPACITY'] : 80;
		$config['wm_overlay_path'] = FCPATH.'mantob/statics/watermark/'.($site['SITE_IMAGE_OVERLAY'] ? $site['SITE_IMAGE_OVERLAY'] : 'default.png');

        $this->load->library('image_lib2', $config);
		$this->image_lib2->resize($thumb_file);

		if (isset($info['remote']) && $info['remote']) {
            @unlink($file);
        }
	}


	
	/**
     * 下载文件
     */
	public function file() {
		
		$id = (int)$this->input->get('id');
		$info = get_attachment($id);
		$this->template->admin();
		
		if (!$info) {
            $this->admin_msg(lang('m-326'));
        }

        // 是否允许下载附件
		if (!$this->member['adminid'] && !$this->member_rule['is_download']) {
            $this->admin_msg(lang('m-322'));
        }
		
		// 虚拟币与经验值检查
		$mark = 'attachment-'.$id;
		$table = $this->db->dbprefix('member_scorelog_'.(int)substr((string)$this->uid, -1, 1));
		if ($this->member_rule['download_score']
            && !$this->db->where('type', 1)->where('mark', $mark)->count_all_results($table)) {
			// 虚拟币不足时，提示错误
			if ($this->member_rule['download_score'] + $this->member['score'] < 0) {
				$this->admin_msg(dr_lang('m-324', SITE_SCORE, abs($this->member_rule['download_score'])));
			}
			// 虚拟币扣减
			$this->member_model->update_score(1, $this->uid, (int)$this->member_rule['download_score'], $mark, "lang,m-325");
		}
		if ($this->member_rule['download_experience']
            && !$this->db->where('type', 0)->where('mark', $mark)->count_all_results($table)) {
			// 经验值扣减
			$this->member_model->update_score(0, $this->uid, (int)$this->member_rule['download_experience'], $mark, "lang,m-325");
		}
		
		$file = $info['attachment'];
		$this->db->where('id', $id)->set('download', 'download+1', FALSE)->update('attachment');
		
		if (strpos($file, ':/')) {
		    //远程文件
			header("Location: $file");
		} else {
		    //本地文件
			$file = FCPATH.str_replace('..', '', $file);
			$name = urlencode($info['filename'].'.'.$info['fileext']);
			$this->load->helper('download');
			force_download($name, file_get_contents($file)); 
		}
	}
	
	
	/**
     * OAuth2授权登录
     */
	public function oauth() {

		if ($this->uid) {
            $this->member_msg(lang('m-013'), $_SERVER['HTTP_REFERER']);
        }

		$appid = $this->input->get('id');
		$oauth = require FCPATH.'config/oauth.php';
		$config	= $oauth[$appid];
		if (!$config) {
            $this->member_msg(lang('m-047'));
        }

		$config['url'] = SITE_URL.'member/index.php?c=api&m=oauth&id='.$appid; // 回调地址设置
		$this->load->library('OAuth2');

		// OAuth
        $code = $this->input->get('code', TRUE);
		$oauth = $this->oauth2->provider($appid, $config);

		if (!$code) {
            // 登录授权页
			try {
				$oauth->authorize();
			} catch (OAuth2_Exception $e) {
				$this->member_msg(lang('m-048').' _ '.$e);
			}
		} else {
		    // 回调返回数据
			try {
	        	$user = $oauth->get_user_info($oauth->access($code));
				if (is_array($user) && $user['oid']) {
					$code = $this->member_model->OAuth_login($appid, $user);
					$this->member_msg(lang('m-002').$code, dr_url('home/index'), 1, 3);
				} else {
					$this->member_msg(lang('m-051'));
				}
			} catch (OAuth2_Exception $e) {
                $this->member_msg(lang('m-051').' - '.$e);
			}
		}
	}
	
	/**
	 * 更新模型浏览数
	 */
	public function hits() {

	    $id = (int)$this->input->get('id');
	    $mid = (int)$this->input->get('mid');
		$mod = $this->get_cache('space-model', $mid);
		if (!$mod) {
            exit('0');
        }

		$table = $this->db->dbprefix('space_'.$mod['table']);
		$name = $table.'-space-hits-'.$id;
		$hits = (int)$this->get_cache_data($name);
		if (!$hits) {
			$data = $this->db
						 ->where('id', $id)
						 ->select('hits')
						 ->limit(1)
						 ->get($table)
						 ->row_array();
			$hits = (int)$data['hits'];
		}

		$hits++;
		$this->set_cache_data($name, $hits, 360000);
		$this->db
			 ->where('id', $id)
			 ->update($table, array('hits' => $hits));

		exit("document.write('$hits');");
	}

    /**
     * 会员验证登录
     *
     * @param	string	$username	用户名
     * @param	string	$password	明文密码
     * @param	intval	$expire	    会话生命周期
     * @param	intval	$back	    返回uid
     * @return	string|intval
     * string	EMAIL
     */
    public function login() {
        $data = $this->member_model->login($this->input->get('username'), $this->input->get('password'), NULL, $this->input->get('back'));
        echo $data['email'];
    }
}