<?php if ($fn_include = $this->_include("header.html")) include($fn_include);  $_pages=$pages; ?>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<?php echo $menu; ?><span>|</span><a href="http://www.mantob.com/help/list-341.html" target="_blank"><em><?php echo lang('help'); ?></em></a>
	</div>
	<div class="bk10"></div>
	<div class="explain-col">
        <form method="post" action="" name="searchform" id="searchform">
        <?php echo lang('html-348'); ?>：
        <select name="data[groupid]">
            <option value=""> -- </option>
            <?php $return = $this->list_tag("action=cache name=MEMBER.group"); if ($return) extract($return); $count=count($return); if (is_array($return)) { foreach ($return as $key=>$t) { ?>
            <option value="<?php echo $t['id']; ?>" <?php if ($t['id']==$param['groupid']) { ?>selected<?php } ?>> <?php echo $t['name']; ?> </option>
            <?php } }  $gcache=$return; ?>
        </select>
        &nbsp;&nbsp;
        <select name="data[field]">
            <option value="uid" <?php if ($param['field']=='uid') { ?>selected<?php } ?>>Uid</option>
            <?php if (is_array($field)) { $count=count($field);foreach ($field as $t) { ?>
            <option value="<?php echo $t['fieldname']; ?>" <?php if ($param['field']==$t['fieldname']) { ?>selected<?php } ?>><?php echo $t['name']; ?></option>
            <?php } } ?>
        </select> ：
        <input type="text" class="input-text" value="<?php echo $param['keyword']; ?>" size="30" placeholder="<?php echo lang('html-249'); ?>" name="data[keyword]" />&nbsp;
        &nbsp;&nbsp;
        <input type="submit" value="<?php echo lang('search'); ?>" class="button" name="search">
        </form>
	</div>
	<div class="bk10"></div>
	<div class="table-list">
		<form action="" method="post" name="myform" id="myform">
        <input name="action" id="action" type="hidden" value="del" />
		<table width="100%">
		<thead>
		<tr>
			<th width="10" align="right"><input name="dr_select" id="dr_select" type="checkbox" onClick="dr_selected()" />&nbsp;</th>
			<th width="40" align="center">Uid</th>
			<th class="<?php echo ns_sorting('username'); ?>" name="username" width="150" align="left"><?php echo lang('html-347'); ?></th>
			<th class="<?php echo ns_sorting('groupid'); ?>" name="groupid" width="90" align="left"><?php echo lang('html-348'); ?></th>
			<th class="<?php echo ns_sorting('experience'); ?>" name="experience" width="80" align="left"><?php echo SITE_EXPERIENCE; ?></th>
			<th class="<?php echo ns_sorting('score'); ?>" name="score" width="80" align="left"><?php echo SITE_SCORE; ?></th>
			<th class="<?php echo ns_sorting('money'); ?>" name="money" width="80" align="left"><?php echo SITE_MONEY; ?></th>
			<th class="<?php echo ns_sorting('spend'); ?>" name="spend" width="80" align="left"><?php echo lang('html-709'); ?></th>
			<th hide="1" class="<?php echo ns_sorting('regtime'); ?>" name="regtime" width="120" align="left"><?php echo lang('html-351'); ?></th>
			<th align="left" class="dr_option"><?php echo lang('option'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if (is_array($list)) { $count=count($list);foreach ($list as $t) {  if ($t['adminid']!=1) { ?>
		<tr id="dr_row_<?php echo $t['uid']; ?>">
			<td align="right"><input name="ids[]" type="checkbox" class="dr_select<?php echo $t['groupid']; ?>" value="<?php echo $t['uid']; ?>" />&nbsp;</td>
			<td align="center"><?php echo $t['uid']; ?></td>
			<td align="left">
            <a onclick="dr_dialog_member('<?php echo $t['uid']; ?>')" href="javascript:;">
			<?php if ($t['groupid']==2 && !$t['username']) {  echo get_member_nickname($t['uid']); ?>&nbsp;<?php echo lang('html-586');  } else {  echo dr_keyword_highlight($t['username'], $param['keyword']);  } ?>
            </a>
			</td>
			<td align="left"><font <?php if ($t['groupid']==1) { ?>color="#FF0000"<?php } ?>><?php $cache = $this->_cache_var('MEMBER'); eval('echo $cache'.$this->_get_var('group.$t[groupid].name').';');unset($cache); ?></font></td>
			<td align="left"><a <?php if ($this->ci->is_auth('member/admin/experience/index')) { ?>href="<?php echo dr_url('member/experience/index',array('uid'=>$t['uid'])); ?>" style="color: blue;text-decoration: underline;font-size: 12px;"<?php } ?>><?php echo $t['experience']; ?></a></td>
			<td align="left"><a <?php if ($this->ci->is_auth('member/admin/score/index')) { ?>href="<?php echo dr_url('member/score/index',array('uid'=>$t['uid'])); ?>" style="color: blue;text-decoration: underline;font-size: 12px;"<?php } ?>><?php echo $t['score']; ?></a></td>
			<td align="left"><a <?php if ($this->ci->is_auth('member/admin/pay/index')) { ?>href="<?php echo dr_url('member/pay/index',array('uid'=>$t['uid'])); ?>" style="color: blue;text-decoration: underline;font-size: 12px;"<?php } ?>><?php echo $t['money']; ?></a></td>
			<td hide="1" align="left"><?php echo $t['spend']; ?></td>
			<td align="left"><?php echo dr_date($t['regtime'], NULL, 'red'); ?></td>
			<td align="left" class="dr_option">
			<?php if ($this->ci->is_auth('member/admin/home/edit')) { ?><a <?php if ($t['id']==1) { ?>href="javascript:;" style="color:#999"<?php } else { ?>href="<?php echo dr_url('member/home/edit',array('uid'=>$t['uid'])); ?>"<?php } ?>><?php echo lang('edit'); ?></a><?php }  if (MEMBER_OPEN_SPACE && $this->ci->is_auth('member/admin/space/edit')) { ?><a href="<?php echo dr_url('member/space/edit',array('uid' => $t['uid'])); ?>"><?php echo lang('html-334'); ?></a><?php }  if ($member['adminid']==1) { ?><a href="<?php echo MEMBER_URL; ?>index.php?c=api&m=ologin&uid=<?php echo $t['uid']; ?>" target="_blank"><?php echo lang('html-703'); ?></a><?php } ?>
			</td>
		</tr> 
		<?php }  } } ?>
		<tr>
        	<th width="20" align="right"><input name="dr_select" id="dr_select" type="checkbox" onClick="dr_selected()" />&nbsp;</th>
			<td colspan="10" align="left" style="border:none">
            <?php if ($this->ci->is_auth('member/admin/home/del')) { ?>
			<input type="button" class="button" value="<?php echo lang('del'); ?>" name="option" onClick="$('#action').val('del');dr_confirm_set_all('<?php echo lang('015'); ?>', 1)" />
            <?php }  if ($this->ci->is_auth('member/admin/home/edit')) { ?>
			<input type="button" class="button" value="<?php echo lang('html-394'); ?>" name="option" onClick="$('#action').val('update');dr_confirm_set_all('<?php echo lang('015'); ?>')" />
            <select name="groupid">
            <?php if (is_array($gcache)) { $count=count($gcache);foreach ($gcache as $t) { ?>
            <option value="<?php echo $t['id']; ?>"> <?php echo $t['name']; ?> </option>
            <?php } } ?>
        	</select>
            <?php } ?>
			</td>
		</tr>
		</tbody>
		</table>
		</form>
        <div id="pages"><a><?php echo dr_lang('html-346', $param['total']); ?></a><?php echo $_pages; ?></div>
	</div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>