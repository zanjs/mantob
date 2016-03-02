<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<?php echo $menu; ?>
	</div>
	<div class="bk10"></div>
	<div class="explain-col">
		<font color="gray"><?php echo lang('html-345'); ?></font>
	</div>
	<div class="bk10"></div>
	<div class="table-list">
		<table width="100%">
        <tr>
        	<?php $id=0; ?>
        	<td align="left" width="250"><?php echo lang('guest'); ?></td>
            <td align="left">
            <a href="javascript:;" onclick="dr_member_rule('<?php echo $id; ?>', '<?php echo dr_url("member/setting/rule", array("id"=>$id)); ?>', '<?php echo lang('guest'); ?>')" class="blue">[<?php echo lang('113'); ?>]</a>
            <div id="dr_status_<?php echo $id; ?>" class="onShow"></div>
            </td>
        </tr>
        <?php $return_group = $this->list_tag("action=cache name=MEMBER.group  return=group"); if ($return_group) extract($return_group); $count_group=count($return_group); if (is_array($return_group)) { foreach ($return_group as $key_group=>$group) {  if ($group['id'] > 2) { ?>
        <tr>
        	<td align="left" width="250"><?php echo $group['name']; ?></td>
            <td align="left"></td>
        </tr>
        <?php if (is_array($group['level'])) { $count=count($group['level']);foreach ($group['level'] as $level) { ?>
        <tr>
        	<?php $id=$group['id'].'_'.$level['id']; ?>
        	<td align="left" width="250" style="padding-left:40px"><?php echo $level['name']; ?>&nbsp;&nbsp;<?php echo dr_show_stars($level['stars']); ?></td>
            <td align="left">
            <a href="javascript:;" onclick="dr_member_rule('<?php echo $id; ?>', '<?php echo dr_url("member/setting/rule", array("id"=>$id)); ?>', '<?php echo $group['name']; ?>-<?php echo $level['name']; ?>')" class="blue">[<?php echo lang('113'); ?>]</a>
            <div id="dr_status_<?php echo $id; ?>" class="onShow"></div>
            </td>
        </tr>
        <?php } }  } else { ?>
        <tr>
        	<?php $id=$group['id']; ?>
        	<td align="left" width="250"><?php echo $group['name']; ?></td>
            <td align="left">
            <a href="javascript:;" onclick="dr_member_rule('<?php echo $id; ?>', '<?php echo dr_url("member/setting/rule", array("id"=>$id)); ?>', '<?php echo $group['name']; ?>')" class="blue">[<?php echo lang('113'); ?>]</a>
            <div id="dr_status_<?php echo $id; ?>" class="onShow"></div>
            </td>
        </tr>
        <?php }  } } ?>
        </table>
	</div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>