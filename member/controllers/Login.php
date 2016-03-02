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
	
class Login extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 登录
     */
    public function index() {
	
		$data = $error = '';
		$MEMBER = $this->get_cache('member');
		
		if (IS_POST) {
			$data = $this->input->post('data', TRUE);
            $back_url = $_POST['back'] ? urldecode($this->input->post('back')) : '';
            if ($MEMBER['setting']['logincode'] && !$this->check_captcha('code')) {
				$error = lang('m-000');
			} elseif (!$data['password'] || !$data['username']) {
				$error = lang('m-001');
			} else {
				$code = $this->member_model->login($data['username'], $data['password'], $data['auto'] ? 31104000 : 86400);
				if (strlen($code) > 3) {
				    // 登录成功
                    $this->hooks->call_hook('member_login', $data); // 
     //                // 2014年10月17日 11:47:54 修改佳晔
     //                $this->member_msg(lang('m-002').$code, dr_member_url('home/index'), 1, 3);
     //                // 登录成功挂钩点
					$this->member_msg(lang('m-002').$code, $back_url && strpos($back_url, 'register') === FALSE ? $back_url : SITE_URL, 1, 3);
                    
				} elseif ($code == -1) {
					$error = lang('m-003');
				} elseif ($code == -2) {
					$error = lang('m-004');
				} elseif ($code == -3) {
					$error = lang('m-005');
				} elseif ($code == -4) {
					$error = lang('m-006');
				}
			}
		} else {
            $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        }
		
		$this->template->assign(array(
			'data' => $data,
			'code' => $MEMBER['setting']['logincode'],
            'back_url' => $back_url,
			'meta_name' => lang('m-007'),
			'result_error' => $error,
		));
		$this->template->display('login.html');
    }
	
	/**
     * Ajax 登录
     */
	public function ajax() {
	
		$login = $data = $error = '';
		$MEMBER = $this->get_cache('member');
		
		if (IS_POST) {
			$data = $this->input->post('data', TRUE);
			if ($MEMBER['setting']['logincode'] && !$this->check_captcha('code')) {
				$error = lang('m-000');
			} elseif (!$data['password'] || !$data['username']) {
				$error = lang('m-001');
			} else {
				$code = $this->member_model->login($data['username'], $data['password'], $data['auto'] ? 31104000 : 86400);
				if (strlen($code) > 3) {
				    // 登录成功
                    $this->hooks->call_hook('member_login', $data); // 登录成功挂钩点
					$login = $code;
				} elseif ($code == -1) {
					$error = lang('m-003');
				} elseif ($code == -2) {
					$error = lang('m-004');
				} elseif ($code == -3) {
					$error = lang('m-005');
				} elseif ($code == -4) {
					$error = lang('m-006');
				}
			}
		}
		
		$this->template->assign(array(
			'data' => $data,
			'code' => $MEMBER['setting']['logincode'],
            'login' => $login,
			'error' => $error,
			'meta_name' => lang('m-007'),
			'result_error' => $error,
		));
		$this->template->display('login_ajax.html');
		$this->output->enable_profiler(FALSE);
	}
	
	/**
     * 找回密码
     */
    public function find() {
	
		$step = max(1, (int)$this->input->get('step'));
		$error = '';
		
		if (IS_POST) {
            switch ($step) {
                case 1:
                    if (!$this->check_captcha('code')) {
                        $this->member_msg(lang('m-000'));
                    }
					if ($uid = get_cookie('find')) {
						$this->member_msg(
                            lang('m-093'),
                            dr_member_url('login/find', array('step' => 2, 'uid' => $uid)),
                            1
                        );
					} else {
						$name = $this->input->post('name', TRUE);
						$name = in_array($name, array('email', 'phone')) ? $name : 'email';
						$value = $this->input->post('value', TRUE);
						$data = $this->db
									 ->select('uid,username,randcode')
									 ->where($name, $value)
									 ->limit(1)
									 ->get('member')
									 ->row_array();
						if ($data) {
							$randcode = dr_randcode();
							if ($name == 'email') {
								$this->load->helper('email');
								if (!$this->sendmail($value, lang('m-014'), dr_lang('m-187', $data['username'], $randcode, $this->input->ip_address()))) {
									$this->member_msg(lang('m-189'));
								}
								set_cookie('find', $data['uid'], 300);
								$this->db
                                     ->where('uid', $data['uid'])
                                    ->update('member', array('randcode' => $randcode));
								$this->member_msg(lang('m-093'), dr_member_url('login/find', array('step' => 2, 'uid' => $data['uid'])), 1);
							} else {
								$result = $this->member_model->sendsms($value, dr_lang('m-088', $randcode));
								if ($result['status']) {
								    // 发送成功
									set_cookie('find', $data['uid'], 300);
									$this->db
                                         ->where('uid', (int)$data['uid'])
                                         ->update('member', array('randcode' => $randcode));
									$this->member_msg(lang('m-093'), dr_member_url('login/find', array('step' => 2, 'uid' => $data['uid'])), 1);
								} else {
								    // 发送失败
									$this->member_msg($result['msg']);
								}
							}
						} else {
							$error = $name == 'phone' ? lang('m-182') : lang('m-185');
						}
					}
					break;

				case 2:

                    if (!$this->check_captcha('code2')) {
                        $this->member_msg(lang('m-000'));
                    }

					$uid = (int)$this->input->get('uid');
					$code = (int)$this->input->post('code');

                    if (!$uid || !$code) {
                        $this->member_msg(lang('m-001'));
                    }

					$data = $this->db
								 ->where('uid', $uid)
								 ->where('randcode', $code)
								 ->select('salt,uid,username,email')
								 ->limit(1)
								 ->get('member')
								 ->row_array();
					if (!$data) {
                        $this->db
                             ->where('uid', $uid)
                             ->update('member', array('randcode' => ''));
                        $this->member_msg(lang('m-202'), dr_member_url('login/find'));
                    }
					
					$password1 = $this->input->post('password1');
					$password2 = $this->input->post('password2');
					if ($password1 != $password2) {
						$error = lang('m-019');
					} elseif (!$password1) {
						$error = lang('m-018');
					} else {
						// 修改密码
						$this->db
							 ->where('uid', $data['uid'])
							 ->update('member', array(
								'randcode' => 0,
								'password' => md5(md5($password1).$data['salt'].md5($password1))
							 ));
						if ($this->get_cache('MEMBER', 'setting', 'ucenter')) {
                            uc_user_edit($data['username'], '', $password1, '', 1);
                        }
						$this->member_msg(lang('m-052'), dr_url('login/index'), 1);
					}
					break;
			}
		}
		
		$this->template->assign(array(
			'step' => $step,
			'error' => $error,
			'action' => 'find',
			'mobile' => $this->get_cache('member', 'setting','ismobile'),
			'meta_name' => lang('m-014'),
			'result_error' => $error,
		));
		$this->template->display('find.html');
    }
	
	/**
     * 审核会员
     */
    public function verify() {

        if (!isset($_SERVER['HTTP_USER_AGENT'])
            || strlen($_SERVER['HTTP_USER_AGENT']) < 20 ) {
            $this->member_msg('认证失败');
        }

        $data = $this->member_model->get_decode($this->input->get('code'));
		if (!$data) {
            $this->member_msg(lang('m-190'));
        }

		list($time, $uid, $code) = explode(',', $data);
		if (!$this->db->where('uid', $uid)->where('randcode', $code)->count_all_results('member')) {
			$this->member_msg(lang('m-193'));
		}

		$this->db
             ->where('uid', $uid)
             ->where('groupid<>', 3)
             ->update('member', array('randcode' => 0, 'groupid' => 3));

		$this->member_msg(lang('m-194'), dr_member_url('login/index'), 1);
    }
	
	/**
     * 重发邮件审核
     */
    public function resend() {

		if ($this->member['groupid'] != 1) {
            $this->member_msg(lang('m-233'));
        }
		if ($this->get_cache('MEMBER', 'setting', 'regverify') != 1) {
            $this->member_msg(lang('m-230'));
        }
		if (get_cookie('resend') && $this->member['randcode']) {
            $this->member_msg(lang('m-232'));
        }

		$url = MEMBER_URL.'index.php?c=login&m=verify&code='.$this->member_model->get_encode($this->uid);
		$this->sendmail(
            $this->member['email'],
            lang('m-191'),
            dr_lang('m-192', $this->member['username'], $url, $url, $this->input->ip_address())
        );

		$this->input->set_cookie('resend', $this->uid, 3600);
		$this->member_msg(dr_lang('m-231', $this->member['email']), dr_url('home/index'), 1);
    }
	
	/**
     * 退出
     */
    public function out() {
		$this->member_msg(lang('m-015').$this->member_model->logout(), SITE_URL, 1, 3);
    }
	
}