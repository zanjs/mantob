<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script type="text/javascript">
$(function(){
	$(".msearch a").click(function(){
		var name = $(this).attr("name");
		$(".msearch a").removeClass("cur");
		$(this).addClass("cur");
		$("#dr_order").val(name);
	});
	$("#dr_loadmore a").click(function(){
		var page = $("#dr_page").val();
		$("#dr_loading").html("<div style='padding:30px;text-align:center;'><img src='<?php echo MEMBER_THEME_PATH; ?>images/loading.gif' /></div>");
		$.get("<?php echo $moreurl; ?>", {page:page}, function(data){
			if (data != "null") {
				$("#dr_body").append(data);
				$("#dr_page").val(Number(page) + 1);
			}
			$("#dr_loading").html("");
		});
	});
});

function dr_delete() {
    $("#action").val('delete');
	var _data = $("#myform").serialize();
	var _url = window.location.href;
	if ((_data.split('ids')).length-1 <= 0) {
		$.dialog.tips("到底删除谁？您还没有选择呢", 2);
		return true;
	}
	// 将表单数据ajax提交验证
	$.ajax({type: "POST",dataType:"json", url: _url, data: _data,
		success: function(data) {
			//验证成功
			if (data.status == 1) {
				$.dialog.tips(data.code, 3, 1);
				// 刷新页
				setTimeout('window.location.reload(true)', 3000);
			} else {
				$.dialog.tips(data.code, 3, 2);
				return true;
			}
		},
		error: function(HttpRequest, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + HttpRequest.statusText + "\r\n" + HttpRequest.responseText);
		}
	});
	return false;
}
</script>
<input name="page" id="dr_page" type="hidden" value="2" />
<div class="content clearfix">
	<?php if ($fn_include = $this->_include("navigator.html")) include($fn_include); ?>
    <div class="article">
        <div class="section">
            <div class="title"><strong><?php echo $meta_name; ?></strong></div>
            <div class="main dr_table">
                <form action="" method="post" name="myform" id="myform">
                <input name="action" id="action" type="hidden" value="" />
				<table style="table-layout:fixed;margin-bottom: 0;">
				<thead>
					<tr>
						<th style="width:20px;" class="algin_r">&nbsp;</th>
						<th class="algin_l">主题</th>
						<th style="width:120px;" class="algin_l">收藏时间</th>
					</tr>
				</thead>
				<tbody id="dr_body">
				<?php if ($fn_include = $this->_include("favorite_data.html")) include($fn_include); ?>
				</tbody>
				</table>
                <table style="table-layout:fixed;" class="mbutton">
                <tr>
                    <td style="width:20px;border:none" class="algin_r"><input type="checkbox" onclick="dr_selected()" id="dr_select" name="dr_select"></td>
					<td class="algin_l" style="padding-top:8px;border:none">
                    <button type="button" class="red_button" onClick="dr_delete()">删除</button>
                    </td>
                </tr>
				</table>
                </form>
				<div id="dr_loading"></div>
                <div id="dr_loadmore" class="load-more"><a href="javascript:;">查 看 更 多</a></div>
            </div>
        </div>
    </div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>