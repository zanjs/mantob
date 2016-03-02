<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script type="text/javascript">
function dr_get_block(id) {
	var body = '<div style="padding:20px;font-size:14px">{'+'om_block('+id+')'+'}</div>';
	var throughBox = art.dialog.through;
	throughBox({
		content: body,
		title: "<?php echo lang('html-472'); ?>"
	});	
}
</script>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<?php echo $menu; ?><span>|</span><a href="http://www.mantob.com/help/list-341.html" target="_blank"><em><?php echo lang('help'); ?></em></a>
	</div>
	<div class="bk10"></div>
	<div class="explain-col">
        <font color="gray"><?php echo lang('html-471'); ?></font>
	</div>
	<div class="bk10"></div>
	<div class="table-list">
		<form action="" method="post" name="myform" id="myform">
		<table width="100%">
		<thead>
		<tr>
			<th width="20" align="right"><input name="dr_select" id="dr_select" type="checkbox" onClick="dr_selected()" />&nbsp;</th>
			<th width="200" align="left"><?php echo lang('html-026'); ?></th>
			<th align="left" class="dr_option"><?php echo lang('option'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if (is_array($list)) { $count=count($list);foreach ($list as $t) { ?>
		<tr>
			<td align="right"><input name="ids[]" type="checkbox" class="dr_select" value="<?php echo $t['id']; ?>" />&nbsp;</td>
			<td align="left"><?php echo $t['name']; ?></td>
			<td align="left" class="dr_option">
			<?php if ($this->ci->is_auth('admin/block/edit')) { ?><a href="<?php echo dr_dialog_url(dr_url('block/edit',array('id'=>$t['id'])), 'edit'); ?>"><?php echo lang('edit'); ?></a><?php } ?>
            <a href="javascript:;" onclick="dr_get_block('<?php echo $t['id']; ?>')"><?php echo lang('html-472'); ?></a>
			</td>
		</tr>
		<?php } } ?>
		<tr>
			<th width="20" align="right"><input name="dr_select" id="dr_select" type="checkbox" onClick="dr_selected()" />&nbsp;</th>
			<td colspan="5" align="left" style="border:none"> 
			<?php if ($this->ci->is_auth('admin/block/del')) { ?><input type="button" class="button" value="<?php echo lang('del'); ?>" name="option" onClick="dr_confirm_set_all('<?php echo lang('015'); ?>')" /><?php } ?>
			</td>
		</tr>
		</tbody>
		</table>
		</form>
	</div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>