<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<script src="<?php echo MEMBER_PATH; ?>statics/js/swfobject.js" type="text/javascript"></script>
<script type="text/javascript">
/**
 * 初始化flash
 */
function initflash(){
    var url = $("#invite_url").val();
    var flashvars = {
        content: encodeURIComponent(url),
        uri: '<?php echo MEMBER_PATH; ?>statics/js/copyurl.jpg'
    };
    var params = {
        wmode: "transparent",
        allowScriptAccess: "always"
    };
    swfobject.embedSWF("<?php echo MEMBER_PATH; ?>statics/js/clipboard.swf", "forLoadSwf", "120", "35", "9.0.0", null, flashvars, params);
}
/**
 * copy成功
 */
function copySuccess(){
    dr_tips('复制成功', 3, 1);
    $("#invite_url").select();
}

</script>
<div class="content clearfix">
	<div class="page_url"><a href="<?php echo SITE_URL; ?>">首页</a> <span>&gt;</span> <a href="<?php echo dr_member_url('home/index'); ?>">会员中心</a> <span>&gt;</span> 首页</div>
	
	<div class="aside">
		<div class="round memberinfo">
			<table width="178" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td rowspan="2" width="50"><div class="memberinfo_avatar"><a href="<?php echo dr_member_url('account/avatar'); ?>"><img src="<?php echo dr_avatar($uid); ?>"></a></div></td>
				<td><b>&nbsp;<?php echo $member['username']; ?></b></td>
				</tr>
			  <tr>
				<td colspan="2"><div class="dr_stars"><a href="" title="<?php echo $member['levelname']; ?>"><?php echo dr_show_stars($member['levelstars']); ?></a></div></td>
			  </tr>
			  <tr height="20">
				<td colspan="3" style="padding-top:8px;">
				<?php echo SITE_EXPERIENCE; ?>：<a href="<?php echo dr_member_url('pay/experience'); ?>"><?php echo $member['experience']; ?></a>
				</td>
			  </tr>
			  <tr height="20">
				<td colspan="3">
				<?php echo SITE_SCORE; ?>：<a href="<?php echo dr_member_url('pay/score'); ?>"><?php echo $member['score']; ?></a>
				</td>
			  </tr>
			  <tr height="20">
				<td colspan="3">
				<?php echo SITE_MONEY; ?>：<a href="<?php echo dr_member_url('pay/index'); ?>"><?php echo $member['money']; ?></a>
				</td>
			  </tr>
			  <tr height="20">
				<td colspan="3">
				会员组：<?php echo $member['groupname']; ?>
				</td>
			  </tr>
			  <?php if ($member['levelid']) { ?>
			  <tr height="20">
				<td colspan="3">
				会员等级：<?php echo $member['levelname']; ?>
				</td>
			  </tr>
			  <?php }  if ($member['groupid']==1 && $regverify == 1) { ?>
			  <tr height="20">
				<td colspan="3">
				审核会员：<a href="<?php echo dr_url('login/resend'); ?>">重发邮件</a>
				</td>
			  </tr>
			  <?php } ?>
			</table>   
		</div>
		<div class="menu round">
			<h4><strong>快捷菜单</strong></h4>
			<ul>
				<li><a href="<?php echo dr_member_url('account/index'); ?>">基本资料</a></li>
				<li><a href="<?php echo dr_member_url('account/password'); ?>">修改密码</a></li>
				<li><a href="<?php echo dr_member_url('account/upgrade'); ?>">会员升级</a></li>
				<li><a href="<?php echo dr_member_url('pm/index'); ?>">站内消息</a></li>
			</ul>
		</div>
	</div>
    
    <div class="article">
        <div class="message message_info">您目前的身份是：<?php echo $member['groupname']; ?>，拥有<?php echo SITE_SCORE; ?>：<?php echo $member['score']; ?></div>

        <div class="section">
            <div class="main invite_main">
                <div class="round part_d">
                    <h3>好友邀请</h3>
                    <strong>
                        您使用的网站链接来邀请好友注册，注册之后将自动关注您，并奖励<?php echo SITE_SCORE;  echo $member_rule['invite_score']; ?>、<?php echo SITE_EXPERIENCE;  echo $member_rule['invite_experience']; ?>。
                    </strong>
                    <input class="input_text" id="invite_url" type="text" value="<?php echo $invite_url; ?>" />
                    <object type="application/x-shockwave-flash" data="<?php echo MEMBER_PATH; ?>statics/js/clipboard.swf" width="120" height="35" id="forLoadSwf" style="visibility: visible;"><param name="wmode" value="transparent"><param name="allowScriptAccess" value="always">
                        <param name="flashvars" value="<?php echo urlencode($invite_url); ?>&amp;uri=<?php echo MEMBER_PATH; ?>statics/js/copyurl.jpg"></object>
                    <script type="text/javascript">initflash();</script>
                </div>
            </div>
            <?php if ($new_notice) { ?>
            <div class="main">
                <div class="message message_tips">
                    <span>您有新提醒：</span>
                    <?php if (is_array($notice)) { $count=count($notice);foreach ($notice as $t) { ?>
                    <a href="<?php echo $t['url']; ?>"><?php echo $t['name']; ?>(<?php echo $t['total']; ?>)</a>&nbsp;&nbsp;
                    <?php } } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="section">
            <div class="title"><strong>登录记录</strong></div>
            <div class="main dr_table">
                <table style="table-layout:fixed;">
                    <thead>
                    <tr>
                        <th style="width:120px;" class="algin_l">登录时间</th>
                        <th style="width:150px;" class="algin_l">登录Ip</th>
                        <th class="algin_l">详情</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $return = $this->list_tag("action=sql sql='select * from @#member_login where uid=$uid order by logintime desc limit 5'"); if ($return) extract($return); $count=count($return); if (is_array($return)) { foreach ($return as $key=>$t) { ?>
                    <tr>
                        <td class="algin_l"><?php echo dr_date($t['logintime'], NULL, 'red'); ?></td>
                        <td class="algin_l"><a href="http://www.baidu.com/baidu?wd=<?php echo $t['loginip']; ?>" target="_blank"><?php if ($t['oauthid']) { ?>【<?php echo $t['oauthid']; ?>】<?php }  echo $t['loginip']; ?></a></td>
                        <td class="algin_l"><?php echo dr_strcut($t['useragent'], 60); ?></td>
                    </tr>
                    <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
