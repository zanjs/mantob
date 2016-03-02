<?php

/**
 * 360
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.com/v2/mantob/libraries/OAuth2/Provider/360.php
 */

class OAuth2_Provider_360 extends OAuth2_Provider {

	public $name	= '360';
	public $human	= '360';
	public $method	= 'POST';
	public $uid_key = 'id';
	
	/**
     * ��Ȩ��֤��¼��ַ
     */
	public function url_authorize() {
		return 'https://openapi.360.cn/oauth2/authorize';
	}
	
	/**
     * ��Ȩ��֤���ʵ�ַ
     */
	public function url_access_token() {
		return 'https://openapi.360.cn/oauth2/access_token';
	}
	
	/**
     * ��ȡ�û���Ϣ
     */
	public function get_user_info(OAuth2_Token_Access $token) {
		$url	= 'https://openapi.360.cn/user/me?'.http_build_query(array(
			'access_token' => $token->access_token
		));
		$return = file_get_contents($url);
		$user	= json_decode($return);
		if (array_key_exists('error', $user)) throw new OAuth2_Exception($return);
		// ����ͳһ�����ݸ�ʽ
		return array(
			'oid'			=> $user->id,
            'oauth'			=> $this->name,
			'avatar'		=> $user->avatar,
			'nickname'		=> $user->name,
			'expire_at'		=> $token->expires,
			'access_token'	=> $token->access_token,
			'refresh_token'	=> $token->refresh_token
		);
	}
}
