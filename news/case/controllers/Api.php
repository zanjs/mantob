<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');
	
/**
 * Api调用类
 * Omweb Website Management System
 *
 * @since		version 2.0.1
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
	
	/**
     * 购买文档
     */
	public function buy() {
		$this->_show_buy();
	}
	
	/**
     * 收藏文档
     */
	public function favorite() {
		$this->api_favorite();
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
	
}