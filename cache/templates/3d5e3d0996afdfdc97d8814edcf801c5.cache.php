<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script type="text/javascript">
$(function(){
	<?php if ($result_error) { ?>
	dr_tips("<?php echo $error; ?>", 3);
	<?php } ?>
});
function dr_set_score(value) {
	$("#dr_price").html(parseFloat(value/<?php echo intval(SITE_CONVERT); ?>));
}
</script>
<div class="content clearfix">
	<?php if ($fn_include = $this->_include("navigator.html")) include($fn_include); ?>
    <div class="article">
    	<div class="section">
            <div class="title"><strong><?php echo $meta_name; ?></strong></div>
            <div class="main">
                <form method="post" action="">
				<table width="100%" class="table_form" style="margin-top:20px">
                <tr>
                    <th width="160">兑换比率： </th>
                    <td>1 <?php echo SITE_MONEY; ?>&nbsp;=&nbsp;<?php echo SITE_CONVERT; ?>&nbsp;<?php echo SITE_SCORE; ?>，可用<?php echo SITE_MONEY; ?>&nbsp;<span class="dr_price"><?php echo $member['money']; ?></span>&nbsp;。</td>
                </tr>
                <tr>
                    <th><font color="red">*</font>&nbsp;兑换数量： </th>
                    <td> <input type="text" value="" name="score" onblur="dr_set_score(this.value)" style="width:100px" class="input_text" /><?php echo SITE_SCORE; ?>，&nbsp;&nbsp;需要&nbsp;<span class="dr_price" id="dr_price">0</span>&nbsp;<?php echo SITE_MONEY; ?>&nbsp;。</td>
                </tr>
                <tr>
                    <th style="border-bottom:none">&nbsp;</th>
                    <td style="border-bottom:none;padding-top: 20px;"><div class="mbutton"><button value="" type="submit" class="blue_button">兑换</button></div></td>
                </tr>
				</table>
                </form>
            </div>
        </div>
    </div>
</div>



<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>