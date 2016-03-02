<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.2.2
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Space extends M_Controller {

	private $space;
	
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		if (!$this->member['allowspace']) {
            $this->member_msg(lang('m-342'));
        }
		$this->load->model('space_model');
		$this->space = $this->space_model->get($this->uid);
    }
	
    /**
     * 空间资料
     */
    public function index() {
	
		$error = NULL;
		$field = array();
		$MEMBER = $this->get_cache('member');
		$field[] = $MEMBER['spacefield']['name'];
		if ($MEMBER['spacefield']
            && $MEMBER['group'][$this->member['groupid']]['spacefield']) {
			foreach ($MEMBER['spacefield'] as $t) {
				if (in_array($t['fieldname'], $MEMBER['group'][$this->member['groupid']]['spacefield'])) {
					$field[] = $t;
				}
			}
		}
		
		if (IS_POST) {
			$post = $this->validate_filter($field, $this->space);
			if (isset($post['error'])) {
				$data = $this->input->post('data', TRUE);
				$error = $post['msg'];
			} else {
				$error = $this->space_model->update($this->uid, $this->member['groupid'], $post[1]);
				if ($error) {
					$this->attachment_handle($this->uid, $this->db->dbprefix('space').'-'.$this->uid, $field, $this->space);
				}
				if ($error == 0) {
					// 名称重复
					$error = lang('m-239');
				} elseif ($error == 1) {
					// 操作成功
					$this->member_msg(lang('000'), dr_url('space/index'), 1);
				} else {
					// 操作成功,等待审核
					$this->member_msg(lang('m-240'), dr_url('space/index'), 2);
				}
			}
		} else {
			$data = $this->space;
		}
		
		$this->template->assign(array(
			'data' => $data,
			'field' => $field,
			'myfield' => $this->field_input($field, $data, FALSE, 'uid'),
			'newspace' => $this->space ? 0 : 1,
            'result_error' => $error,
		));
		$this->template->display('space_index.html');
    }
	
	
	/**
     * 空间模板
     */
	public function template() {
		
		$style = $this->input->get('style');
		if ($style && $this->space['style'] != $style) {
			$rule = dr_string2array(@file_get_contents(FCPATH.'member/templates/'.$style.'/rule.php'));
			if ($style == 'default' || isset($rule[$this->markrule])) {
				$this->db
					 ->where('uid', (int)$this->uid)
					 ->update($this->db->dbprefix('space'), array('style' => $style));
				$this->member_msg(lang('m-319'), dr_member_url('space/template'), 1);
			} else {
				$this->member_msg(lang('m-320'));
			}
		}
		
		$list = array();
		$data = array_diff(dr_dir_map(FCPATH.'member/templates/', 1), array('admin', 'member'));
		if ($data) {
			foreach ($data as $dir) {
				$tpl = array(
					'name' => $dir,
					'preview' => MEMBER_URL.'templates/'.$dir.'/preview.jpg'
				);
				$rule = dr_string2array(@file_get_contents(FCPATH.'member/templates/'.$dir.'/rule.php'));
				if ($dir == 'default') {
					$list[] = $tpl;
				} elseif ($rule && isset($rule[$this->markrule])) {
					$list[] = $tpl;
				}
			}
		}
		$this->template->assign(array(
			'list' => $list,
			'style' => $this->space['style'] ? $this->space['style'] : 'default',
		));
		$this->template->display('space_template.html');
	}
	
}