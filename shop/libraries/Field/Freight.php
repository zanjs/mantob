<?php

/**
 * mantob Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class F_Freight extends A_Field {
	
	/**
     * 构造函数
     */
    public function __construct() {
		parent::__construct();
		$this->name = '运费模式'; // 字段名称
		$this->fieldtype = array(
			'INT' => '10'
		); // TRUE表全部可用字段类型,自定义格式为 array('可用字段类型名称' => '默认长度', ... )
		$this->defaulttype = 'INT'; // 当用户没有选择字段类型时的缺省值
    }
	
	/**
	 * 字段相关属性参数
	 *
	 * @param	array	$value	值
	 * @return  string
	 */
	public function option($option) {
		return '';
	}
	
	/**
	 * 字段输出
	 */
	public function output($value) {
		return dr_string2array($value);
	}
	
	/**
	 * 字段入库值
	 */
	public function insert_value($field) {
		$value = $this->ci->post[$field['fieldname']];
		if ((float)$value['price'] > 0) {
			$this->ci->data[$field['ismain']][$field['fieldname']] = dr_array2string($value);
		} else {
			$this->ci->data[$field['ismain']][$field['fieldname']] = 0;
		}
	}
	
	/**
	 * 字段表单输入
	 *
	 * @param	string	$cname	字段别名
	 * @param	string	$name	字段名称
	 * @param	array	$cfg	字段配置
	 * @param	string	$value	值
	 * @return  string
	 */
	public function input($cname, $name, $cfg, $value = NULL, $id = 0) {
		// 字段显示名称
		$text = (isset($cfg['validate']['required']) && $cfg['validate']['required'] == 1 ? '<font color="red">*</font>' : '').'&nbsp;'.$cname.'：';
		// 表单附加参数
		$attr = isset($cfg['validate']['formattr']) && $cfg['validate']['formattr'] ? $cfg['validate']['formattr'] : '';
		// 字段提示信息
		$tips = isset($cfg['validate']['tips']) && $cfg['validate']['tips'] ? '<div class="onShow" id="dr_'.$name.'_tips">'.$cfg['validate']['tips'].'</div>' : '<div class="onTime" id="dr_'.$name.'_tips"></div>';
		// 字段默认值
		$value = is_array($value) ? $value : dr_string2array($value);
		$str = '
		<select name="data[freight][type]">
		<option value="0" '.(isset($value['type']) && $value['type'] == 0 ? 'selected' : '').'> 按订单 </option>
		<option value="1" '.(isset($value['type']) && $value['type'] == 1 ? 'selected' : '').'> 按数量 </option>
		</select>&nbsp;&nbsp;
		费用：<input type="text" style="width:80px;" value="'.$value['price'].'" name="data[freight][price]" class="input-text" />&nbsp;&nbsp;
		'.$tips;
		return $this->input_format($name, $text, $str);
	}
	
}