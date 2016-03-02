<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since		version 2.3.1
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
	
class Pay_model extends CI_Model{

	public $cache_file;
    
	/**
	 * 初始化
	 */
    public function __construct() {
        parent::__construct();
    }
	
	/**
	 * 条件查询
	 *
	 * @param	object	$select	查询对象
	 * @param	array	$param	条件参数
	 * @return	array	
	 */
	private function _card_where(&$select, $param) {
	
		$_param = array();
		$this->cache_file = md5($this->duri->uri(1).$this->uid.SITE_ID.$this->input->ip_address().$this->input->user_agent()); // 缓存文件名称
		
		// 存在POST提交时，重新生成缓存文件
		if (IS_POST) {
			$data = $this->input->post('data');
			$this->cache->file->save($this->cache_file, $data, 3600);
			$param['search'] = 1;
		}
		
		// 存在search参数时，读取缓存文件
		if ($param['search'] == 1) {
			$data = $this->cache->file->get($this->cache_file);
			$_param['search'] = 1;
			if ($data['card']) {
				$select->where('card', $data['card']);
			}
			if (strlen($data['status']) > 0 && !$data['status']) {
				$select->where('uid=0');
			} elseif ($data['status']) {
				$select->where('uid>0');
			}
			if ($data['username']) {
				$select->where('username', $data['username']);
			}
		}
		
		return $_param;
	}
	
