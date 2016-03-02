<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * mantob Website Management System
 *
 * @since		version 2.0.0
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
     * 购物车
     */
    public function cart() {
        $this->_home_cart();
    }
	
	/**
     * 加入购物车
     */
    public function add() {
        $this->_add_cart();
    }
	
	/**
     * 移出购物车
     */
    public function del() {
        $this->_del_cart();
    }
	
	/**
     * 订单购买确认
     */
    public function buy() {
        $this->_buy();
    }
	
	/**
     * 订单计算价格
     */
    public function price() {
	
        $data = dr_string2array($this->input->get('data'));
		$score = (int)$this->input->get('score'); // 虚拟币抵消
		
		if ($data) {
			list($price, $total, $freight) = $this->order_model->get_price($data, $score);
		} else {
			$freight = $price = $total = 0;
		}
		
		echo $this->input->get('callback').'('.json_encode(array(
			'price' => number_format($price, 2),
			'total' => number_format($total, 2),
			'freight' => number_format($freight, 2),
		)).')';
    }
}