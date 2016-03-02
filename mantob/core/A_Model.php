<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.5
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
 
class A_Model extends CI_Model {

    /**
     * 应用模型继承类
     */
    public function __construct() {
        parent::__construct();
    }
	
	/**
	 * 删除模块时调用
	 *
	 * @param	string	$module	模块目录
	 * @param	intval	$siteid	站点id，默认为全部站点
	 * @return  string
	 */
	public function delete_for_module($module, $siteid) {
	
	}
	
	/**
	 * 删除模块内容时调用
	 *
	 * @param	string	$module	模块目录
	 * @param	intval	$siteid	站点id，默认为全部站点
	 * @return  string
	 */
	public function delete_for_cid($cid, $module) {
	
	}
	
	/**
	 * 删除会员时调用
	 *
	 * @param	intval	$uid	会员uid
	 * @return  string
	 */
	public function delete_for_uid($uid) {
	
	}
	
	/**
	 * 将应用菜单安装至后台菜单中
	 *
	 * @param	string	$dir	应用目录名称
	 * @param	intval	$id		应用id
	 * @return  void
	 */
	public function install_admin_menu($dir, $id) {
		
	}
	
	/**
	 * 将应用菜单安装至会员菜单中
	 *
	 * @param	string	$dir	应用目录名称
	 * @param	intval	$id		应用id
	 * @return  void
	 */
	public function install_member_menu($dir, $id) {
		
	}

	/**
	 * 生成此应用的钩子配置文件
	 *
	 * @param	string	$dir	应用目录名称
	 * @param	array	$data	钩子数据数组
	 * @return  void
	 */
	public function update_hooks($dir, $data = NULL) {

        $app = require FCPATH.'config/app_hooks.php';

        if ($data) {
            // 安装钩子
            $app[$dir] = $data;
        } else {
            // 卸载钩子
            unset($app[$dir]);
        }

        // 更新文件
        $php = '<?php'.PHP_EOL.PHP_EOL
            .'/**'.PHP_EOL
            .' * 应用的钩子定义配置'.PHP_EOL
            .' */'.PHP_EOL.PHP_EOL
            .'return '.str_replace(
                array('\'{app}', '"{app}'),
                array('FCPATH.\'app/'.$dir.'/', 'FCPATH."app/'.$dir.'/'),
                var_export($app, TRUE)
            ).';';

        // 生成文件
        file_put_contents(FCPATH.'config/app_hooks.php', $php);

	}
	
}