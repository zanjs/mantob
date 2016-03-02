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

// 订单状态（0取消订单，1下单成功，2付款成功，3发货完成，4收货完成）
 
class Order_model extends CI_Model {

	public $link;
	public $payname; // 支付记录表名称
	public $tablename; // 订单表名称
	public $indexname; // 订单索引表名称

	/**
	 * 订单模型类
	 */
    public function __construct() {
        parent::__construct();
		$this->link = $this->site[SITE_ID];
		$this->payname = $this->db->dbprefix('member_paylog_'.(int)substr((string)$this->uid, -1, 1));
		$this->tablename = $this->db->dbprefix(SITE_ID.'_'.APP_DIR.'_order');
		$this->indexname = $this->tablename.'_index';
		$this->reviewname = $this->tablename.'_review';
    }

    // 未付款前关闭订单或自动关闭订单，status=0
    public function close_order() {

        $day = (int)$this->mconfig['day'];
        if (!$day) {
            return NULL;
        }
        // 计算出过期的时间戳
        $time = strtotime('- '.$day.'day');
        // 查询已经过期订单
        if (!IS_ADMIN) {
            $this->link->where('uid', $this->uid);
        }
        $this->link->where('(status=1 or (status=2 and ptid=3))');
        $this->link->where('inputtime<=', $time);
        $this->link->update($this->tablename, array('status' => 0));

    }

