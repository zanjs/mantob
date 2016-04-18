<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script type="text/javascript">
$(function() {
	SwapTab(0);
	$(".table_form tr>th").attr("width", 200);
	<?php if ($error) { ?>
	art.dialog.tips('<font color=red><?php echo $error['msg']; ?></font>', 3);
	d_tips('<?php echo $error['error']; ?>', 0);
	<?php }  if ($result) { ?>
	art.dialog.tips('<?php echo $result; ?>', 3, 1);
	<?php }  if ($create) { ?>
	$.post('<?php echo $create; ?>&rand='+Math.random(),{}, function(){});
	$.post('<?php echo SITE_URL;  echo APP_DIR; ?>/index.php?c=home&m=create_list_html&id=<?php echo $catid; ?>&rand='+Math.random(),{}, function(){});
	<?php } ?>
    var catid = $("#dr_catid").val();
    if (catid) {
        show_category_field(catid);
    }
});
function show_category_field(catid) {
    $('#dr_category_field').html('');
	$.post(siteurl+'?s=<?php echo APP_DIR; ?>&c=category&m=field&rand='+Math.random(),{ catid:catid, data:<?php echo json_encode(dr_array2string($data)); ?> }, function(data){
		$('#dr_category_field').html(data);
	});
}
</script>
<form action="" method="post" name="myform" id="myform">
<input name="backurl" type="hidden" value="<?php echo $backurl; ?>" />
<input name="page" id="page" type="hidden" value="<?php echo $page; ?>" />
<input name="action" id="dr_action" type="hidden" value="back" />
<input name="dr_id" id="dr_id" type="hidden" value="<?php echo $data['id']; ?>" />
<input name="dr_module" id="dr_module" type="hidden" value="<?php echo APP_DIR; ?>" />
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<?php echo $menu; ?>
	</div>
	<div class="bk10"></div>
    <div class="table-list col-tab">
        <ul class="tabBut cu-li">
            <li class="on"><?php echo lang('246'); ?></li>
        </ul>
        <div class="contentList pad-10 dr_table">
        <table width="100%" class="table_form">
		<?php if ($is_category) { ?>
        <tr>
            <th width="200"><font color="red">*</font>&nbsp;<?php echo lang('cat-00'); ?>： </th>
            <td><?php echo $select; ?></td>
        </tr>
		<?php }  echo $myfield;  if ($flag) { ?>
        <tr>
            <th width="200"><?php echo lang('html-174'); ?>： </th>
            <td>
			<?php if (is_array($flag)) { $count=count($flag);foreach ($flag as $i=>$t) {  if ($t['name']) { ?><input name="flag[]" type="checkbox" <?php if (@in_array($i, $myflag)) { ?>checked="checked" <?php } ?>value="<?php echo $i; ?>" />&nbsp;<label><?php echo $t['name']; ?></label>&nbsp;&nbsp;&nbsp;<?php }  } } ?>
            </td>
        </tr>
		<?php }  if (!$data['id']) { ?>
        <tr>
            <th width="200"><?php echo lang('m-113'); ?>： </th>
            <td>
			<input name="qq_share" type="checkbox" checked="checked" value="1" />
			<label>腾讯微博</label>
            <?php if (!$member['oauth']['qq']) { ?><label style="color:#FF0000">（请进入会员中心-账户-快捷登录，绑定QQ账户）</label><?php } ?>
			&nbsp;&nbsp;
			<input name="sina_share" type="checkbox" checked="checked" value="1" />
			<label>新浪微博</label>
            <?php if (!$member['oauth']['sina']) { ?><label style="color:#FF0000">（请进入会员中心-账户-快捷登录，绑定新浪账户）</label><?php } ?>
            </td>
        </tr>
        <?php } ?>
        </table>
        </div>
    </div>
</div>
<div class="fixed-bottom">
    <div class="fixed-but text-c">
        <div class="button"><input value="<?php echo lang('html-362'); ?>" type="submit" name="submit" class="cu" onclick="$('#dr_action').val('back')" style="width:100px;" /></div>
        <?php if (count($data) < 5) { ?>
        <div class="button"><input value="<?php echo lang('html-363'); ?>" type="submit" name="submit" class="cu" onclick="$('#dr_action').val('continue')" style="width:100px;" /></div>
        <?php } ?>
    </div>
</div>
</form>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>