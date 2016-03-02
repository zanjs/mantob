<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mantob Website Management System
 *
 * @since		version 2.0.1
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
 
class Home extends M_Controller {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function index() {
        $file = FCPATH.'cache/index/home-'.SITE_ID.'.html';
        // 系统开启静态首页、非手机端访问、静态文件不存在时，才生成文件
		if (SITE_HOME_INDEX && !$this->template->mobile && !is_file($file)) {
            ob_start();
            $this->template->assign(array(
                'indexc' => 1,
                'meta_title' => SITE_TITLE,
                'meta_keywords' => SITE_KEYWORDS,
                'meta_description' => SITE_DESCRIPTION,
            ));
            $this->template->display('index.html');
            $html = ob_get_clean();
            @file_put_contents($file, $html, LOCK_EX);
            echo $html;exit;
		} else {
			$this->template->assign(array(
                'indexc' => 1,
				'meta_title' => SITE_TITLE,
				'meta_keywords' => SITE_KEYWORDS,
				'meta_description' => SITE_DESCRIPTION,
			));
			$this->template->display('index.html');
		}
    }

}