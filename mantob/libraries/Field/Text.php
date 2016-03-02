<?php

/**
 * Omweb Website Management System
 *
 * @since		version 2.0.0
 * @author		mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 * @filesource	svn://www.mantob.com/v2/mantob/libraries/Field/Text.php
 */

class F_Text extends A_Field {
	
	/**
     * 构造函数
     */
    public function __construct() {
		parent::__construct();
		$this->name = IS_ADMIN ? lang('302') : ''; // 字段名称
		$this->fieldtype = TRUE; // TRUE表全部可用字段类型,自定义格式为 array('可用字段类型名称' => '默认长度', ... )
		$this->defaulttype = 'VARCHAR'; // 当用户没有选择字段类型时的缺省值
    }
	
	/**
	 * 字段相关属性参数
	 *
	 * @param	array	$value	值
	 * @return  string
	 */
	public function option($option) {
        $unique = '';
        $option['value'] = isset($option['value']) ? $option['value'] : '';
		$option['width'] = isset($option['width']) ? $option['width'] : 200;
        $option['unique'] = isset($option['unique']) ? $option['unique'] : 0;
		$option['fieldtype'] = isset($option['fieldtype']) ? $option['fieldtype'] : '';
		$option['fieldlength'] = isset($option['fieldlength']) ? $option['fieldlength'] : '';
        if (TEXT_UNIQUE) {
            $unique.= '
            <tr>
                <th>'.lang('349').'：</th>
                <td>
                <input type="radio" value="0" name="data[setting][option][unique]" '.(!$option['unique'] ? 'checked' : '').'>&nbsp;<label>'.lang('close').'</label>&nbsp;&nbsp;
                <input type="radio" value="1" name="data[setting][option][unique]" '.($option['unique'] ? 'checked' : '').'>&nbsp;<label>'.lang('open').'</label>&nbsp;&nbsp;
                <div class="onShow">'.lang('350').'</div>
                </td>
            </tr>
            ';
        }
		return '<tr>
                    <th>'.lang('265').'：</th>
                    <td>
                    <input type="text" class="input-text" size="10" name="data[setting][option][width]" value="'.$option['width'].'">
					<div class="onShow">'.lang('290').'</div>
                    </td>
                </tr>
                '.$unique.'
				<tr>
                    <th>'.lang('277').'：</th>
                    <td>
                    <input id="field_default_value" type="text" class="input-text" size="20" value="'.$option['value'].'" name="data[setting][option][value]">
					'.$this->member_field_select().'
					<div class="onShow">'.lang('278').'</div>
                    </td>
                </tr>
				'.$this->field_type($option['fieldtype'], $option['fieldlength']);
	}

    /**
     * 字段入库值
     *
     * @param	array	$field	字段信息
     * @return  void
     */
    public function insert_value($field) {
        $this->ci->data[$field['ismain']][$field['fieldname']] = htmlspecialchars($this->ci->post[$field['fieldname']]);
    }

	/**
	 * 字段表单输入
	 *
	 * @param	string	$cname	字段别名
	 * @param	string	$name	字段名称
	 * @param	array	$cfg	字段配置
	 * @param	array	$value	值
	 * @param	array	$id		当前内容表的id（表示非发布操作）
	 * @return  string
	 */
	public function input($cname, $name, $cfg, $value = NULL, $id = 0) {
		// 字段显示名称
		$text = (isset($cfg['validate']['required']) && $cfg['validate']['required'] == 1 ? '<font color="red">*</font>' : '').'&nbsp;'.$cname.'：';
		// 表单宽度设置
		$width = isset($cfg['option']['width']) && $cfg['option']['width'] ? $cfg['option']['width'] : '200';
		$width = 'style="width:'.$width.(is_numeric($width) ? 'px' : '').';"';
		// 表单附加参数
		$attr = isset($cfg['validate']['formattr']) && $cfg['validate']['formattr'] ? $cfg['validate']['formattr'] : '';
		// 字段提示信息
		$tips = ($name == 'title' && APP_DIR) || (isset($cfg['validate']['tips']) && $cfg['validate']['tips']) ? '<div class="onShow" id="dr_'.$name.'_tips">'.$cfg['validate']['tips'].'</div>' : '';
		// 字段默认值
		$value = htmlspecialchars_decode($value ? $value : $this->get_default_value($cfg['option']['value']));
		// 禁止修改
		$disabled = !IS_ADMIN && $id && $value && isset($cfg['validate']['isedit']) && $cfg['validate']['isedit'] ? ' disabled' : '';
		// 当字段必填时，加入html5验证标签
		$required = isset($cfg['validate']['required']) && $cfg['validate']['required'] == 1 ? ' required="required"' : '';
		return $this->input_format($name, $text, '<input class="input-text" type="text" name="data['.$name.']" id="dr_'.$name.'" value="'.$value.'" '.$width.$disabled.$required.' '.$attr.' />'.$tips);
	}
	
}