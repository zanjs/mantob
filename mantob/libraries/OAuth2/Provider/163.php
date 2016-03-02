<?php

/**
 * 163
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.com/v2/mantob/libraries/OAuth2/Provider/163.php
 */

class OAuth2_Provider_163 extends OAuth2_Provider {

	public $name	= '163';
	public $human	= '网易微博';
	public $method	= 'POST';
	public $uid_key = 'user_id';
	
	/**
     * 授权认证登录地址
     */
	public function url_authorize() {
		return 'https://api.t.163.com/oauth2/authorize';
	}
	
	/**
     * 授权认证访问地址
     */
	public function url_access_token() {
		return 'https://api.t.163.com/oauth2/access_token';
	}
	
	/**
     * 获取用户信息
     */
	public function get_user_info(OAuth2_Token_Access $token) {
		$url = 'https://api.t.163.com/users/show.json?'.http_build_query(array(
			'access_token' => $token->access_token,
            'user_id' => $token->uid
		));
		$return	= dr_catcher_data($url);
		$user = json_decode($return);
      	if (array_key_exists('error', $user)) {
            throw new OAuth2_Exception($return);
        }
		// 返回统一的数据格式
		return array(
			'oid' => $user->id,
            'oauth' => $this->name,
			'avatar' => $user->profile_image_url,
			'nickname' => $user->screen_name,
			'expire_at' => $token->expires,
			'access_token' => $token->access_token,
			'refresh_token'	=> $token->refresh_token
		);
	}
}