	/**
	 * 数据分页显示
	 *
	 * @param	array	$param	条件参数
	 * @param	intval	$page	页数
	 * @param	intval	$total	总数据
	 * @return	array	
	 */
	public function card_limit_page($param, $page, $total) {
        
		if (!$total) {
			$select	= $this->db->select('count(*) as total');
			$this->_card_where($select, $param);
			$data = $select->get('member_paycard')->row_array();
			unset($select);
			$total = (int)$data['total'];
			if (!$total) return array(array(), array('total' => 0));
		}
		
		$select	= $this->db->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1));
		$_param	= $this->_card_where($select, $param);
		$data = $select->order_by('inputtime DESC')
					   ->get('member_paycard')
					   ->result_array();
		$_param['total'] = $total;
		
		return array($data, $_param);
	}
	
	/*
	 * 条件查询
	 *
	 * @param	object	$select	查询对象
	 * @param	array	$param	条件参数
	 * @return	array	
	 */
	private function _where(&$select, $param) {
	
		$_param = array();
		$this->cache_file = md5($this->duri->uri(1).$this->uid.SITE_ID.$this->input->ip_address().$this->input->user_agent()); // 缓存文件名称
		
		// 存在POST提交时，重新生成缓存文件
		if (IS_POST) {
			$data = $this->input->post('data');
			$this->cache->file->save($this->cache_file, $data, 3600);
			$param['search'] = 1;
		}
		
		// 存在search参数时，读取缓存文件
		if ($param['search'] == 1) {
			$data = $this->cache->file->get($this->cache_file);
			$_param['search'] = 1;
			if (isset($data['start']) && $data['start'] && $data['start'] != $data['end']) {
				$select->where('inputtime BETWEEN '.$data['start'].' AND '. $data['end']);
			}
			if (strlen($data['status']) > 0) {
				$select->where('status', (int)$data['status']);
			}
		}
		
		$select->where('uid', $param['uid']);
		$_param['uid'] = $data['uid'];
		
		return $_param;
	}
	
	/*
	 * 数据分页显示
	 *
	 * @param	array	$param	条件参数
	 * @param	intval	$page	页数
	 * @param	intval	$total	总数据
	 * @return	array	
	 */
	public function limit_page($param, $page, $total) {
        $tableid = (int)substr((string)$param['uid'], -1, 1);
		if (!$total) {
			$select	= $this->db->select('count(*) as total');
			$this->_where($select, $param);
			$data = $select->get('member_paylog_'.$tableid)->row_array();
			unset($select);
			$total = (int)$data['total'];
			if (!$total) return array(array(), array('total' => 0));
		}
		
		$select	= $this->db->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1));
		$_param	= $this->_where($select, $param);
		$data = $select->order_by('inputtime DESC')
					   ->get('member_paylog_'.$tableid)
					   ->result_array();
		$_param['total'] = $total;
		
		return array($data, $_param);
	}
	
	// 购物消费
	public function add_for_buy($money, $order, $module = APP_DIR) {
		
			if (!$money || !$order) {
                return FALSE;
            }
		
			// 将变动金额冻结
			$this->db
				 ->where('uid', $this->uid)
				 ->set('money', 'money-'.$money, FALSE)
				 ->set('spend', 'spend+'.$money, FALSE)
				 ->update('member');

			$note = '<a href="'.MEMBER_URL.'index.php?s='.$module.'&c=order&m=show&id='.$order.'" target="_blank">'.$order.'</a>';

			// 更新记录表 
			$this->db->insert('member_paylog_'.$this->member['tableid'], array(
				'uid' => $this->uid,
				'type' => 0,
				'note' => 'lang,m-200,'.$note,
				'value' => -1 * $money,
				'order' => $order,
				'status' => 1,
				'module' => APP_DIR,
				'inputtime' => SYS_TIME
			));
			
			return TRUE;
	}
	
	// 充值
	public function add($uid, $value, $note) {
	
		if (!$uid || !$value) {
            return NULL;
        }
		
		// 更新RMB
        $db = $this->db->where('uid', $uid);
        if ($value > 0) {
            $db->set('money', 'money+'.$value, FALSE);
        } else {
            $db->set('money', 'money-'.abs($value), FALSE);
            $db->set('spend', 'spend+'.abs($value), FALSE);
        }
		$db->update('member');
        unset($db);
			 
		// 更新记录表 
		$this->db->insert('member_paylog_'.(int)substr((string)$uid, -1, 1), array(
			'uid' => $uid,
			'type' => 0,
			'note' => $note,
			'value' => $value,
			'order' => 0,
			'status' => 1,
			'module' => '',
			'inputtime' => SYS_TIME,
		));
	}
	
	// 卡密充值
	public function add_for_card($id, $money, $card) {
		
			if (!$id || $money < 0) {
                return NULL;
            }
		
			// 更新RMB
			$this->db
				 ->where('uid', $this->uid)
				 ->set('money', 'money+'.$money, FALSE)
				 ->update('member');
			
			// 更新记录表 
			$this->db->insert('member_paylog_'.$this->member['tableid'], array(
				'uid' => $this->uid,
				'type' => 0,
				'note' => 'lang,m-174,'.$card,
				'order' => 0,
				'value' => $money,
				'module' => '',
				'status' => 1,
				'inputtime' => SYS_TIME
			));
			
			// 更新卡密状态
			$this->db->where('id', $id)->update('member_paycard', array(
				'uid' => $this->uid,
				'usetime' => SYS_TIME,
				'username' => $this->member['username'],
			));
			
			return $money;
	}
	
	// 生成充值卡
	public function card($money, $endtime, $i) {
		
		if (!$money || !$endtime) {
            return NULL;
        }
		
		mt_srand((double)microtime() * (1000000 + $i));
		$data = array(
			'uid' => 0,
			'card' => date('Ys').strtoupper(substr(md5(uniqid()), rand(0, 20), 8)).mt_rand(100000, 999999),
			'money' => $money,
			'usetime' => 0,
			'endtime' => $endtime,
			'username' => '',
			'password' => mt_rand(100000, 999999),
			'inputtime' => SYS_TIME,
		);
		
		return $this->db->insert('member_paycard', $data) ? $data : NULL;
	}
	
	// 支付成功，更改状态
	public function pay_success($sn, $money, $note = '') {
		
		list($a, $id, $uid, $module, $order) = explode('-', $sn);
		if (!$id || !$uid) {
            return NULL;
        }
		
		// 查询支付记录 
		$tid = (int)substr((string)$uid, -1, 1);
		$data = $this->db
					 ->where('id', $id)
					 ->limit(1)
					 ->get('member_paylog_'.$tid)
					 ->row_array();
		if (!$data) {
            return NULL;
        }

		if ($data['status']) {
            return $data['module'];
        }

        $money = $money > 0 ? $money : $data['value'];
		
		// 标示支付订单成功
		$this->db->where('id', $id)->update('member_paylog_'.$tid, array('status' => 1, 'note' => $note));

        // 更新会员表金额
        $this->db
             ->where('uid', $uid)
             ->set('money', 'money+'.$money, FALSE)
             ->update('member');

        // 订单直接付款
		if ($data['module']) {
			require_once FCPATH.$data['module'].'/models/Order_model.php';
            $order = new Order_model();
            $order->payname = $this->db->dbprefix('member_paylog_'.$tid);
            $order->tablename = $this->db->dbprefix(SITE_ID.'_'.$data['module'].'_order');
            $order->order_success($data['order'], 1, $data['module']);
            $this->add_for_buy($money, $data['order'], $data['module']);
		}

        // 支付成功挂钩点
        $this->hooks->call_hook('pay_success', $data);

		return $data['module'];
	}
	
	// 在线充值
	public function add_for_online($pay, $money, $module = '', $order = 0) {
	
		if (!$pay || $money < 0) {
            return NULL;
        }
		
		// 更新记录表 
		$this->db->insert('member_paylog_'.$this->member['tableid'], array(
			'uid' => $this->uid,
			'note' => '',
			'type' => $pay,
			'value' => $money,
			'order' => $order,
			'status' => 0,
			'module' => $module,
			'inputtime' => SYS_TIME
		));
		
		$id = $this->db->insert_id();
		if (!$id) {
            return NULL;
        }

        if ($order) {
            $sn = 'FC-'.$id.'-'.$this->uid.'-'.strtoupper($module).'-'.$order;
            $title = dr_lang('m-180', strtoupper($module).'-'.$order);
        } else {
            $sn= 'FC-'.$id.'-'.$this->uid;
            $title = lang('m-179');
        }

        require_once FCPATH.'member/pay/'.$pay.'/pay.php';

		return $result;
	}
	
	// 在线付款
	public function pay_for_online($id) {
	
		if (!$id) {
            return NULL;
        }
		
		// 查询支付记录 
		$data = $this->db
					 ->where('id', $id)
					 ->where('uid', $this->uid)
					 ->where('status', 0)
					 ->select('value,type,order')
					 ->limit(1)
					 ->get('member_paylog_'.$this->member['tableid'])
					 ->row_array();
		if (!$data) {
            return NULL;
        }
		
		// 判断订单是否支付过，否则作废
		if ($data['order']) {
            $sn = 'FC-'.$id.'-'.$this->uid.'-'.strtoupper($data['module']).'-'.$data['order'];
			$title = dr_lang('m-180', strtoupper($data['module']).'-'.$data['order']);
		} else {
            $sn= 'FC-'.$id.'-'.$this->uid;
			$title = lang('m-179');
		}
		
		$money = $data['value'];
        require_once FCPATH.'member/pay/'.$data['type'].'/pay.php';

        return $result;
		//return method_exists($this, '_get_'.$data['type']) ? call_user_func_array(array($this, '_get_'.$data['type']), array($id, $data['value'], $title, $sn)) : '';
	}

}