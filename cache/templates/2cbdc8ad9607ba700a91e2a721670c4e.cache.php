<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script type="text/javascript">
$(function() {
	SwapTab(<?php echo $page; ?>);
	<?php if ($result) { ?>
	art.dialog.tips('<?php echo lang('000'); ?>', 3, 1);
	<?php } ?>
});
function import_ucenter() {
	window.location.href="<?php echo dr_url('member/setting/importuc'); ?>";
}
</script>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<?php echo $menu; ?><span>|</span><a href="http://www.mantob.com/help/list-341.html" target="_blank"><em><?php echo lang('help'); ?></em></a>
	</div>
	<div class="bk10"></div>
	<div class="table-list col-tab">
		<form action="" method="post" name="myform" id="myform">
        <input name="page" id="page" type="hidden" value="<?php echo $page; ?>" />
		<ul class="tabBut cu-li">
			<li onclick="SwapTab(0)"><?php echo lang('html-083'); ?></li>
			<li onclick="SwapTab(1)"><?php echo lang('html-155'); ?></li>
			<li onclick="SwapTab(2)"><?php echo lang('html-288'); ?></li>
			<li onclick="SwapTab(3)">Ucenter</li>
			<li onclick="SwapTab(4)"><?php echo lang('html-751'); ?></li>
			<li onclick="SwapTab(5)"><?php echo lang('html-034'); ?></li>
		</ul>
		<div class="contentList pad-10">
			<div id="cnt_0" style="display:none" class="dr_hide">
				<table width="100%" class="table_form">
				<tr>
					<th width="200">&nbsp;<?php echo lang('html-292'); ?>： </th>
					<td>
					<input type="radio" name="data[logincode]" value="1" <?php echo dr_set_radio('logincode', $data['logincode'], '1', TRUE); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[logincode]" value="0" <?php echo dr_set_radio('logincode', $data['logincode'], '0'); ?> />&nbsp;<label><?php echo lang('close'); ?></label>
					</td>
				</tr>
                <tr>
					<th>&nbsp;<?php echo lang('html-340'); ?>： </th>
					<td>
					<input type="radio" name="data[avatar]" value="1" <?php echo dr_set_radio('avatar', $data['avatar'], '1'); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[avatar]" value="0" <?php echo dr_set_radio('avatar', $data['avatar'], '0', TRUE); ?> />&nbsp;<label><?php echo lang('close'); ?></label>
					</td>
				</tr>
                <tr>
					<th>&nbsp;<?php echo lang('html-339'); ?>： </th>
					<td>
					<input type="radio" name="data[complete]" value="1" <?php echo dr_set_radio('complete', $data['complete'], '1'); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[complete]" value="0" <?php echo dr_set_radio('complete', $data['complete'], '0', TRUE); ?> />&nbsp;<label><?php echo lang('close'); ?></label>&nbsp;&nbsp;
                    <div class="onShow"><?php echo lang('html-148'); ?></div>
					</td>
				</tr>
                <tr>
					<th>&nbsp;<?php echo lang('html-658'); ?>： </th>
					<td>
					<input <?php if (!$mobile) { ?>disabled="disabled"<?php } ?> type="radio" name="data[ismobile]" value="1" <?php echo dr_set_radio('ismobile', $data['ismobile'], '1'); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input <?php if (!$mobile) { ?>disabled="disabled"<?php } ?> type="radio" name="data[ismobile]" value="0" <?php echo dr_set_radio('ismobile', $data['ismobile'], '0', TRUE); ?> />&nbsp;<label><?php echo lang('close'); ?></label>&nbsp;&nbsp;
                    <div class="onShow"><?php echo lang('html-657'); ?></div>
					</td>
				</tr>
                <tr>
					<th>&nbsp;<?php echo lang('html-656'); ?>： </th>
					<td>
					<input <?php if (!$mobile) { ?>disabled="disabled"<?php } ?> type="radio" name="data[mobile]" value="1" <?php echo dr_set_radio('mobile', $data['mobile'], '1'); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input <?php if (!$mobile) { ?>disabled="disabled"<?php } ?> type="radio" name="data[mobile]" value="0" <?php echo dr_set_radio('mobile', $data['mobile'], '0', TRUE); ?> />&nbsp;<label><?php echo lang('close'); ?></label>&nbsp;&nbsp;
                    <div class="onShow"><?php echo lang('html-657'); ?></div>
					</td>
				</tr>
				<tr>
					<th><?php echo lang('html-479'); ?>： </th>
					<td>
					<input class="input-text" type="text" name="data[pagesize]" value="<?php echo $data['pagesize']; ?>" size="20" /><div class="onShow"><?php echo lang('html-480'); ?></div>
					</td>
				</tr>
				</table>
			</div>
            <div id="cnt_1" style="display:none" class="dr_hide">
                <table width="100%" class="table_form">
                    <?php if (is_array($SITE)) { $count=count($SITE);foreach ($SITE as $sid=>$t) { ?>
                    <tr>
                        <th><?php echo dr_strcut($t['SITE_NAME'], 25); ?>： </th>
                        <td>
                            <input class="input-text" type="text" name="data[domain][<?php echo $sid; ?>]" value="<?php echo $data['domain'][$sid]; ?>" size="30" />
                            <?php if ($data['domain'][$sid]) {  if ($data['domain'][$sid] == SITE_DOMAIN) { ?>
                            <div class="onError"><?php echo dr_lang('html-730', $data['domain'][$sid]); ?></div>
                            <?php } else { ?>
                            <script>
                                $.get("<?php echo dr_url('home/domain', array('domain' => $data['domain'][$sid])); ?>", function(data){
                                    if (data) {
                                        $("#dr_domian_<?php echo $sid; ?>").html(data);
                                    } else {
                                        $("#dr_domian_<?php echo $sid; ?>").hide();
                                    }
                                });
                            </script>
                            <div id="dr_domian_<?php echo $sid; ?>" class="onError"></div>
                            <?php }  } else { ?>
                            <div class="onShow"><?php echo lang('html-291'); ?></div>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } } ?>
                    <tr>
                        <th width="200" style="color: blue"><?php echo lang('html-623'); ?>： </th>
                        <td>
                            <font color="blue"><?php echo FCPATH; ?>member/</font>
                        </td>
                    </tr>

                </table>
            </div>
			<div id="cnt_2" style="display:none" class="dr_hide">
				<table width="100%" class="table_form">
				<tr>
					<th width="200">&nbsp;<?php echo lang('html-293'); ?>： </th>
					<td>
					<input type="radio" name="data[register]" value="1" <?php echo dr_set_radio('register', $data['register'], '1', TRUE); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[register]" value="0" <?php echo dr_set_radio('register', $data['register'], '0'); ?> />&nbsp;<label><?php echo lang('close'); ?></label>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-294'); ?>： </th>
					<td>
					<input type="radio" name="data[regcode]" value="1" <?php echo dr_set_radio('regcode', $data['regcode'], '1', TRUE); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[regcode]" value="0" <?php echo dr_set_radio('regcode', $data['regcode'], '0'); ?> />&nbsp;<label><?php echo lang('close'); ?></label>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-679'); ?>： </th>
					<td>
					<input type="radio" name="data[regoauth]" value="0" <?php echo dr_set_radio('regoauth', $data['regoauth'], '0', TRUE); ?> />&nbsp;<label><?php echo lang('html-680'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[regoauth]" value="1" <?php echo dr_set_radio('regoauth', $data['regoauth'], '1'); ?> />&nbsp;<label><?php echo lang('html-681'); ?></label>
					<div class="onShow"><?php echo lang('html-682'); ?></div>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-295'); ?>： </th>
					<td>
					<input type="radio" name="data[regverify]" value="1" <?php echo dr_set_radio('regverify', $data['regverify'], '1'); ?> />&nbsp;<label><?php echo lang('html-296'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[regverify]" value="2" <?php echo dr_set_radio('regverify', $data['regverify'], '2'); ?> />&nbsp;<label><?php echo lang('html-297'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[regverify]" value="0" <?php echo dr_set_radio('regverify', $data['regverify'], '0', TRUE); ?> />&nbsp;<label><?php echo lang('close'); ?></label>
					<div class="onShow"><?php echo lang('html-298'); ?></div>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-299'); ?>： </th>
					<td>
					<input class="input-text" type="text" name="data[regiptime]" value="<?php echo $data['regiptime']; ?>" size="10" />
					<div class="onShow"><?php echo lang('html-300'); ?></div>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-301'); ?>： </th>
					<td>
					<input class="input-text" type="text" name="data[regnamerule]" id="dr_regnamerule" value="<?php echo $data['regnamerule']; ?>" size="40" />
					<select onchange="javascript:$('#dr_regnamerule').val(this.value)" name="pattern_select">
                    <option value=""><?php echo lang('html-190'); ?></option>
					<option value="/.*/"><?php echo lang('html-302'); ?></option>
					<option value="/^[0-9.-]+$/"><?php echo lang('html-191'); ?></option>
					<option value="/^[0-9-]+$/"><?php echo lang('html-192'); ?></option>
					<option value="/^[a-z]+$/i"><?php echo lang('html-193'); ?></option>
					<option value="/^[0-9a-z]+$/i"><?php echo lang('html-194'); ?></option>
					<option value="/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/">E-mail</option>
					<option value="/^[0-9]{5,20}$/">QQ</option>
					<option value="/^(1)[0-9]{10}$/"><?php echo lang('html-196'); ?></option>
					<option value="/^[0-9-]{6,13}$/"><?php echo lang('html-197'); ?></option>
					<option value="/^[0-9]{6}$/"><?php echo lang('html-198'); ?></option>
					</select>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-303'); ?>： </th>
					<td>
					<input class="input-text" type="text" name="data[regnotallow]" value="<?php echo $data['regnotallow']; ?>" size="60" />
					<div class="onShow"><?php echo lang('html-304'); ?></div>
					</td>
				</tr>
				</table>
			</div>
			<div id="cnt_3" style="display:none" class="dr_hide">
				<table width="100%" class="table_form">
				<tr>
					<th width="200">&nbsp;Ucenter： </th>
					<td>
					<input type="radio" name="data[ucenter]" value="1" <?php echo dr_set_radio('ucenter', $data['ucenter'], '1'); ?> />&nbsp;<label><?php echo lang('open'); ?></label>
                    &nbsp;&nbsp;&nbsp;
					<input type="radio" name="data[ucenter]" value="0" <?php echo dr_set_radio('ucenter', $data['ucenter'], '0', TRUE); ?> />&nbsp;<label><?php echo lang('close'); ?></label>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-309'); ?>： </th>
					<td>
					<input readonly class="input-text" type="text" name="ucenterapi" value="<?php echo SITE_URL; ?>member" style="width:320px;" />
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-310'); ?>： </th>
					<td>
					<textarea readonly style="width:60%;height:80px" class="input-text" name="ucenterapi"><?php if (is_array($synurl)) { $count=count($synurl);foreach ($synurl as $url) {  if ($url != SITE_URL.'member') {  echo $url;  echo PHP_EOL;  }  } } ?></textarea><br><font color="gray"><?php echo lang('html-311'); ?></font>
					</td>
				</tr>
				<tr>
					<th>&nbsp;<?php echo lang('html-312'); ?>： </th>
					<td>
					<textarea style="width:60%;height:240px" class="input-text" name="data[ucentercfg]"><?php echo $data['ucentercfg']; ?></textarea>
					</td>
				</tr>
				</table>
			</div>
            <div id="cnt_4" style="display:none" class="dr_hide">
                <table width="100%" class="table_form">
                    <tr>
                        <th width="200"><?php echo lang('html-753'); ?>： </th>
                        <td>
                            <textarea style="width:60%;height:120px" class="input-text" name="data[field]"><?php if ($data['field']) {  echo $data['field'];  } else { ?><tr id="dr_row_{name}"><th>{text} </th><td>{value}</td></tr><?php } ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>&nbsp;<?php echo lang('html-754'); ?>： </th>
                        <td>
                            <textarea style="width:60%;height:120px" class="input-text" name="data[mbfield]"><?php if ($data['mbfield']) {  echo $data['mbfield'];  } else { ?><tr id="dr_row_{name}"><th>{text} </th><td>{value}</td></tr><?php } ?></textarea>
                            <br><font color=""><?php echo lang('html-752'); ?></font>
                        </td>
                    </tr>
                </table>
            </div>
            <div id="cnt_5" style="display:none" class="dr_hide">
                <table width="100%" class="table_form">
                    <tr>
                        <th width="200"><?php echo lang('html-705'); ?>： </th>
                        <td>
                            <input class="input-text" type="text" name="data[sns_post_time]" value="<?php echo $data['sns_post_time']; ?>" size="7" /><div class="onShow"><?php echo lang('html-760'); ?></div>
                        </td>
                    </tr>
                </table>
            </div>
			<table width="100%" class="table_form">
			<tr>
				<th width="200" style="border:none;">&nbsp;</th>
				<td>
				<input class="button" type="submit" name="submit" value="<?php echo lang('submit'); ?>" />
				</td>
			</tr>
			</table>
		</div>
		</form>
	</div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>