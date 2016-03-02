<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.0.5
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

require FCPATH.'mantob/core/C_Model.php';
 
class Content_model extends C_Model {

	/**
	 * 构造函数
	 */
    public function __construct() {
        parent::__construct();
    }
	
	/**
	 * 用于商品订单
	 */
	public function get_item_data($id) {
	
		if (!$id) {
            return NULL;
        }
		
		$data1 = $this->link // 主表
					  ->where('id', $id)
					  ->where('status', 9)
					  ->where('onsale', 1)
					  ->select('id,catid,tableid,title,thumb,price,uid,author,url,quantity,freight')
					  ->limit(1)
					  ->get($this->prefix)
					  ->row_array();
		if (!$data1) {
            return NULL;
        }
		
		$data2 = $this->link // 副表
					  ->where('id', $id)
					  ->select('discount,format')
					  ->limit(1)
					  ->get($this->prefix.'_data_'.$data1['tableid'])
					  ->row_array();
		$data1['format'] = dr_string2array($data2['format']);
		$data1['discount'] = dr_string2array($data2['discount']);
		
		return $data1;
	}
	
}