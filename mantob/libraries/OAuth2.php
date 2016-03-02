<?php

/**
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.com/v2/mantob/libraries/OAuth2.php
 */

/**
 * OAuth2��¼
 */
 
include('OAuth2/Exception.php');
include('OAuth2/Token.php'); 
include('OAuth2/Provider.php');

class OAuth2 {

	/**
	 * ����һ���µĹ�Ӧ��
	 *
	 * @param   string $name    provider name
	 * @param   array  $options provider options
	 * @return  OAuth2_Provider
	 */
	public static function provider($name, array $options = NULL) {
		$name = ucfirst(strtolower($name));
		$class = 'OAuth2_Provider_'.$name;
		include_once 'OAuth2/Provider/'.$name.'.php';
		return new $class($options);
	}
}