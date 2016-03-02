<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script type="text/javascript">
$(function(){
	$("#dr_remove").sortable();
	<?php if ($result) { ?>
	art.dialog.tips('<?php echo $result; ?>', 3, 1);
	<?php } ?>
});
</script>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<?php echo $menu; ?>
	</div>
	<div class="bk10"></div>
	<div class="explain-col">
        <font color="gray"><?php echo lang('html-313'); ?></font>
	</div>
	<div class="bk10"></div>
	<div class="table-list">
		<form action="" method="post" name="myform" id="myform">
		<table width="100%">
		<thead>
		<tr>
			<th width="10" align="right">&nbsp;</th>
			<th width="120" align="left">OAuth</th>
			<th width="200" align="left">App Id</th>
			<th width="300" align="left">App Key</th>
			<th align="left" class="dr_option"><?php echo lang('html-314'); ?></th>
		</tr>
		</thead>
		<tbody id="dr_remove">
		<?php if (is_array($data)) { $count=count($data);foreach ($data as $id=>$t) { ?>
		<tr>
			<td align="right">&nbsp;<input name="data[id][]" type="hidden" value="<?php echo $id; ?>" /></td>
			<td align="left"><img src="<?php echo SITE_URL; ?>member/statics/OAuth/<?php echo $t['icon']; ?>.png" style="cursor:move">&nbsp;&nbsp;<?php echo $t['name']; ?></td>
			<td align="left"><input class="input-text" type="text" style="width:200px" name="data[key][]" value="<?php echo $t['key']; ?>" /></td>
			<td align="left"><input class="input-text" type="text" style="width:300px" name="data[secret][]" value="<?php echo $t['secret']; ?>" /></td>
			<td align="left" class="dr_option">&nbsp;<input name="data[use][<?php echo $id; ?>][]" type="checkbox" value="1" <?php if ($t['use']) { ?>checked<?php } ?> /></td>
		</tr>
		<?php } } ?>
		<tr>
			<th width="10" align="right">&nbsp;</th>
			<td colspan="4" align="left" style="border:none"> 
			<input type="submit" class="button" value="<?php echo lang('save'); ?>" name="option" />
			</td>
		</tr>
		</tbody>
		</table>
		</form>
	</div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>