<?php if (is_array($list)) { $count=count($list);foreach ($list as $t) { ?>
<tr id="dr_row_<?php echo $t['id']; ?>">
	<td class="algin_r"><input type="checkbox" value="<?php echo $t['id']; ?>" class="dr_select" name="ids[]"></td>
	<td class="algin_l"><a href="<?php echo $t['url']; ?>" title="<?php echo $t['title']; ?>" target="_blank"><?php echo $t['title']; ?></a></td>
	<td class="algin_l"><?php echo dr_date($t['inputtime'], '', 'red'); ?></td>
</tr>
<?php } } ?>