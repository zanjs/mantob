<?php

/**
 * mantob Website Management System
 *
 * @since		version 2.2.2
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class D_Order extends M_Controller {
	
	public $mconfig; // 商店配置信息
	public $is_quantity = TRUE; // 是否更新库存
	protected $is_num = TRUE; // 是否购买数量验证
	protected $is_format = TRUE; // 是否商品格式筛选（SKU）
	protected $is_freight = TRUE; // 是否加入运送方式
	
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
		$module = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR);
		if (!$module) {
            $this->admin_msg(lang('m-148'));
        }
		$this->load->library('cart');
		$this->load->model('order_model');
		$this->mconfig = $this->order_model->get_config();
		$this->is_num = (bool)$this->mconfig['num'];
		$this->is_freight = (bool)$this->mconfig['freight'];
		$this->is_quantity = (bool)$this->mconfig['quantity'];
    }
	
	/**
     * 购物车商品数量
     */
    public function total() {
		exit("document.write('{$this->cart->total_items()}');");
	}
	
	/**
     * 加入购物车
     */
    protected function _add_cart() {
		$iid = (int)$this->input->get('iid');
		$num = (int)$this->input->get('num');
		$fid = $this->input->get('fid');
		$data = array(
		   'id' => $iid.'_'.$fid,
		   'qty' => $num,
		   'name' => 'finecms',
		   'price' => 2.0,
		);
		$this->cart->insert($data);
		echo $this->cart->total_items();
		exit;
    }
	
	/**
     * 移出购物车
     */
    protected function _del_cart() {
		$this->cart->update(array(
		   'qty' => 0,
		   'rowid' => $this->input->post('id'),
		));
    }
	
	/**
	 * 格式化商品信息
	 *
	 * @param	array	$data
	 * @return	array
	 */
	private function _format_item($data) {
	
		$this->load->model('content_model');
		if ($this->is_format) {
			$FORMAT = $this->get_cache('format-'.SITE_ID);
			if (!$FORMAT) {
				$this->load->model('format_model');
				$FORMAT = $this->format_model->cache();
			}
		}
		
		$list = array();
		$amount = 0;

		foreach ($data as $key => $t) {
		
			list($id, $fid) = @explode('_', $t['id']); // 分解商品id与规格
			
			if (isset($item[$id]) && $item[$id]) {
				$row = $item[$id];
			} else {
				$row = $item[$id] = $this->content_model->get_item_data($id); // 商品信息
			}

            // 商品不存在时跳过
			if (!$row) {
                continue;
            }

            // 验证商品规格
            if ($this->is_format
                && isset($row['format']['quantity'])
                && $row['format']['quantity']
                && !isset($row['format']['quantity'][$fid])) {
                continue;
            }
			
			$num = (int)$t['qty']; // 购买数量
			$price = $row['price']; // 商品价格
			$format = ''; // 商品规格信息
			$quantity = (int)$row['quantity']; // 商品库存数量
			$discount = 0; // 促销价格
			
			if ($this->is_format && $fid) {
				// 根据商品规格计算价格和库存
				$_fid = explode('-', $fid);
				foreach ($_fid as $f) {
					$format.= ','.$FORMAT[$row['catid']]['data'][$f]['name'];
				}
				$price = (float)$row['format']['price'][$fid];
				$quantity = (int)$row['format']['quantity'][$fid];
			}

            // 验证库存
			if ($this->is_num) {
				$num = $num > $quantity ? $quantity : $num;
                // 数量不足时跳过
				if ($num <= 0 ) {
                    continue;
                }
			}
			
			// 计算促销价格，判断促销时间
			if ($row['discount']
                && SYS_TIME >= $row['discount']['star']
                && SYS_TIME <= $row['discount']['end']) {
				$z = (float)$row['discount'][$this->member['mark']];
				$discount = $z ? $price * ($z/10) : 0;
				$total = $discount * $num;
			} else {
				$total = $price * $num; // 当前商品的总价格
			}
			
			$amount+= $total; // 整个订单的总价格
			
			$list[$t['id']] = array(
				'id' => $id,
				'key' => $key,
				'num' => $num,
				'fid' => $fid ? $fid: 0,
				'url' => $row['url'],
				'catid' => $row['catid'],
				'title' => $row['title'],
				'thumb' => $row['thumb'],
				'price' => $price,
				'total' => $total,
				'format' => trim($format, ','),
				'_format' => $row['format'],
				'freight' => isset($row['freight']) ? dr_string2array($row['freight']) : NULL,
				'tableid' => $row['tableid'],
				'quantity' => $quantity,
				'discount' => $discount,
			);
		}

        if (!$list) {
            $this->_del_cart();
        }
		
		return array($list, $amount);
	}
	
	/**
     * 购物车
     */
    protected function _home_cart() {
	
		$list = array();
		$data = $this->cart->contents();
		
		if (IS_POST) {
			// 生成订单信息
			$url = dr_url('order/buy');
			$fid = $iid = $num = array();
			$post = $this->input->post('key');
			foreach ($post as $key => $_num) {
				list($_iid, $_fid) = explode('_', $data[$key]['id']);
				if ($_iid) {
					$iid[] = $_iid;
					$fid[] = $_fid;
					$num[] = $_num;
				}
			}
			$url.= '&iid='.@implode(',', $iid).'&fid='.@implode(',', $fid).'&num='.@implode(',', $num);
			header('Location: '.$url);
			exit;
		}

		if ($data) {
            list($list, $amount) = $this->_format_item($data);
        }
		
		$this->template->assign(array(
			'list' => $list,
			'amount' => number_format($amount, 2),
			'meta_title' => lang('my-00').(SITE_SEOJOIN ? SITE_SEOJOIN : '_').MODULE_NAME,
		));
        $this->template->display('cart.html');
    }
	
	/**
	 * 确认订单
	 */
	protected function _buy() {
	
		// 登录验证
    	if (!$this->uid) {
            $this->msg(lang('m-039'), MEMBER_URL.SELF.'?c=login&m=index&backurl='.urlencode(dr_now_url()));
        }
		
		// 分析url参数
		$iid = $this->input->get('iid');
		$fid = $this->input->get('fid');
		$num = $this->input->get('num');
		if (!$iid) {
            $this->member_msg(lang('my-01'));
        }
		if ($this->is_num && !$num) {
            $this->member_msg(lang('my-02'));
        }
		
		// 组装订单数据
		$_iid = explode(',', $iid);
		$_num = explode(',', $num);
		$_fid = $fid ? explode(',', $fid) : 0;
		$data = array();
		foreach ($_iid as $key => $id) {
			if ($id) {
				$data[$key] = array(
                    // id标识由【商品id_规格参数】组成
					'id' => $id.(isset($_fid[$key]) && $_fid[$key] ? '_'.$_fid[$key] : '_0'),
					'qty' => max((int)$_num[$key], 1), // 购买数量
				);
			}
		}
		list($item, $amount) = $this->_format_item($data);
		
		if (!$item) {
            $this->member_msg(lang('my-03'));
        } // 数量不足时提示
		
		// 按卖家计算运单及总价格
		$list = $jsonp = array();
		foreach ($item as $i => $t) {
			$total = ($t['discount'] ? $t['discount'] : $t['price']) * $t['num']; // 计算订单总价格
			$item[$i]['total'] = $total;
			$jsonp[$i] = array(
				'num' => $t['num'],
				'total' => $total,
				'price' => $t['price'],
				'freight' => $t['freight'],
				'discount' => $t['discount'],
			);
			$list[] = $item[$i];
		}
		
		// 付款方式
		$paytype = $this->order_model->get_pay_type();
		
		// 提交订单
		if (IS_POST) {

            // 验证码验证
			if ($this->mconfig['code'] && !$this->check_captcha('code')) {
                $this->member_msg(lang('m-000'));
            }

            $pay = $this->input->post('pay');
			$post = $this->input->post('data');
			$ptid = (int)$post['ptid'];
			$score = (int)$post['score'];

            // 付款方式不存在
			if (!$ptid || !isset($paytype[$ptid])) {
                $this->member_msg(lang('my-42'));
            }

            // 没有选择在线支付方式
            if ($ptid == 2 && !$pay) {
                $this->member_msg(lang('my-52'));
            }
			
			// 支持运费时才验证收货地址
			if ($this->is_freight) {
				if (!$post['address']) {
                    $this->member_msg(lang('my-05'));
                } // 收货地址不存在
				$address = $this->db
								->where('id', (int)$post['address'])
								->where('uid', $this->uid)
								->get('member_address')
								->row_array();
				if (!$address) {
                    $this->member_msg(lang('my-05'));
                } // 收货地址不存在
			}
			
			// 重新计算总价
			list($price, $total, $freight) = $this->order_model->get_price($jsonp, $score);
			
			// 更新至订单表中
			$id = $this->order_model->add_order(array(
				'uid' => $this->uid,
				'ptid' => $ptid,
				'city' => isset($address['city']) ? $address['city'] : '',
				'name' => isset($address['name']) ? $address['name'] : '',
				'score' => $score,
				'price' => $total,
				'items' => dr_array2string($list),
				'phone' => isset($address['phone']) ? $address['phone'] : '',
				'gbook' => isset($post['gbook']) ? $post['gbook'] : '',
				'status' => 1,
				'zipcode' => isset($address['zipcode']) ? $address['zipcode'] : '',
				'address' => isset($address['address']) ? $address['address'] : '',
				'freight' => $freight,
				'sendnote' => '',
				'sendtime' => 0,
				'username' => $this->member['username'],
				'inputtime' => SYS_TIME,
				'successtime' => 0,
			));
			if (!$id) {
                $this->member_msg(lang('my-06'));
            } // 订购失败
			
			// 清空购物车
			$this->cart->destroy();
			
			// 付款
			list($msg, $url, $code) = $this->order_model->pay($ptid, $id, $total, $pay);
			// 
			$this->member_msg($msg, $url, $code);
		} else {
            $data = $this->get_cache('member', 'setting', 'pay');
            if ($data) {
                foreach ($data as $i => $t) {
                    if (!$t['use'] || !is_dir(FCPATH.'member/pay/'.$i.'/')) {
                        unset($data[$i]);
                    }
                }
            }
			$this->template->assign(array(
				'list' => $list,
				'jsonp' => dr_array2string($jsonp),
                'online' => $data,
				'iscode' => $this->mconfig['code'],
				'paytype' => $paytype,
				'address' => $this->is_freight ? $this->db->where('uid', $this->uid)->order_by('default desc')->get('member_address')->result_array() : NULL,
				'meta_title' => lang('my-07').(SITE_SEOJOIN ? SITE_SEOJOIN : '_').MODULE_NAME,
			));
			$this->template->display('buy.html');
		}
	}
	
	// 会员中心记录  
	protected function _member($type) {

        // 执行关闭过期订单操作 
        $this->order_model->close_order();
		// 按用户分类查询
		$this->link->where('uid', $this->uid);
		// id筛选
		$id = (int)$this->input->get('id');
		if ($id) {
			$this->link->where('id', $id);
		} else {
			// 时间筛选
			switch ($this->input->get('where')) {
				case 1: // 一月内
					$this->link->where('inputtime BETWEEN '.strtotime('-30 day').' AND '. SYS_TIME);
					break;
				case 2: // 半年内
					$this->link->where('inputtime BETWEEN '.strtotime('-180 day').' AND '. SYS_TIME);
					break;
				case 3: // 一年内
					$this->link->where('inputtime BETWEEN '.strtotime('-365 day').' AND '. SYS_TIME);
					break;
				case 4: // 三年内
					$this->link->where('inputtime BETWEEN '.strtotime('-1000 day').' AND '. SYS_TIME);
					break;
				default: // 默认一周内
					$this->link->where('inputtime BETWEEN '.strtotime('-7 day').' AND '. SYS_TIME);
					break;
			}
			// 状态筛选
			if ($this->input->get('status')) {
                $this->link->where('status', (int)$this->input->get('status'));
            }
			// 名称筛选
			if ($this->input->get('name')) {
                $this->link->where('username', $this->input->get('name'));
            }
		}
		$this->link->order_by('inputtime DESC');
		if ($this->input->get('action') == 'more') { // ajax更多数据
			$page = max((int)$this->input->get('page'), 1);
			$data = $this->link
						 ->limit($this->pagesize, $this->pagesize * ($page - 1))
						 ->get($this->order_model->tablename)
						 ->result_array();
			if (!$data) {
                exit('null');
            }

			$this->template->assign('list', $data);
			$this->template->display('order_data.html');
		} else {
			$this->template->assign(array(
				'type' => $type,
				'list' => $this->link
							   ->limit($this->pagesize)
							   ->get($this->order_model->tablename)
							   ->result_array(),
                'days' => (int)$this->mconfig['day'],
				'method' => $this->router->method,
				'paytype' => $this->order_model->get_pay_type(),
				'moreurl' => 'index.php?s='.APP_DIR.'&c='.$this->router->class.'&m='.$this->router->method.'&action=more',
                'meta_name' => $type ? lang('my-08') : lang('my-09'),
			));

			$this->template->display('order_index.html');
		}
	}
	
	//买家预约
	protected function _make($type) {

        // 执行关闭过期订单操作 
        $this->order_model->close_order();
		// 按用户分类查询
		$this->link->where('uid', $this->uid);
		// id筛选
		$id = (int)$this->input->get('id');
		if ($id) {
			$this->link->where('id', $id);
		} else {
			// 时间筛选
			switch ($this->input->get('where')) {
				case 1: // 一月内
					$this->link->where('inputtime BETWEEN '.strtotime('-30 day').' AND '. SYS_TIME);
					break;
				case 2: // 半年内
					$this->link->where('inputtime BETWEEN '.strtotime('-180 day').' AND '. SYS_TIME);
					break;
				case 3: // 一年内
					$this->link->where('inputtime BETWEEN '.strtotime('-365 day').' AND '. SYS_TIME);
					break;
				case 4: // 三年内
					$this->link->where('inputtime BETWEEN '.strtotime('-1000 day').' AND '. SYS_TIME);
					break;
				default: // 默认一周内
					$this->link->where('inputtime BETWEEN '.strtotime('-7 day').' AND '. SYS_TIME);
					break;
			}
			// 状态筛选
			if ($this->input->get('status')) {
                $this->link->where('status', (int)$this->input->get('status'));
            }
			// 名称筛选
			if ($this->input->get('name')) {
                $this->link->where('username', $this->input->get('name'));
            }
		}
		$this->link->order_by('inputtime DESC');
		if ($this->input->get('action') == 'more') { // ajax更多数据
			$page = max((int)$this->input->get('page'), 1);
			$data = $this->link
						 ->limit($this->pagesize, $this->pagesize * ($page - 1))
						 ->get($this->order_model->tablename)
						 ->result_array();
			if (!$data) {
                exit('null');
            }

			$this->template->assign('list', $data);
			$this->template->display('order_data.html');
		} else {
			$this->template->assign(array(
				'type' => $type,
				'list' => $this->link
							   ->limit($this->pagesize)
							   ->get($this->order_model->tablename)
							   ->result_array(),
                'days' => (int)$this->mconfig['day'],
				'method' => $this->router->method,
				'paytype' => $this->order_model->get_pay_type(),
				'moreurl' => 'index.php?s='.APP_DIR.'&c='.$this->router->class.'&m='.$this->router->method.'&action=more',
                'meta_name' => $type ? lang('my-08') : lang('my-09'),
			));

			$this->template->display('order_make.html');
		}
	}

	// 买家付款
	protected function _member_pay() {
		
		$id = (int)$this->input->get('id');
		$data = $this->order_model->get_order($id);
		if (!$data) {
            $this->member_msg(lang('my-10'));
        }
		
		if ($data['uid'] != $this->uid) {
            $this->member_msg(lang('my-11'));
        }
		if ($data['status'] != 1) {
            $this->member_msg(lang('my-12'));
        }

        $this->load->model('pay_model');
        if ($data['ptid'] == 2) {
            $pay = $this->db
                        ->where('uid', $this->uid)
                        ->where('order', $id)
                        ->get('member_paylog_'.(int)substr((string)$this->uid, -1, 1))
                        ->row_array();
            if ($url = $this->pay_model->add_for_online($pay['type'], $data['price'], APP_DIR, $id)) {
                $this->msg(lang('my-53').'<div style="display:none">'.$url.'</div>', strpos($url, 'http') === 0 ? $url : 'javascript:;', 2, 0);
            } else {
                $this->msg(lang('m-173'));
            }
        } else {
            // 在余额中扣除
            if ($data['price'] > $this->member['money']) {
                $this->msg(lang('my-13'), MEMBER_URL.'index.php?c=pay&m=add&money='.($data['price'] - $this->member['money']), 1);
            }
            $this->pay_model->add_for_buy($data['price'], array($id));
            $this->order_model->order_success($id, 1);
            $this->msg(lang('my-14'), MEMBER_URL.'index.php?s='.APP_DIR.'&c=order&m=show&id='.$id, 1);
        }

	}
	
	// 买家确认收货
	protected function _member_confirm() {
	
		$id = (int)$this->input->get('id');
		$data = $this->order_model->get_order($id);
		if (!$data) {
            $this->member_msg(lang('my-10'));
        }
		
		if ($data['ptid'] == 3) {
            $this->member_msg(lang('my-49'));
        }
		if ($data['uid'] != $this->uid) {
            $this->member_msg(lang('my-11'));
        }
		if ($data['status'] != 3) {
            $this->member_msg(lang('my-12'));
        }
		
		$this->order_model->order_success($id, 3);
		$this->msg(lang('my-15'), MEMBER_URL.'index.php?s='.APP_DIR.'&c=order&m=show&id='.$id, 1);
	}

    // 买家申请退款
    protected function _member_refund() {
    
        $id = (int)$this->input->get('id');

        $data = $this->order_model->get_order($id);
       
        if (!$data) {
            $this->member_msg(lang('my-10'));
        }
        
        if ($data['ptid'] == 3) {
            $this->member_msg(lang('my-49'));
            echo "1";
            die;
        }
        if ($data['uid'] != $this->uid) {
            $this->member_msg(lang('my-11'));
             echo "2";
              die;
        }
        if ($data['status'] != 3) {
            $this->member_msg(lang('my-12'));
             echo "3"; 
              die;
        }
        
        $this->order_model->order_success($id, 4);
        $this->msg(lang('my-115'), MEMBER_URL.'index.php?s='.APP_DIR.'&c=order&m=index');
    }

	
	// 订单详情
	protected function _member_show() {

        // 执行关闭过期订单操作
        $this->order_model->close_order();
        // 查询订单信息
		$id = (int)$this->input->get('id');
		$data = $this->order_model->get_order($id);
		if (!$data) {
            $this->member_msg(lang('my-10'));
        }
		
		// 订单信息只能相关的买家才能查看
		if (!IS_ADMIN && $data['uid'] != $this->uid) {
            $this->member_msg(lang('my-11'));
        }
		
		$data['sendnote'] = $data['sendnote'] ? dr_string2array($data['sendnote']) : $data['sendnote'];
		
		if (IS_ADMIN) {
			$kds = $this->link
						->where('name', 'expresses')
						->limit(1)
						->get(SITE_ID.'_'.APP_DIR.'_config')
						->row_array();
			$kds = dr_string2array($kds['value']);
			$kdlist = array('null' => lang('my-16'));
			$expresses = $this->order_model->get_expresses();
			if ($kds['list']) {
				$temp = explode(',', $kds['list']);
				foreach ($temp as $i) {
					$kdlist[$i] = $expresses[$i];
				}
			}
			if (IS_POST) {
				if ($data['status'] == 2) {
					$action = $this->input->post('action');
					if ($action == 1) {
						// 后台发货处理
						$post = $this->input->post('data');
						$post['name'] = $kdlist[$post['id']];
						$this->link->where('id', $id)->update($this->order_model->tablename, array(
							'sendtime' => SYS_TIME,
							'sendnote' => dr_array2string($post),
						));
						$data['status'] = 3;
						$data['sendtime'] = SYS_TIME;
						$data['sendnote'] = $post;
						$this->order_model->order_success($id, 2);
						// 通知买家
						$this->member_model->add_notice($data['uid'], 3, dr_lang('my-40', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
					} elseif ($action == 2) {
						// 后台退款处理
						if ($data['ptid'] != 3) {
							// 在线付款和余额付款都退回到账号中
							if ($data['score']) {
								// 虚拟币
								$this->member_model->update_score(1, $data['uid'], $data['score'], '', dr_lang('my-47', strtoupper(APP_DIR).'-'.$id));
							}
							if ($data['price']) {
								// money
								$this->load->model('pay_model');
								$this->pay_model->add($data['uid'], $data['price'], dr_lang('my-47', strtoupper(APP_DIR).'-'.$id));
							}
                            // 通知买家
                            $this->member_model->add_notice($data['uid'], 3, dr_lang('my-48', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                        } else {
                            // 通知买家
                            $this->member_model->add_notice($data['uid'], 3, dr_lang('my-45', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                        }
						// 后台关闭订单
						$this->link
                             ->where('id', $id)
                             ->update($this->order_model->tablename, array('status' => 0));
						$this->admin_msg(lang('000'), dr_url(APP_DIR.'/order/index'), 1);
					}
				} elseif ($data['status'] == 1) {
					$action = $this->input->post('action');
					if ($action == 1) {
						// 后台修改价格
						$price = $data['price'];
						$data['price'] = $this->input->post('price');
						if ($price != $data['price']) {
							$this->link->where('id', $id)->update($this->order_model->tablename, array(
								'price' => $data['price']
							));
							// 通知买家
							$this->member_model->add_notice($data['uid'], 3, dr_lang('my-39', $this->member['username'], strtoupper(APP_DIR).'-'.$id, $price, $data['price']));
						}
					} elseif ($action == 2) {
						// 后台设置为已付款
						$this->link
                             ->where('id', $id)
                             ->update($this->order_model->tablename, array('status' => 2));
						$data['status'] = 2;
						// 通知买家
						$this->member_model->add_notice($data['uid'], 3, dr_lang('my-19', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
					} elseif ($action == 3) {
						// 后台关闭订单
						$this->link
                             ->where('id', $id)
                             ->update($this->order_model->tablename, array('status' => 0));
						$data['status'] = 0;
						// 通知买家 
						$this->member_model->add_notice($data['uid'], 3, dr_lang('my-45', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
					} elseif ($action == 4) {
						// 后台删除订单
						$this->link
                             ->where('id', $id)
                             ->delete($this->order_model->tablename);
						// 通知买家
						$this->member_model->add_notice($data['uid'], 3, dr_lang('my-46', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
						$this->admin_msg(lang('000'), dr_url(APP_DIR.'/order/index'), 1);
					}
				} elseif ($data['status'] == 0) {
					// 后台删除订单
					$this->link
                         ->where('id', $id)
                         ->delete($this->order_model->tablename);
					// 通知买家
					$this->member_model->add_notice($data['uid'], 3, dr_lang('my-46', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
					$this->admin_msg(lang('000'), dr_url(APP_DIR.'/order/index'), 1);
				} elseif ($data['status'] == 3) {
					// 后台确认收货
					$this->order_model->order_success($id, 3);
					$data['status'] = 4;
					$data['successtime'] = SYS_TIME;
					// 通知买家
					$this->member_model->add_notice($data['uid'], 3, dr_lang('my-18', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
				}
			}
			
			$review = FALSE;
			if ($this->mconfig['isreview']) {
				foreach ($data['items'] as $i => $t) {
					// 查询对应的评论数据
					$index = $this->link
								  ->where('oid', (int)$id)
								  ->where('uid', (int)$this->uid)
								  ->where('iid', (int)$t['id'])
								  ->where('fid', (int)$t['fid'])
								  ->limit(1)
								  ->get($this->order_model->indexname)
								  ->row_array();
					if ($index) {
						$rdata = $this->link
									  ->where('id', (int)$index['id'])
									  ->limit(1)
									  ->get($this->order_model->reviewname)
									  ->row_array();
						// 评论信息
						$review = TRUE;
						$data['items'][$i]['value'] = dr_string2array($rdata['value']);
						$data['items'][$i]['avgsort'] = $rdata['avgsort'];
						$data['items'][$i]['content'] = $rdata['content'];
					}
				}
			}
			
			$this->template->assign(array(
                'kdlist' => $kdlist,
				'review' => $review,
				'option' => @explode(PHP_EOL, $this->mconfig['review']), // 配置文件中的点评选项
			));
		}
		 
		$this->template->assign(array(
			'id' => $id,
			'data' => $data,
            'days' => (int)$this->mconfig['day'],
			'paytype' => $this->order_model->get_pay_type(),
			'meta_name' => lang('my-20'),
		));
		$this->template->display('order_show.html');
	}
	


    // 打印订单详情
    protected function _member_print() {

        // 执行关闭过期订单操作
        $this->order_model->close_order();
        // 查询订单信息
        $id = (int)$this->input->get('id');
        $data = $this->order_model->get_order($id);
        if (!$data) {
            $this->member_msg(lang('my-10'));
        }
        
        // 订单信息只能相关的买家才能查看
        if (!IS_ADMIN && $data['uid'] != $this->uid) {
            $this->member_msg(lang('my-11'));
        }
        
        $data['sendnote'] = $data['sendnote'] ? dr_string2array($data['sendnote']) : $data['sendnote'];
        
        if (IS_ADMIN) {
            $kds = $this->link
                        ->where('name', 'expresses')
                        ->limit(1)
                        ->get(SITE_ID.'_'.APP_DIR.'_config')
                        ->row_array();
            $kds = dr_string2array($kds['value']);
            $kdlist = array('null' => lang('my-16'));
            $expresses = $this->order_model->get_expresses();
            if ($kds['list']) {
                $temp = explode(',', $kds['list']);
                foreach ($temp as $i) {
                    $kdlist[$i] = $expresses[$i];
                }
            }
            if (IS_POST) {
                if ($data['status'] == 2) {
                    $action = $this->input->post('action');
                    if ($action == 1) {
                        // 后台发货处理
                        $post = $this->input->post('data');
                        $post['name'] = $kdlist[$post['id']];
                        $this->link->where('id', $id)->update($this->order_model->tablename, array(
                            'sendtime' => SYS_TIME,
                            'sendnote' => dr_array2string($post),
                        ));
                        $data['status'] = 3;
                        $data['sendtime'] = SYS_TIME;
                        $data['sendnote'] = $post;
                        $this->order_model->order_success($id, 2);
                        // 通知买家
                        $this->member_model->add_notice($data['uid'], 3, dr_lang('my-40', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                    } elseif ($action == 2) {
                        // 后台退款处理
                        if ($data['ptid'] != 3) {
                            // 在线付款和余额付款都退回到账号中
                            if ($data['score']) {
                                // 虚拟币
                                $this->member_model->update_score(1, $data['uid'], $data['score'], '', dr_lang('my-47', strtoupper(APP_DIR).'-'.$id));
                            }
                            if ($data['price']) {
                                // money
                                $this->load->model('pay_model');
                                $this->pay_model->add($data['uid'], $data['price'], dr_lang('my-47', strtoupper(APP_DIR).'-'.$id));
                            }
                            // 通知买家
                            $this->member_model->add_notice($data['uid'], 3, dr_lang('my-48', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                        } else {
                            // 通知买家
                            $this->member_model->add_notice($data['uid'], 3, dr_lang('my-45', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                        }
                        // 后台关闭订单
                        $this->link
                             ->where('id', $id)
                             ->update($this->order_model->tablename, array('status' => 0));
                        $this->admin_msg(lang('000'), dr_url(APP_DIR.'/order/index'), 1);
                    }
                } elseif ($data['status'] == 1) {
                    $action = $this->input->post('action');
                    if ($action == 1) {
                        // 后台修改价格
                        $price = $data['price'];
                        $data['price'] = $this->input->post('price');
                        if ($price != $data['price']) {
                            $this->link->where('id', $id)->update($this->order_model->tablename, array(
                                'price' => $data['price']
                            ));
                            // 通知买家
                            $this->member_model->add_notice($data['uid'], 3, dr_lang('my-39', $this->member['username'], strtoupper(APP_DIR).'-'.$id, $price, $data['price']));
                        }
                    } elseif ($action == 2) {
                        // 后台设置为已付款
                        $this->link
                             ->where('id', $id)
                             ->update($this->order_model->tablename, array('status' => 2));
                        $data['status'] = 2;
                        // 通知买家
                        $this->member_model->add_notice($data['uid'], 3, dr_lang('my-19', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                    } elseif ($action == 3) {
                        // 后台关闭订单
                        $this->link
                             ->where('id', $id)
                             ->update($this->order_model->tablename, array('status' => 0));
                        $data['status'] = 0;
                        // 通知买家
                        $this->member_model->add_notice($data['uid'], 3, dr_lang('my-45', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                    } elseif ($action == 4) {
                        // 后台删除订单
                        $this->link
                             ->where('id', $id)
                             ->delete($this->order_model->tablename);
                        // 通知买家
                        $this->member_model->add_notice($data['uid'], 3, dr_lang('my-46', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                        $this->admin_msg(lang('000'), dr_url(APP_DIR.'/order/index'), 1);
                    }
                } elseif ($data['status'] == 0) {
                    // 后台删除订单
                    $this->link
                         ->where('id', $id)
                         ->delete($this->order_model->tablename);
                    // 通知买家
                    $this->member_model->add_notice($data['uid'], 3, dr_lang('my-46', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                    $this->admin_msg(lang('000'), dr_url(APP_DIR.'/order/index'), 1);
                } elseif ($data['status'] == 3) {
                    // 后台确认收货
                    $this->order_model->order_success($id, 3);
                    $data['status'] = 4;
                    $data['successtime'] = SYS_TIME;
                    // 通知买家
                    $this->member_model->add_notice($data['uid'], 3, dr_lang('my-18', $this->member['username'], strtoupper(APP_DIR).'-'.$id));
                }
            }
            
            $review = FALSE;
            if ($this->mconfig['isreview']) {
                foreach ($data['items'] as $i => $t) {
                    // 查询对应的评论数据
                    $index = $this->link
                                  ->where('oid', (int)$id)
                                  ->where('uid', (int)$this->uid)
                                  ->where('iid', (int)$t['id'])
                                  ->where('fid', (int)$t['fid'])
                                  ->limit(1)
                                  ->get($this->order_model->indexname)
                                  ->row_array();
                    if ($index) {
                        $rdata = $this->link
                                      ->where('id', (int)$index['id'])
                                      ->limit(1)
                                      ->get($this->order_model->reviewname)
                                      ->row_array();
                        // 评论信息
                        $review = TRUE;
                        $data['items'][$i]['value'] = dr_string2array($rdata['value']);
                        $data['items'][$i]['avgsort'] = $rdata['avgsort'];
                        $data['items'][$i]['content'] = $rdata['content'];
                    }
                }
            }
            
            $this->template->assign(array(
                'kdlist' => $kdlist,
                'review' => $review,
                'option' => @explode(PHP_EOL, $this->mconfig['review']), // 配置文件中的点评选项
            ));
        }
         
        $this->template->assign(array(
            'id' => $id,
            'data' => $data,
            'days' => (int)$this->mconfig['day'],
            'paytype' => $this->order_model->get_pay_type(),
            'meta_name' => lang('my-20'),
        ));
        $this->template->display('order_print.html');
    }



	// 订单商品是否评价
	protected function _member_isreview() {
		
		if (!$this->mconfig['isreview']) {
            exit('');
        } // 评论关闭
		
		#$fid = $this->input->get('fid');
		$oid = (int)$this->input->get('oid');
		#$iid = (int)$this->input->get('iid');
		
		$data = $this->link
					 ->where('oid', $oid)
					 ->where('uid', $this->uid)
					 ->select('review,id')
					 ->limit(1)
					 ->get($this->order_model->indexname)
					 ->row_array();
		if (!$data) {
            exit('');
        }
		
		if (IS_ADMIN) {
			echo 'document.write(\'' . ($data['review'] ? lang('my-21') : '') . '\');';
		} else {
			$html = '<a href="'.dr_member_url(APP_DIR.'/order/review', array('id' => $oid)).'" target="_blank">'.($data['review'] ? lang('my-21') : lang('my-22')).'</a>';
			echo 'document.write(\'' . $html . '\');';
		}
		
		$this->output->enable_profiler(FALSE);
	}
	
	// 订单商品评价
	protected function _member_review() {
		
		if (!$this->mconfig['isreview']) {
            $this->member_msg(lang('my-51'));
        } // 评论关闭
		
		$id = (int)$this->input->get('id');
		$data = $this->order_model->get_item_review($id);
		if (!$data) {
            $this->msg(lang('my-10'));
        } // 订单不存在
		
		$error = '';
		$review = explode(PHP_EOL, $this->mconfig['review']); // 配置文件中的点评选项
		
		if (IS_POST) {
			$post = $this->input->post('data', TRUE);
			foreach ($data as $i => $t) {
				if ($t['review']) {
                    continue;
                }
				if ($post[$i]['content']) {
					$st = round(10/5); // 十分制
					$avgsort = 0; // 评价总分
					foreach ($review as $rid => $name) {
						$post[$i]['value'][$rid] = min(5, (int)$post[$i]['value'][$rid]);
						if (!$post[$i]['value'][$rid]) {
							$error = dr_lang('my-23', $name);
							break;
						}
						$avgsort+= round($post[$i]['value'][$rid] / 1 * $st, 1);
					}
					if (!$error) {
						$avgsort = round($avgsort/count($review), 1); // 评价总分
						$this->link->insert($this->order_model->reviewname, array(
							'id' => $t['index'],
							'uid' => $this->uid,
							'iid' => $t['iid'],
							'item' => $t['format'],
							'value' => dr_array2string($post[$i]['value']),
							'author' => $this->member['username'],
							'avgsort' => $avgsort,
							'content' => $post[$i]['content'],
							'inputtime' => SYS_TIME,
						));
						// 更新索引表的标示
						$this->link
                             ->where('id', $t['index'])
                             ->update($this->order_model->indexname, array('review' => 1));
						// 统计商品的总分
						$this->order_model->update_review($t['iid']);
					}
				} else {
					$error = lang('my-25');
				}
			}
			if (!$error) {
                $this->member_msg(lang('my-24'), dr_url(APP_DIR.'/order/review', array('id' => $id)), 1);
            }
		}
		
		$this->template->assign(array(
			'data' => $data,
			'error' => $error,
			'review' => $review,
			'meta_name' => lang('my-26')
		));
		$this->template->display('order_review.html');
	}
	
}