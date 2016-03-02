<?php

/**
 * OAuth�쳣��
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class OAuth2_Exception extends Exception {

	public function __construct($message) {
		parent::__construct($message, 0);
	}
	
	public function __toString() {
		return $this->message;
	}
}