	// 获取支持快递接口列表
	public function get_expresses() {
		return array (
		  'aae' => 'AAE快递',
		  'anjie' => '安捷快递',
		  'anxinda' => '安信达快递',
		  'aramex' => 'Aramex国际快递',
		  'balunzhi' => '巴伦支',
		  'baotongda' => '宝通达',
		  'benteng' => '成都奔腾国际快递',
		  'cces' => 'CCES快递',
		  'changtong' => '长通物流',
		  'chengguang' => '程光快递',
		  'chengji' => '城际快递',
		  'chengshi100' => '城市100',
		  'chuanxi' => '传喜快递',
		  'chuanzhi' => '传志快递',
		  'chukouyi' => '出口易物流',
		  'citylink' => 'CityLinkExpress',
		  'coe' => '东方快递',
		  'cszx' => '城市之星',
		  'datian' => '大田物流',
		  'dayang' => '大洋物流快递',
		  'debang' => '德邦物流',
		  'dechuang' => '德创物流',
		  'dhl' => 'DHL快递',
		  'diantong' => '店通快递',
		  'dida' => '递达快递',
		  'dingdong' => '叮咚快递',
		  'disifang' => '递四方速递',
		  'dpex' => 'DPEX快递',
		  'dsu' => 'D速快递',
		  'ees' => '百福东方物流',
		  'ems' => 'EMS快递',
		  'fanyu' => '凡宇快递',
		  'fardar' => 'Fardar',
		  'fedex' => '国际Fedex',
		  'fedexcn' => 'Fedex国内',
		  'feibang' => '飞邦物流',
		  'feibao' => '飞豹快递',
		  'feihang' => '原飞航物流',
		  'feihu' => '飞狐快递',
		  'feite' => '飞特物流',
		  'feiyuan' => '飞远物流',
		  'fengda' => '丰达快递',
		  'fkd' => '飞康达快递',
		  'gdyz' => '广东邮政物流',
		  'gnxb' => '邮政国内小包',
		  'gongsuda' => '共速达物流|快递',
		  'guotong' => '国通快递',
		  'haihong' => '山东海红快递',
		  'haimeng' => '海盟速递',
		  'haosheng' => '昊盛物流',
		  'hebeijianhua' => '河北建华快递',
		  'henglu' => '恒路物流',
		  'huacheng' => '华诚物流',
		  'huahan' => '华翰物流',
		  'huaqi' => '华企快递',
		  'huaxialong' => '华夏龙物流',
		  'huayu' => '天地华宇物流',
		  'huiqiang' => '汇强快递',
		  'huitong' => '汇通快递',
		  'hwhq' => '海外环球快递',
		  'jiaji' => '佳吉快运',
		  'jiayi' => '佳怡物流',
		  'jiayunmei' => '加运美快递',
		  'jinda' => '金大物流',
		  'jingdong' => '京东快递',
		  'jingguang' => '京广快递',
		  'jinyue' => '晋越快递',
		  'jixianda' => '急先达物流',
		  'jldt' => '嘉里大通物流',
		  'kangli' => '康力物流',
		  'kcs' => '顺鑫(KCS)快递',
		  'kuaijie' => '快捷快递',
		  'kuanrong' => '宽容物流',
		  'kuayue' => '跨越快递',
		  'lejiedi' => '乐捷递快递',
		  'lianhaotong' => '联昊通快递',
		  'lijisong' => '成都立即送快递',
		  'longbang' => '龙邦快递',
		  'minbang' => '民邦快递',
		  'mingliang' => '明亮物流',
		  'minsheng' => '闽盛快递',
		  'nell' => '尼尔快递',
		  'nengda' => '港中能达快递',
		  'ocs' => 'OCS快递',
		  'pinganda' => '平安达',
		  'pingyou' => '中国邮政平邮',
		  'pinsu' => '品速心达快递',
		  'quanchen' => '全晨快递',
		  'quanfeng' => '全峰快递',
		  'quanjitong' => '全际通快递',
		  'quanritong' => '全日通快递',
		  'quanyi' => '全一快递',
		  'rpx' => 'RPX保时达',
		  'rufeng' => '如风达快递',
		  'saiaodi' => '赛澳递',
		  'santai' => '三态速递',
		  'scs' => '伟邦(SCS)快递',
		  'shengan' => '圣安物流',
		  'shengbang' => '晟邦物流',
		  'shengfeng' => '盛丰物流',
		  'shenghui' => '盛辉物流',
		  'shentong' => '申通快递（可能存在延迟）',
		  'shunfeng' => '顺丰快递',
		  'suchengzhaipei' => '速呈宅配',
		  'suijia' => '穗佳物流',
		  'sure' => '速尔快递',
		  'tiantian' => '天天快递',
		  'tnt' => 'TNT快递',
		  'tongcheng' => '通成物流',
		  'tonghe' => '通和天下物流',
		  'ups' => 'UPS快递',
		  'usps' => 'USPS快递',
		  'wanbo' => '万博快递',
		  'wanjia' => '万家物流',
		  'weitepai' => '微特派',
		  'xianglong' => '祥龙运通快递',
		  'xinbang' => '新邦物流',
		  'xinfeng' => '信丰快递',
		  'xingchengzhaipei' => '星程宅配快递',
		  'xiyoute' => '希优特快递',
		  'yad' => '源安达快递',
		  'yafeng' => '亚风快递',
		  'yibang' => '一邦快递',
		  'yinjie' => '银捷快递',
		  'yinsu' => '音素快运',
		  'yishunhang' => '亿顺航快递',
		  'yousu' => '优速快递',
		  'ytfh' => '北京一统飞鸿快递',
		  'yuancheng' => '远成物流',
		  'yuantong' => '圆通快递',
		  'yuanzhi' => '元智捷诚',
		  'yuefeng' => '越丰快递',
		  'yumeijie' => '誉美捷快递',
		  'yunda' => '韵达快递',
		  'yuntong' => '运通中港快递',
		  'yuxin' => '宇鑫物流',
		  'ywfex' => '源伟丰',
		  'zengyi' => '增益快递',
		  'zhaijisong' => '宅急送快递',
		  'zhengzhoujianhua' => '郑州建华快递',
		  'zhima' => '芝麻开门快递',
		  'zhongtian' => '济南中天万运',
		  'zhongtie' => '中铁快运',
		  'zhongtong' => '中通快递',
		  'zhongxinda' => '忠信达快递',
		  'zhongyou' => '中邮物流',
		);
	}
	
	public function kd_status($id, $sn) {
		$config = $this->link
					   ->where('name', 'expresses')
					   ->limit(1)
					   ->get(SITE_ID.'_'.APP_DIR.'_config')
					   ->row_array();
		$config = dr_string2array($config['value']);
		return str_replace(
			array('ickd_return', '<tr><th>时间</th><th>记录</th></tr>', '</table>'), 
			array('table_form', '', '<tr><td colspan="2" style="border:none;text-align:center;color:red;">以上部分信息来自于第三方</td></tr></table>'), 
			dr_catcher_data('http://api.ickd.cn/?id='.$config['id'].'&secret='.$config['secret'].'&com='.$id.'&nu='.$sn.'&type=html&encode=utf8&ord=asc')
		);
	}
	
