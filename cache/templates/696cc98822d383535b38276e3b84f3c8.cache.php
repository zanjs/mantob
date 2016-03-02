<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<link rel="stylesheet" href="<?php echo SITE_PATH; ?>mantob/statics/css/bootstrap.css" />
<style type="text/css">
html { _overflow-y:scroll;}
* { font-size:13px; }
tr { height:23px;}
.td { height:20px;overflow:hidden}
.box {
    width:48%;
    float:left;
    margin:0 10px;
}
.box .box-title {
    border-bottom: 1px solid #DDDDDD;
    padding: 7px 0 7px 10px;
}
.box .box-title h3 {
    color: #444444;
    float: left;
    font-size: 18px;
    font-weight: 400;
    line-height: 24px;
    margin: 3px 0;
}
.box .content {
    background: none repeat scroll 0 0 #FFFFFF;
    padding: 20px;
}
.box .box-title h3 i {
	font-size:18px;
	margin-right:10px;
}
.mleft {
	width:120px;
	text-align:right;
}

</style>
<link rel="stylesheet" href="<?php echo SITE_PATH; ?>mantob/statics/css/font-awesome/css/font-awesome.css" />
<div id="main_frameid" class="pad-10" style="_margin-right:-12px;_width:98.9%;">
	<script type="text/javascript">
    $(function(){   	

        $.getScript("<?php echo dr_url('home/mtotal'); ?>");
        
 
    }); 
    </script>
	<div class="explain-col" id="screenAlert" style="display:none;margin-bottom:10px;"><font color="#FF0000"><?php echo lang('html-243'); ?></font></div>
	<div class="explain-col" id="browserVersionAlert" style="display:none;margin-bottom:10px;"><font color="#FF0000"><?php echo lang('html-018'); ?></font></div>

    <?php if ($ip) { ?>
    <div class="explain-col" style="margin-bottom:10px;"><font color="#FF0000"><?php echo $ip; ?></font></div>
    <?php } ?>


    <div class="box">
        <div class="box-title">
            <h3><i class="icon-volume-up"></i><?php echo lang('html-704'); ?></h3>
            <div class="clear"></div>
        </div>
        <div class="content" style="padding-left:3px;padding-right:3px">
            <table class="table table-hover table-nomargin table-bordered">
                <thead>
                <tr>
                    <th style="text-align:center"><?php echo lang('html-583'); ?></th>
                    <th style="text-align:center"><?php echo lang('html-757'); ?></th>
                    <th style="text-align:center"><?php echo lang('html-758'); ?></th>
                    <!-- <th style="text-align:center"><?php echo lang('114'); ?></th>
                    <th style="text-align:center"><?php echo lang('html-759'); ?></th> -->
                    <th style="text-align:center"><?php echo lang('option'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (is_array($mtotal)) { $count=count($mtotal);foreach ($mtotal as $dir=>$t) { ?>
               
                <tr>
                    <td style="text-align:center"><?php echo $t['name']; ?></td>
                    <td style="text-align:center"><a href="<?php echo $t['content']; ?>" id="<?php echo $dir; ?>_content"><img src="<?php echo SITE_PATH; ?>mantob/statics/images/mloading.gif"></a></td>
                    <td style="text-align:center"><a href="<?php echo $t['today']; ?>" id="<?php echo $dir; ?>_today"><img src="<?php echo SITE_PATH; ?>mantob/statics/images/mloading.gif"></a></td>
                   <!--  <td style="text-align:center"><a href="<?php echo $t['content_verify']; ?>" id="<?php echo $dir; ?>_content_verify"><img src="<?php echo SITE_PATH; ?>mantob/statics/images/mloading.gif"></a></td>
                    <td style="text-align:center"><a href="<?php echo $t['extend_verify']; ?>" id="<?php echo $dir; ?>_extend_verify"><img src="<?php echo SITE_PATH; ?>mantob/statics/images/mloading.gif"></a></td> -->
                    <td style="text-align:center">
                        <a href="<?php echo $t['add']; ?>" class="btn btn-mini"><?php echo lang('post'); ?></a>
                        <a href="<?php echo $t['url']; ?>" class="btn btn-mini" target="_blank"><?php echo lang('go'); ?></a>
                    </td>
                </tr>
                <?php } } ?>
                </tbody>
            </table>

        </div>
    </div>

    <div class="box">
        <div class="box-title">
            <h3><i class="icon-globe"></i><?php echo lang('html-029'); ?></h3>
            <div class="clear"></div>
        </div>
        <div class="content">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="mleft" align="right"><?php echo lang('html-031'); ?>：</td>
                    <td>&nbsp;<a href="http://www.ZAN3.com/" target="_blank"> Mantob for<?php echo MAN_NAME; ?></a>&nbsp;v<?php echo MAN_VERSION; ?>&nbsp; <span id="mantob_version"></span></td>
                </tr>
                <tr>
                    <td class="mleft" align="right"><?php echo lang('html-032'); ?>：</td>
                    <td>&nbsp;PHP <?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td class="mleft" align="right"><?php echo lang('html-033'); ?>：</td>
                    <td>&nbsp;MySql <?php echo $sqlversion; ?></td>
                </tr>
                <tr>
                    <td class="mleft" align="right"><?php echo lang('html-724'); ?>：</td>
                    <td>&nbsp;<?php echo @ini_get("upload_max_filesize"); ?>
                        <!-- <a href="http://www.mantob.net/forum.php?mod=viewthread&tid=2490" style="color:#0000ff" target="_blank"><?php echo lang('html-726'); ?></a> -->
                    </td>
                </tr>
                <tr>
                    <td class="mleft" align="right"><?php echo lang('html-725'); ?>：</td>
                    <td>&nbsp;<?php echo @ini_get("post_max_size"); ?>
                       <!--  <a href="http://www.mantob.net/forum.php?mod=viewthread&tid=2490" style="color:#0000ff" target="_blank"><?php echo lang('html-726'); ?></a> -->
                    </td>
                </tr>
                <tr>
                    <td class="mleft" align="right"><?php echo lang('html-732'); ?>：</td>
                    <td>&nbsp;<?php echo $sip; ?>
                       <!--  <a href="http://www.mantob.net/forum.php?mod=viewthread&tid=3093" style="color:#0000ff" target="_blank"><?php echo lang('html-733'); ?></a> -->
                    </td>
                </tr>
            </table>
            <div class="bk20 hr"><hr></div>
            <div id="mantob_license">
                <div style="padding:20px;">
                    <img src="<?php echo SITE_URL; ?>mantob/statics/images/loading-yun.gif" />
                    正在努力访问云端服务器 ...
                </div>
            </div>
        </div>
    </div>
    <div class="box" style="clear:both;width:98%">
		<div class="box-title">
			<h3><i class="icon-cloud"></i><?php echo lang('html-036'); ?></h3>
			<div class="clear"></div>
		</div>
        <div class="content" id="mantob_news"  style="padding-left:3px;padding-right:3px">
            <div style="padding:20px;">
                <img src="<?php echo SITE_URL; ?>mantob/statics/images/loading-yun.gif" />
                正在努力访问云端服务器 ...
            </div>
        </div>
    </div>
    <div class="box">
        <div class="box-title">
            <h3><i class="icon-group"></i><?php echo lang('yanfa'); ?></h3>
            <div class="clear"></div>
        </div>
        <div class="content">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="mleft"align="right"><?php echo lang('yanfa'); ?>：</td>
                    <td>&nbsp;<a href="http://www.zan3.com" target="_blank">zan3</a></td>
                </tr>
               <tr>
                    <td class="mleft" align="right"><?php echo lang('cehua'); ?>：</td>
                    <td>
                         &nbsp;<a href="http://www.zan3.com/" target="_blank">赞生博客技术支持</a>
                    </td>
                </tr>
                <!-- <tr>
                    <td class="mleft" align="right" valign="top" style="padding-top:2px;">感谢捐赠者：</td>
                    <td style="padding-right:5px;padding-top:2px;"><div id="finecms_juan" >

                        <div style="padding:20px;">
                            <img src="<?php echo SITE_URL; ?>mantob/statics/images/loading-yun.gif" />
                            正在努力访问云端服务器 ...
                        </div>
                    </div></td>
                </tr> -->
            </table>
        </div>
    </div>
</div>
</body>
</html>