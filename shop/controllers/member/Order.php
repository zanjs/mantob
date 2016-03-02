<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Dayrui Website Management System
 *
 * @since		version 2.0.0
 * @author		Dayrui <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, Dayrui.Com, Inc.
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

		$this->_member(0);
	}

    /**
     * 我的预约
     */
    public function make() {

        $this->_make(0);
    }
	
	/**
     * 商品订单
     */
	public function item() {
		$this->_member(1);
	}
	
	/**
     * 买家付款
     */
	public function pay() {
		$this->_member_pay();
	}
	
	/**
     * 买家确认收货
     */
	public function confirm() {
		$this->_member_confirm();
	}
	
    /**
     * 买家申请退款
     */
    public function refund() {
       
        $this->_member_refund();
    }


	/**
     * 订单详情
     */
	public function show() {
		$this->_member_show();
	}
	
	/**
     * 订单商品是否评价
     */
	public function isreview() {

		$this->_member_isreview();
	}

	/**
     * 订单商品评价
     */
	public function review() {
		$this->_member_review();
	}

	/**
     * 关闭订单
     */
	public function close() {

        $id = (int)$this->input->get('id');
        $data = $this->order_model->get_order($id);
        if (!$data) {
            $this->member_msg(lang('my-10'));
        }

        // 订单信息只能相关的买家才能查看
        if ($data['uid'] != $this->uid) {
            $this->member_msg(lang('my-11'));
        }

        if ($data['status'] == 1 || ($data['status'] == 2 && $data['ptid'] == 3)) {
            // 关闭订单
            $this->link
                 ->where('id', $id)
                 ->update($this->order_model->tablename, array('status' => 0));
            $this->member_msg(lang('000'), dr_url(APP_DIR.'/order/show', array('id' => $id)), 1);
        } else {
            $this->member_msg(lang('my-54'));
        }

	}

    /**
     * 删除订单
     */
    public function delmake() {

        $id = (int)$this->input->get('id');
        $data = $this->order_model->get_order($id);
        if (!$data) {
            $this->member_msg(lang('my-10'));
        }

        // 订单信息只能相关的买家才能查看
        if ($data['uid'] != $this->uid) {
            $this->member_msg(lang('my-11'));
        }

        if ($data['status'] == 1 || $data['status'] == 2 ) {
            // 关闭订单
            $this->link
                 ->where('id', $id)
                 ->delete($this->order_model->tablename);
            $this->member_msg(lang('000'), MEMBER_URL.'index.php?s='.APP_DIR.'&c=order&m=make');
        } else {
            $this->member_msg(lang('my-55'));
        }


    }
	
	/**
     * 快递跟踪
     */
	public function kd() {
	
		$id = $this->input->get('id');
		$sn = $this->input->get('sn');
		
		if (!$id || !$sn) {
            exit(lang('my-17'));
        }
		
		echo $this->order_model->kd_status($id, $sn);
	}
}