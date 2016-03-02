<?php

/**
 * mantob Website Management System
 *
 * @since		version 2.0.1
 * @author		mantob <mantob@gmail.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */

class F_Ueditor extends A_Field {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->name = 'Ueditor';	// 字段名称
        $this->fieldtype = array('MEDIUMTEXT' => ''); // TRUE表全部可用字段类型,自定义格式为 array('可用字段类型名称' => '默认长度', ... )
        $this->defaulttype = 'MEDIUMTEXT'; // 当用户没有选择字段类型时的缺省值
    }

    /**
     * 字段相关属性参数
     *
     * @param	array	$value	值
     * @return  string
     */
    public function option($option) {

        $option['key'] = isset($option['key']) ? $option['key'] : '';
        $option['mode'] = isset($option['mode']) ? $option['mode'] : 1;
        $option['tool'] = isset($option['tool']) ? $option['tool'] : '\'bold\', \'italic\', \'underline\'';
        $option['mode2'] = isset($option['mode2']) ? $option['mode2'] : $option['mode'];
        $option['tool2'] = isset($option['tool2']) ? $option['tool2'] : $option['tool'];
        $option['mode3'] = isset($option['mode3']) ? $option['mode3'] : $option['mode'];
        $option['tool3'] = isset($option['tool3']) ? $option['tool3'] : $option['tool'];
        $option['value'] = isset($option['value']) ? $option['value'] : '';
        $option['width'] = isset($option['width']) ? $option['width'] : '90%';
        $option['height'] = isset($option['height']) ? $option['height'] : 200;
        $option['fieldtype'] = isset($option['fieldtype']) ? $option['fieldtype'] : '';
        $option['fieldlength'] = isset($option['fieldlength']) ? $option['fieldlength'] : '';

        return '<tr>
                    <th>'.lang('265').'：</th>
                    <td>
                    <input type="text" class="input-text" size="10" name="data[setting][option][width]" value="'.$option['width'].'">
					<div class="onShow">'.lang('290').'</div>
                    </td>
                </tr>
				<tr>
                    <th>'.lang('266').'：</th>
                    <td>
                    <input type="text" class="input-text" size="10" name="data[setting][option][height]" value="'.$option['height'].'">
					<div class="onShow">px</div>
                    </td>
                </tr>
				<tr>
                    <th>'.lang('304').'：</th>
                    <td>
                    <input type="radio" value="1" name="data[setting][option][mode]" '.($option['mode'] == 1 ? 'checked' : '').' onclick="$(\'#bjqms1\').hide()">&nbsp;<label>'.lang('305').'</label>&nbsp;&nbsp;
                    <input type="radio" value="2" name="data[setting][option][mode]" '.($option['mode'] == 2 ? 'checked' : '').' onclick="$(\'#bjqms1\').hide()">&nbsp;<label>'.lang('306').'</label>&nbsp;&nbsp;
                    <input type="radio" value="3" name="data[setting][option][mode]" '.($option['mode'] == 3 ? 'checked' : '').' onclick="$(\'#bjqms1\').show()">&nbsp;<label>'.lang('307').'</label>&nbsp;&nbsp;
                    </td>
                </tr>
				<tr id="bjqms1" '.($option['mode'] < 3 ? 'style="display:none"' : '').'>
                    <th>'.lang('308').'：</th>
                    <td>
                    <textarea name="data[setting][option][tool]" style="width:520px;height:50px;" class="text">'.$option['tool'].'</textarea>
					<br><font color="#959595">'.lang('309').'</font>
                    </td>
                </tr>
				<tr>
                    <th>'.lang('345').'：</th>
                    <td>
                    <input type="radio" value="1" name="data[setting][option][mode2]" '.($option['mode2'] == 1 ? 'checked' : '').' onclick="$(\'#bjqms2\').hide()">&nbsp;<label>'.lang('305').'</label>&nbsp;&nbsp;
                    <input type="radio" value="2" name="data[setting][option][mode2]" '.($option['mode2'] == 2 ? 'checked' : '').' onclick="$(\'#bjqms2\').hide()">&nbsp;<label>'.lang('306').'</label>&nbsp;&nbsp;
                    <input type="radio" value="3" name="data[setting][option][mode2]" '.($option['mode2'] == 3 ? 'checked' : '').' onclick="$(\'#bjqms2\').show()">&nbsp;<label>'.lang('307').'</label>&nbsp;&nbsp;
                    </td>
                </tr>
				<tr id="bjqms2" '.($option['mode2'] < 3 ? 'style="display:none"' : '').'>
                    <th>'.lang('308').'：</th>
                    <td>
                    <textarea name="data[setting][option][tool2]" style="width:520px;height:50px;" class="text">'.$option['tool2'].'</textarea>
					<br><font color="#959595">'.lang('309').'</font>
                    </td>
                </tr>
				<tr>
                    <th>'.lang('html-743').'：</th>
                    <td>
                    <input type="radio" value="1" name="data[setting][option][mode3]" '.($option['mode3'] == 1 ? 'checked' : '').' onclick="$(\'#bjqms3\').hide()">&nbsp;<label>'.lang('305').'</label>&nbsp;&nbsp;
                    <input type="radio" value="2" name="data[setting][option][mode3]" '.($option['mode3'] == 2 ? 'checked' : '').' onclick="$(\'#bjqms3\').hide()">&nbsp;<label>'.lang('306').'</label>&nbsp;&nbsp;
                    <input type="radio" value="3" name="data[setting][option][mode3]" '.($option['mode3'] == 3 ? 'checked' : '').' onclick="$(\'#bjqms3\').show()">&nbsp;<label>'.lang('307').'</label>&nbsp;&nbsp;
                    </td>
                </tr>
				<tr id="bjqms3" '.($option['mode3'] < 3 ? 'style="display:none"' : '').'>
                    <th>'.lang('308').'：</th>
                    <td>
                    <textarea name="data[setting][option][tool3]" style="width:520px;height:50px;" class="text">'.$option['tool3'].'</textarea>
					<br><font color="#959595">'.lang('309').'</font>
                    </td>
                </tr>
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
     */
    public function insert_value($field) {

        $value = $this->ci->post[$field['fieldname']];
        $value = str_replace('class="pagebreak" name="dr_page_break"', 'name="dr_page_break" class="pagebreak"', $value);
        $value = str_replace('name="dr_page_break" class="pagebreak"', 'class="pagebreak"', $value);
        $value = str_replace('id="undefined"', '', $value);
        $attach = array();

        // 下载远程图片
        if ($field['fieldname'] == 'content'
            && preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|png))\\2/i", $value, $imgs)) {

            $uid = isset($_POST['data']['uid']) ? (int)$_POST['data']['uid'] : $this->ci->uid;
            $down = FALSE;

            // 附件总大小判断
            if ($uid == $this->ci->uid
                && ($this->ci->member['adminid'] || $this->ci->member_rule['attachsize'])) {
                $data = $this->ci
                    ->db
                    ->select_sum('filesize')
                    ->where('uid', $uid)
                    ->get('attachment')
                    ->row_array();
                if ($this->ci->member['adminid']
                    || $data['filesize'] <= $this->ci->member_rule['attachsize'] * 1024 * 1024) {
                    $down = TRUE;
                }
            }

            $this->ci->load->model('attachment_model');
            foreach ($imgs[3] as $i => $img) {
                $timg = 1;
                // 开始下载远程图片
                if ($down && $uid) {
                    $result = $this->ci->attachment_model->catcher($uid, $img);
                    if (is_array($result)) {
                        list($id, $file, $_ext) = $result;
                        $timg = 0;
                        $value = str_replace($imgs[0][$i], " id=\"mantob_img_$id\" src=\"".dr_file($file)."\"", $value);
                        $attach[] = $id;
                    }
                }
                // 当不下载图片
                if ($timg) {
                    if (preg_match('/<img id="mantob_img_([0-9]+)"([\w|\s|=\."]*)src="'.str_replace('/', '\/', $img).'"/iU', $value, $match)) {
                        $attach[] = (int)$match[1];
                    } elseif (preg_match('/<img src="'.str_replace('/', '\/', $img).'"(.*)id="mantob_img_([0-9]+)"/iU', $value, $match)) {
                        $attach[] = (int)$match[2];
                    } else {
                        if (strpos($img, 'ueditor142') === FALSE) {
                            $attach[] = $img;
                        }
                    }
                }
            }
        }

        // 第一张作为缩略图
        if ($field['fieldname'] == 'content'
            && isset($_POST['data']['thumb'])
            && !$_POST['data']['thumb'] && $attach) {
            $this->ci->data[1]['thumb'] = $attach[0];
        }

        $this->ci->data[$field['ismain']][$field['fieldname']] = $value;
    }

    /**
     * 字段输出
     *
     * @param	array	$value	数据库值
     * @return  string
     */
    public function output($value) {
        return $value;
    }

    /**
     * 获取附件id
     */
    public function get_attach_id($value) {

        $data = array();
        $pregs = array('/id="mantob_img_([0-9]+)"/iU');

        foreach ($pregs as $preg) {
            if (preg_match_all($preg, $value, $aid)) {
                foreach ($aid[1] as $i => $id) {
                    $data[] = (int)$id;
                }
            }
        }

        return $data;
    }

    /**
     * 附件处理
     */
    public function attach($data, $_data) {

        $data1 = $data2 = array();
        $pregs = array('/id="mantob_img_([0-9]+)"/iU');

        // 新数据筛选附件
        foreach ($pregs as $preg) {
            if (preg_match_all($preg, $data, $aid)) {
                foreach ($aid[1] as $i => $id) {
                    $data1[] = (int)$id;
                }
            }
        }

        // 旧数据筛选附件
        foreach ($pregs as $preg) {
            if (preg_match_all($preg, $_data, $aid)) {
                foreach ($aid[1] as $i => $id) {
                    $data2[] = (int)$id;
                }
            }
        }

        // 新旧数据都无附件就跳出
        if (!$data1 && !$data2) {
            return NULL;
        }

        // 新旧数据都一样时表示没做改变就跳出
        if ($data1 === $data2) {
            return NULL;
        }

        // 当无新数据且有旧数据表示删除旧附件
        if (!$data1 && $data2) {
            return array(
                array(),
                $data2
            );
        }

        // 当无旧数据且有新数据表示增加新附件
        if ($data1 && !$data2) {
            return array(
                $data1,
                array()
            );
        }

        // 剩下的情况就是删除旧文件增加新文件

        // 新旧附件的交集，表示固定的
        $intersect = @array_intersect($data1, $data2);

        return array(
            array_diff($data1, $intersect), // 固有的与新文件中的差集表示新增的附件
            array_diff($data2, $intersect), // 固有的与旧文件中的差集表示待删除的附件
        );
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

        // 表单宽度设置
        $width = isset($cfg['option']['width']) && $cfg['option']['width'] ? $cfg['option']['width'] : '90%';

        // 表单高度设置
        $key = isset($cfg['option']['key']) && $cfg['option']['key'] ? $cfg['option']['key'] : '';
        $height = isset($cfg['option']['height']) && $cfg['option']['height'] ? $cfg['option']['height'] : '300';

        // 字段提示信息
        $tips = isset($cfg['validate']['tips']) && $cfg['validate']['tips'] ? '<div class="onShow" id="dr_'.$name.'_tips">'.$cfg['validate']['tips'].'</div>' : '';

        // 字段默认值
        $value = $value ? $value : $this->get_default_value($cfg['option']['value']);

        // 输出
        $str = '';
        $ueurl = MEMBER_PATH.'ueditor142/';

        // 防止重复加载JS
        if (!defined('DAYRUI_UEDITOR_LD')) {
            $str.= '
			<script type="text/javascript" src="'.$ueurl.'ueditor.config.js"></script>
			<script type="text/javascript" src="'.$ueurl.'ueditor.all.js"></script>
			<script type="text/javascript" src="'.$ueurl.'lang/'.SITE_LANGUAGE.'/'.SITE_LANGUAGE.'.js"></script>';
            define('DAYRUI_UEDITOR_LD', 1);
        }

        $tool = IS_ADMIN ? "'fullscreen', 'source', '|', " : ''; // 后台引用时显示html工具栏
        $pagebreak = $name == 'content' ? ', \'pagebreak\'' : '';

        // 编辑器模式
        $mode = 1;
        if (IS_ADMIN) {
            $mode = $cfg['option']['mode'];
        } else {
            if ($this->ci->mobile) {
                $mode = isset($cfg['option']['mode3']) && $cfg['option']['mode3'] ? $cfg['option']['mode3'] : $cfg['option']['mode'];
                $cfg['option']['tool'] = isset($cfg['option']['tool3']) && $cfg['option']['tool3'] ? $cfg['option']['tool3'] : $cfg['option']['tool'];
            } else {
                $mode = isset($cfg['option']['mode2']) && $cfg['option']['mode2'] ? $cfg['option']['mode2'] : $cfg['option']['mode'];
                $cfg['option']['tool'] = isset($cfg['option']['tool2']) && $cfg['option']['tool2'] ? $cfg['option']['tool2'] : $cfg['option']['tool'];
            }

        }
        // 编辑器工具
        switch ($mode) {
            case 3: // 自定义
                $tool.= $cfg['option']['tool'];
                break;
            case 2: // 精简
                $tool.= "'undo', 'redo', '|',
						'bold', 'italic', 'underline', 'strikethrough','|', 'pasteplain', 'forecolor', 'fontfamily', 'fontsize','|', 'emotion', 'map', 'link', 'unlink'$pagebreak";
                break;
            case 1: // 默认
                $tool.= "'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'template', '|',
            'horizontal', 'date', 'time', 'spechars', 'snapscreen', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
            'print', 'preview', 'searchreplace', 'help', 'drafts'$pagebreak";
                break;
        }

        $str.= "
		<script name=\"data[$name]\" type=\"text/plain\" id=\"dr_$name\">$value</script>
		<script type=\"text/javascript\">
			var editorOption = {
				UEDITOR_HOME_URL: \"".$ueurl."\",
				serverUrl:\"".$ueurl."php/index.php\",
				toolbars: [
					[ $tool ]
				],
				lang: \"".SITE_LANGUAGE."\",
				initialContent:\"\",
				initialFrameWidth: \"{$width}\",
				initialFrameHeight: \"{$height}\",
				initialStyle:\"body{font-size:14px}\",
				wordCount:false,
				elementPathEnabled:false,
				charset:\"utf-8\",
				zIndex: \"1\",
				pageBreakTag:\"_page_break_tag_\"
			};
			var editor = new baidu.editor.ui.Editor(editorOption);
			editor.render(\"dr_$name\");
		</script>
		".$tips;

        return $this->input_format($name, $text, $str);
    }
}