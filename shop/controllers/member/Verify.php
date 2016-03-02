<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.net/v2/news/controllers/member/verify.php
 */

require FCPATH.'mantob/core/D_Member_Verify.php';

class Verify extends D_Member_Verify {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }
	
}