	/**
	 * 订单支付
	 *
	 * @param	intval	$ptid		付款方式id
	 * @param	intval	$orderid	订单id字符串
	 * @param	intval	$total		付款总额
	 * @param	string	$pay		在线付款方式
	 * @return	array
	 */
	public function pay($ptid, $orderid, $total, $pay = '') {
	
		switch ($ptid) {
			case 1:	// 余额支付
				
				if ($this->member['money'] >= $total) {
					// 在余额中扣除
					$this->load->model('pay_model');
					$this->pay_model->add_for_buy($total, $orderid);
					$this->order_success($orderid, 1);
					return array(lang('my-14'), MEMBER_URL.'index.php?s='.APP_DIR.'&c=order', 1);
				} else {
					return array(lang('my-13'), MEMBER_URL.'index.php?c=pay&m=add&money='.($total - $this->member['money']), 1);
				}
				
				break;
			case 2: // 在线支付
                $this->load->model('pay_model');
                if ($url = $this->pay_model->add_for_online($pay, $total, APP_DIR, $orderid)) {
                    return array(lang('my-53').'<div style="display:none">'.$url.'</div>', strpos($url, 'http') === 0 ? $url : 'javascript:;', 1);
                } else {
                    return array(lang('m-173'));
                }
				break;
			case 3:	// 货到付款
				$this->link->where('id', $orderid)->update($this->tablename, array('status' => 2));
				return array(lang('my-44'), MEMBER_URL.'index.php?s='.APP_DIR.'&c=order', 1);
				break;
		}
		
		return array(lang('my-43'), '', 0);
	}
	
	// 付款方式
	public function get_pay_type() {
		
		$paytype = $this->link
						->where('name', 'paytype')
						->limit(1)
						->get(SITE_ID.'_'.APP_DIR.'_config')
						->row_array();
		$paytype = dr_string2array($paytype['value']);
		
		if (isset($paytype[1]) && $paytype[1]['use']) {
            $paytype[1]['color'] = 'green';
        }
		if (isset($paytype[2]) && $paytype[2]['use']) {
            $paytype[2]['color'] = 'blue';
        }
		if (isset($paytype[3]) && $paytype[3]['use']) {
            $paytype[3]['color'] = '#FF9933';
        }
		
		if (!$paytype[1]['use']) {
            unset($paytype[1]);
        }
		if (!$paytype[2]['use']) {
            unset($paytype[2]);
        }
		if (!$paytype[3]['use']) {
            unset($paytype[3]);
        }
		
		return $paytype;
	}
	
	// 商店配置
	public function get_config() {
		
		$data = $this->link
					 ->where('name', 'config')
					 ->limit(1)
					 ->get(SITE_ID.'_'.APP_DIR.'_config')
					 ->row_array();
		$data = dr_string2array($data['value']);
		
		return $data;
	}
	
	// 计算价格
	public function get_price($data, $score = 0) {
		
		$freight = $price = 0;
		
		foreach ($data as $t) {
			// 支持运费时才计算运费
			if ($t['freight'] && $t['freight']['type'] == 0) {
				// 按订单
				$freight+= $t['freight']['price'];
			} elseif ($t['freight'] && $t['freight']['type'] == 1) {
				// 按数量
				$freight+= $t['freight']['price'] * $t['num'];
			}
			// 计算总价格
			$price+= $t['total'];
		}
		
		// 计算虚拟币兑换
		if ($score) {
			$score = (int)abs($score);
			$score = $score > $this->member['score'] ? $this->member['score'] : $score;
			// 计算需要多少虚拟币
			$_temp = $price * SITE_CONVERT;
			$score = $_temp > $score ? $score : $_temp;
			// 计算虚拟币可兑换多少RMB
			$total = $price - $score/SITE_CONVERT;
		} else {
			$total = $price;
		}
		
		return array($price, $total, $freight);
	}
	
	/**
	 * 订单详情
	 *
	 * @param	intval	$id
	 * @return	array	$data
	 */
	public function get_order($id) {
	
		if (!$id) {
            return NULL;
        }
		
		$data = $this->link
					 ->where('id', $id)
					 ->limit(1)
					 ->get($this->tablename)
					 ->row_array();
		if (!$data) {
            return NULL;
        }
		
		$data['items'] = dr_string2array($data['items']);
		
		return $data;
	}
	
	/**
	 * 订单状态
	 *
	 * @param	intval	$id
	 * @param	array	$items
	 * @param	intval	$status
	 * @return	intval
	 */
	private function _get_order_status($id, $items, $status) {
		
		if ($status == 1) {
		
			switch ((int)$this->mconfig['status']) {
			
				case 0:
					return 2; // 按照正常流程，下一步：等待发货
				
				case -1:
					$this->order_success($id, 2); // 
					return 3;
				
				case 1:
					$this->order_success($id, 3); // 直接交易完成
					return 4;
			}
		}
		
		return $status + 1;
	}
	
