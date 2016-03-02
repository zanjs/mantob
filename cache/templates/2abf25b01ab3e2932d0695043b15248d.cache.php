<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script type="text/javascript">
$(function() {
    $(".select-cat").click(function(){
        var action = $(this).attr("action");
        var childs = $(this).attr("childs");
        var catid = $(this).attr("catid");
        var catids= new Array(); //定义一数组
        catids = childs.split(","); //字符分割
        if (action == 'close') {
            $.cookie('dr_<?php echo SITE_ID; ?>_<?php echo $this->ci->router->class; ?>_'+catid, null,{path:"/",expires: -1});
            $(this).attr("action", "open");
            $(this).html("[-]");
            for (i=0;i<catids.length ;i++ ) {
                if (catids[i] != catid) {
                    $(".dr_catid_"+catids[i]).show();
                }
            }
        } else {
            // 关闭状态存储cookie
            $.cookie('dr_<?php echo SITE_ID; ?>_<?php echo $this->ci->router->class; ?>_'+catid, 1);
            $(this).attr("action", "close");
            $(this).html("[+]");
            for (i=0;i<catids.length ;i++ ) {
                if (catids[i] != catid) {
                    $(".dr_catid_"+catids[i]).hide();
                }
            }
        }
    });
    $(".select-cat").each(function(){
        var childs = $(this).attr("childs");
        var catid = $(this).attr("catid");
        var cache = $.cookie('dr_<?php echo SITE_ID; ?>_<?php echo $this->ci->router->class; ?>_'+catid);
        if (cache) {
            $(this).attr("action", "close");
            $(this).html("[+]");
            var catids= new Array(); //定义一数组
            catids = childs.split(","); //字符分割
            for (i=0;i<catids.length ;i++ ) {
                if (catids[i] != catid) {
                    $(".dr_catid_"+catids[i]).hide();
                }
            }
        }
    });
});
</script>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<?php echo $menu; ?><span>|</span><a href="http://www.mantob.com/help/list-341.html" target="_blank"><em><?php echo lang('help'); ?></em></a>
	</div>
	<div class="bk10"></div>
	<div class="explain-col">
		<font color="gray"><?php echo lang('html-384'); ?></font>
	</div>
	<div class="bk10"></div>
	<div class="table-list">
		<form action="" method="post" name="myform" id="myform">
		<input name="action" id="action" type="hidden" value="order" />
		<table width="100%">
		<thead>
        <tr>
			<th width="20" align="right"><input name="dr_select" id="dr_select" type="checkbox" onClick="dr_selected()" />&nbsp;</th>
			<th width="30" align="center"><?php echo lang('order'); ?></th>
			<th width="40" align="left">Id</th>
            <th align="left"><?php echo lang('html-542'); ?></th>
            <th align="center" width="80"><?php echo lang('179'); ?></th>
            <th align="center" width="80"><?php echo lang('html-378'); ?></th>
            <th align="center" width="50"><?php echo lang('html-379'); ?></th>
			<th align="left"><?php if ($this->ci->is_auth('admin/navigator/add')) { ?><a class="add" title="<?php echo lang('add'); ?>" href="<?php echo dr_url('navigator/add', array('type' => $type)); ?>"></a><?php } ?></th>
        </tr>
        </thead>
		<tbody>
		<?php echo $list; ?>
		<tr>
			<th align="right"><input name="dr_select" type="checkbox" onClick="dr_selected()" />&nbsp;</th>
			<td colspan="7" align="left"> 
            <?php if ($this->ci->is_auth('navigator/del')) { ?><input type="button" class="button" value="<?php echo lang('del'); ?>" name="button" onClick="$('#action').val('del');return dr_confirm_set_all('<?php echo lang('015'); ?>')" />&nbsp;<?php }  if ($this->ci->is_auth('navigator/edit')) { ?><input type="button" class="button" value="<?php echo lang('order'); ?>" name="button" onclick="$('#action').val('order');return dr_confirm_set_all('<?php echo lang('015'); ?>')" />&nbsp;<div class="onShow"><?php echo lang('html-054'); ?></div><?php } ?>
			</td>
		</tr>
		</tbody>
		</table>
		</form>
	</div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>