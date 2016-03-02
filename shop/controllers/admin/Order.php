<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.2.2
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

require APPPATH.'core/D_Order.php';
 
class Order extends D_Order {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }
	
	/**
     * 我的订单
     */
	public function index() {

		if (IS_POST && $this->input->post('action')) {
			
			$ids = $this->input->post('ids', TRUE);
			if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
			
			if ($this->input->post('action') == 'del') {
				if (!$this->is_auth(APP_DIR.'admin/format/del')) {
                    exit(dr_json(0, lang('160')));
                }
				$this->link
					 ->where_in('id', $ids)
					 ->delete($this->order_model->tablename);
				$this->link
					 ->where_in('fid', $ids)
					 ->delete($this->order_model->dataname);
				$this->order_model->cache();
				exit(dr_json(1, lang('000')));
			} else {
				if (!$this->is_auth(APP_DIR.'admin/format/edit')) {
                    exit(dr_json(0, lang('160')));
                }
				$_data = $this->input->post('data');
				foreach ($ids as $id) {
					$this->link
						 ->where('id', $id)
						 ->update($this->order_model->tablename, $_data[$id]);
				}
				$this->order_model->cache();
				exit(dr_json(1, lang('000')));
			}			
		} else {
            // 执行关闭过期订单操作
            $this->order_model->close_order();
        }
	 
		// 根据参数筛选结果
		$param = array();
		if ($this->input->get('search')) {
            $param['search'] = 1;
        }
		
		// 数据库中分页查询
		list($data, $param)	= $this->order_model->limit_page($param, max((int)$this->input->get('page'), 1), (int)$this->input->get('total'));
		
		if ($this->input->get('search')) {
			$_param = $this->cache->file->get($this->order_model->cache_file);
		} else {
			$_param = $this->input->post('data');
		}
		$_param = $_param ? $param + $_param : $param;
		
		$this->template->assign(array(
			'list' => $data,
			'pages'	=> $this->get_pagination(dr_url(APP_DIR.'/order/index', $param), $param['total']),
			'param'	=> $_param,
			'menu' => $this->get_menu(array(
				lang('my-31') => APP_DIR.'/admin/order/index'
			)),
			'paytype' => $this->order_model->get_pay_type(),
		));
		$this->template->display('order_index.html');
	}
    
   /**
     * 订单查询
     */
     
	public function orderQuery(){
        // echo " 订单查询";
        $this->template->display('order_index.html');
    }

    /**
     * 订单打印
     */
     
    public function orderPrint(){
        $this->template->assign(array(
            'menu' => $this->get_menu(array(
                lang('my-31') => APP_DIR.'/admin/order/index',
                lang('my-38') => APP_DIR.'/admin/order/orderPrint/id/'.$this->input->get('id'),
            ))
        ));
        $this->_member_print();
    }
	
    
	/**
     * 快递跟踪
     */
	public function kd() {
		echo $this->order_model->kd_status($this->input->get('id'), $this->input->get('sn'));
	}
	
	/**
     * 订单商品是否评价
     */
	public function isreview() {
		$this->_member_isreview();
	}
	
	/**
     * 订单详情
     */
	public function show() {
		$this->template->assign(array(
			'menu' => $this->get_menu(array(
				lang('my-31') => APP_DIR.'/admin/order/index',
				lang('my-38') => APP_DIR.'/admin/order/show/id/'.$this->input->get('id'),
			))
		));
		$this->_member_show();
	}
}