	// 更新库存
	private function _update_quantity($items) {
		
		if (!$items) {
            return NULL;
        }
		
		foreach ($items as $item) {
			if ($item['fid'] && $item['_format']) {
				$_format = $item['_format'];
				$_format['quantity'][$item['fid']] -= $item['num'];
				$this->link
					 ->where('id', $item['id'])
					 ->update(SITE_ID.'_'.APP_DIR.'_data_'.$item['tableid'], array('format' => dr_array2string($_format)));
			} else {
				$this->link
					 ->where('id', $item['id'])
					 ->set('quantity', 'quantity-'.$item['num'], FALSE)
					 ->update(SITE_ID.'_'.APP_DIR);
			}
		}
	}
	
	/**
	 * 订单操作成功
	 *
	 * @param	intval	$id		订单id
	 * @param	intval	$status	当前状态代码
	 * @param	string	$module	模块目录
	 * @return	status
	 */
	public function order_success($id, $status, $module = APP_DIR) {
	
		if (!$id) {
            return NULL;
        }
		
		$data = $this->get_order($id);
		if (!$data) {
            return NULL;
        }

		switch ($status) {
			
			case 0: // 当前状态
				break;
				
			case 1: // 当前状态是1（下单成功）
                // 价格存在时进行扣减处理
				if ($data['price'] > 0) {
                    // 更新库存
					if ($this->is_quantity) {
                        $this->_update_quantity($data['items']);
                    }
					// 虚拟币扣减
					if ($data['score']) {
						$this->db // 更新虚拟币
							 ->set('score', 'score-'.abs($data['score']), FALSE)
							 ->where('uid', $data['uid'])
							 ->update('member');
						$this->db->insert('member_scorelog_'.$this->member['tableid'], array(
							'uid' => $data['uid'],
							'type' => 1,
							'mark' => '',
							'value' => -$data['score'],
							'note' => '购物消费，订单：<a href="index.php?s='.$module.'&c=order&m=show&id='.$id.'">#'.strtoupper($module).'-'.$id.'</a>',
							'inputtime' => SYS_TIME,
						));
					}
				}
				
				$status = $this->_get_order_status($id, $data['items'], 1);
				$this->link
					 ->where('id', $id)
					 ->update($this->tablename, array('status' => $status));
				
				break;
				
			case 2: // 当前状态是2（支付成功，等待发货）时，按照发货流程转换状态 
				$status = $this->_get_order_status($id, $data['items'], 2);
				$this->link
					 ->where('id', $id)
					 ->update($this->tablename, array('status' => $status));
				break;
				
			case 3: // 交易成功时，按照发货流程转换状态
				// 更新订单状态
				$this->link->where('id', $id)->update($this->tablename, array('status' => 4, 'successtime' => SYS_TIME));
				// 更新订单索引表
				$category = $this->ci->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category');
				foreach ($data['items'] as $t) {
					if ($t['id']) {
						$this->link->replace($this->indexname, array(
							'oid' => $id,
							'iid' => $t['id'],
							'uid' => $data['uid'],
							'fid' => $t['fid'] ? $t['fid'] : 0,
							'review' => 0
						));
						$exp = (int)$category[$t['catid']]['permission'][$this->member['mark']]['buy_experience'];
						$score = (int)$category[$t['catid']]['permission'][$this->member['mark']]['buy_score'];
						$note = '购物消费，商品编号：<a href="'.$t['url'].'" target="_blank">#'.strtoupper($module).'-'.$t['id'].'</a>';
						// 积分检查
						if ($exp) {
                            $this->member_model->update_score(0, $data['uid'], $exp, '', $note);
                        }
						// 虚拟币
						if ($score) {
                            $this->member_model->update_score(1, $data['uid'], $score, '', $note);
                        }
						// 更新成交量
						$this->link
							 ->where('id', $t['id'])
							 ->set('volume', 'volume+'.$t['num'], FALSE)
							 ->update(SITE_ID.'_'.APP_DIR);
					}
				}
				// 订单交易成功邮件通知买家
				$url = MEMBER_URL.'index.php?s='.APP_DIR.'&c=order&m=show&id='.$id;
				$this->ci->sendmail_queue(
                    $this->member['email'],
                    lang('my-29'),
                    dr_lang('my-30', strtoupper($module).'-'.$id, $url, $url)
                );
				break;
				// 订单退款
				case 4:
				// 更新订单状态
				$this->link->where('id', $id)->update($this->tablename, array('status' => 5, 'successtime' => SYS_TIME));
				break;
		}
		
		return $status;
	}
	
