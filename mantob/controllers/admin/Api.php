<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');
	
/**
 * 后台Api调用类
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.net/v2/mantob/controllers/admin/api.php
 */
 
class Api extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }
	
	/**
     * 查看资料
     */
	public function member() {

        $uid = str_replace('author_', '', $this->input->get('uid'));
        if (is_numeric($uid)) {
            $data = $this->db
                         ->where('uid', (int)$uid)
                         ->limit(1)
                         ->get('member')
                         ->row_array();
        } else {
            $data = $this->db
                         ->like('username', $uid)
                         ->limit(1)
                         ->get('member')
                         ->row_array();
        }

		if (!$data) {
            exit('(#'.$uid.')'.lang('236'));
        }
		
		$this->template->assign(array(
			'data' => $data,
		));
		$this->template->display('member.html');
	}
	
	/**
     * 测试ftp链接状态
     */
	public function testftp() {
	
		$rurl = $this->input->get('rurl');
		$host = $this->input->get('host');
		$port = $this->input->get('port');
		$pasv = $this->input->get('pasv');
		$path = $this->input->get('path');
		$mode = $this->input->get('mode');
        $username = $this->input->get('username');
        $password = $this->input->get('password');
		
		if (!$host || !$username || !$password) {
            exit(lang('035'));
        }

        if (!$rurl) {
            exit(lang('199'));
        }

		$this->load->library('ftp');
		if (!$this->ftp->connect(array(
			'hostname' => $host,
			'username' => $username,
			'password' => $password,
			'port' => $port ? $prot : 21,
			'passive' => $pasv ? TRUE : FALSE,
			'debug' => FALSE
		))) {
            exit(lang('036'));
        }

		if (!$this->ftp->upload(FCPATH.'index.php', $path.'/test.ftp', $mode, 0775)) {
            exit(lang('037'));
        }

        if (strpos(dr_catcher_data($rurl.'/test.ftp'), 'mantob.com') === FALSE) {
            exit(lang('200'));
        }

		if (!$this->ftp->delete_file($path.'/test.ftp')) {
            exit(lang('039'));
        }

		$this->ftp->close();
		
		exit('ok');
	}

    // 测试阿里云存储状态
    public function aliyuntest() {

        $id = $this->input->get('id');
        $host = $this->input->get('host');
        $rurl = $this->input->get('rurl');
        $secret = $this->input->get('secret');
        $bucket = $this->input->get('bucket');

        if (!$id || !$host || !$secret || !$bucket) {
            exit(lang('035'));
        }

        if (!$rurl) {
            exit(lang('199'));
        }

        require_once FCPATH.'mantob/libraries/AliyunOSS/sdk.class.php';
        $oss = new ALIOSS($id, $secret, $host);
        $response = $oss->upload_file_by_file($bucket, 'test.txt', FCPATH.'index.php');

        if ($response->status == 200) {
            $oss->delete_object($bucket, 'test.txt');
            if (strpos(dr_catcher_data($rurl.'/test.txt'), 'mantob.com') === FALSE) {
                exit(lang('200'));
            }
            exit('ok');
        } else {
            exit($response->body);
        }

    }

    // 测试百度云存储状态
    public function baidutest() {

        $ak = $this->input->get('ak');
        $sk = $this->input->get('sk');
        $host = $this->input->get('host');
        $rurl = $this->input->get('rurl');
        $bucket = $this->input->get('bucket');

        if (!$ak || !$host || !$sk || !$bucket) {
            exit(lang('035'));
        }

        if (!$rurl) {
            exit(lang('199'));
        }

        require_once FCPATH.'mantob/libraries/BaiduBCS/bcs.class.php';
        $bcs = new BaiduBCS($ak, $sk, $host);
        $opt = array();
        $opt['acl'] = BaiduBCS::BCS_SDK_ACL_TYPE_PUBLIC_WRITE;
        $opt['curlopts'] = array(CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 1800);
        $response = $bcs->create_object($bucket, '/test.txt', FCPATH.'index.php', $opt);

        if ($response->status == 200) {
            if (strpos(dr_catcher_data($rurl.'/test.txt'), 'mantob.com') === FALSE) {
                exit(lang('200'));
            }
            $bcs->delete_object($bucket, '/test.txt');
            exit('ok');
        } else {
            exit('error');
        }

    }
}