	/**
	 * 添加订单
	 *
	 * @param	array	$data
	 * @return	id
	 */
	public function add_order($data) {
	
		if (!$data) {
            return NULL;
        }
		
		$this->link->insert($this->tablename, $data);

		return $this->db->insert_id();
	}
	
	/**
	 * 获取单个收货地址
	 *
	 * @param	intval	$id
	 * @return	array
	 */
	public function get_address($id) {
	
		if (!$id) {
            return NULL;
        }
		
		return $this->db
					->where('id', $id)
					->where('uid', $this->uid)
					->limit(1)
					->get('member_address')
					->row_array();
	}
	
	/**
	 * 订单商品评价信息
	 *
	 * @param	intval	$id
	 * @return	array
	 */
	public function get_item_review($id) {
		
		if (!$id) {
            return NULL;
        }
		
		$order = $this->link
					  ->where('id', (int)$id)
					  ->select('items')
					  ->limit(1)
					  ->get($this->tablename)
					  ->row_array();
		if (!$order || !$order['items']) {
            return NULL;
        }
		
		$data = array();
		$items = dr_string2array($order['items']);
		foreach ($items as $t) {
			// 查询对应的评论数据
			$index = $this->link
						  ->where('oid', (int)$id)
						  ->where('uid', (int)$this->uid)
						  ->where('iid', (int)$t['id'])
						  ->where('fid', (int)$t['fid'])
						  ->limit(1)
						  ->get($this->indexname)
						  ->row_array();
			if ($index) {
				$review = $this->link
							   ->where('id', (int)$index['id'])
							   ->limit(1)
							   ->get($this->reviewname)
							   ->row_array();
				$data[] = array(
					'oid' => $index['oid'],
					'iid' => $index['iid'],
					'fid' => $index['fid'],
					'key' => $index['oid'].'-'.$index['iid'].'-'.$index['fid'],
					'url' => $t['url'],
					'thumb' => $t['thumb'],
					'title' => $t['title'],
					'format' => $t['format'],
					'index' => $index['id'],
					'value' => dr_string2array($review['value']),
					'review' => $index['review'],
					'avgsort' => $review['avgsort'],
					'content' => $review['content'],
				);
			}
		}
		
		return $data;
	}
	
	// 更新商品表中的总评论分数
	public function update_review($iid) {
		
		if (!$iid) {
            return NULL;
        }
		
		$sql = "SELECT avg(b.avgsort) as avg FROM `{$this->indexname}` as a, `{$this->reviewname}` as b WHERE a.id=b.id AND a.iid={$iid} AND a.review=1 GROUP BY a.iid";
		$data = $this->link->query($sql)->row_array();
		if (!$data || $data['avg'] <= 0) return NULL;
		
		$this->link
			 ->where('id', (int)$iid)
			 ->update(SITE_ID.'_'.APP_DIR, array('review' => round($data['avg'], 1)));
		
	}
	
	/**
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
			if (isset($data['keyword']) && $data['keyword']) {
				$select->where('id', intval($data['keyword']));
			}
			if (isset($data['start']) && $data['start'] && $data['start'] != $data['end']) {
				$select->where('inputtime BETWEEN '.$data['start'].' AND '. $data['end']);
			}
			if (isset($data['name']) && $data['name']) {
				$select->like('username', $data['name']);
			}
			if (isset($data['status']) && $data['status']) {
				$select->where('status', $data['status']);
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
	public function limit_page($param, $page, $total) {
	
		if (!$total) {
			$select	= $this->link->select('count(*) as total');
			$this->_where($select, $param);
			$data = $select->get($this->tablename)->row_array();
			unset($select);
			$total = (int)$data['total'];
			if (!$total) {
                return array(array(), array('total' => 0));
            }
		}
		
		$select	= $this->link->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1));
		$_param	= $this->_where($select, $param);
		$data = $select->order_by('id DESC')
					   ->get($this->tablename)
					   ->result_array();
		$_param['total'] = $total;
		
		return array($data, $_param);
	}
	
	
}