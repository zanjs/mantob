<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<link href="<?php echo MEMBER_THEME_PATH; ?>sns/sns.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo MEMBER_THEME_PATH; ?>sns/uploadify/jquery.uploadify.min.js"></script>
<script type="text/javascript">
var THEME_URL = '<?php echo MEMBER_THEME_PATH; ?>';
var moreurl = '<?php echo $moreurl; ?>';
$(function(){
    dr_find_user(0);
    var unid = "weibo";
    var fileSizeLimit = "10240KB";
    var total = "9";
    $('#uploadify_'+unid).uploadify({
        formData: {
            attach_type: 'feed_image',
            upload_type: 'image',
            thumb: 1,
            width: 100,
            height: 100,
            cut: 1,
            PHPSESSID: "<?php echo dr_authcode($uid, 'ENCODE'); ?>"
        },
        fileSizeLimit: fileSizeLimit,
        fileTypeDesc: 'Image Files',
        fileTypeExts: "*.jpg; *.gif; *.jpeg; *.png; ",
        swf: '<?php echo MEMBER_THEME_PATH; ?>sns/uploadify/uploadify.swf',
        uploader: '<?php echo dr_member_url("api/sns_upload"); ?>',
        width: 80,
        height: 80,
        buttonImage: '<?php echo MEMBER_THEME_PATH; ?>sns/add-photo-multi.png',
        queueSizeLimit: 9,
        queueID: true,
        overrideEvents: ['onSelectError', 'onDialogClose'],
        onUploadSuccess : function(file, data, response) {
            // 解析JSON数据
            var jsondata = $.parseJSON(data);
            if (jsondata.status === 1) {
                // 添加附件ID表单项目
                var $sendAction = $('.attach_div');
                if ($sendAction['find']('.attach_ids').length === 0) {
                    $sendAction['append']('<input id="attach_ids" class="attach_ids" type="hidden" name="attach_ids" feedtype="image" value="" />');
                }
                dr_multimage.removeLoading(unid);
                $('#btn_'+unid).before($('<li class="dr_row_li" id="li_'+unid+'_'+file.index+'"><img src="'+jsondata.data.src+'" width="80" height="80" /><a href="javascript:;" onclick="dr_multimage.removeImage(\''+unid+'\', '+file.index+', '+jsondata.data.attach_id+')"><span class="del">删除</span></a></li>').fadeIn('slow'));
                // 动态设置数目
                dr_multimage.upNumVal(unid, 'inc');
                // 设置附件的值
                dr_multimage.upAttachVal('add', jsondata.data.attach_id);
            } else {
                dr_tips(jsondata.data);
            }
        },
        onSelectError: function (file, errorCode, errorMsg) {
            switch (errorCode) {
                case -100:
                    dr_tips('选择的上传数目超过，您还能上传'+errorMsg+'个图片');
                    break;
                case -110:
                    dr_tips("文件 [" + file.name + "] 大小超出系统限制的" + $('#uploadify_'+unid).uploadify('settings', 'fileSizeLimit') + "大小", 4);
                    break;
                case -120:
                    dr_tips("文件 [" + file.name + "] 大小异常");
                    break;
                case -130:
                    dr_tips("文件 [" + file.name + "] 类型不正确");
                    break;
            }
        },
        onFallback: function () {
            dr_tips('您未安装FLASH控件，无法上传！请安装FLASH控件后再试');
        },
        onUploadStart: function (file) {
            dr_multimage.addLoading(unid);
            // 验证是否能继续上传
            var len = $('#ul_'+unid).find('li').length - 1;
            if (len > total) {
                dr_multimage.removeLoading(unid);
                dr_tips('最多只能上传' + total + '个图片');
                // 停止上传
                $('#uploadify_'+unid).uploadify('stop');
                // 移除队列
                $('#uploadify_'+unid).uploadify('cancel', file.id);
            }
        }
    });
    // 加载更多
    $("#dr_loadmore a").click(function(){
        var page = $("#dr_page").val();
        $("#dr_loading").html("<div style='padding:30px;text-align:center;'><img src='<?php echo MEMBER_THEME_PATH; ?>images/loading.gif' /></div>");
        $.get("<?php echo $moreurl; ?>", {page:page}, function(data){
            if (data != "null") {
                $("#feed-lists").append(data);
                $("#dr_page").val(Number(page) + 1);
            }
            $("#dr_loading").html("");
        });
    });
});

</script>
<input name="page" id="dr_page" type="hidden" value="2" />
<script type="text/javascript" src="<?php echo MEMBER_THEME_PATH; ?>sns/sns.js"></script>
<div class="content clearfix">
	<?php if ($fn_include = $this->_include("navigator.html")) include($fn_include); ?>
    <div class="article">
    	<div class="section">
            <div class="title"><strong><?php echo $meta_name; ?></strong></div>
            <div class="main" style="min-height:500px;">


                <div class="send_weibo diy-send-weibo">
                    <div class="input">
                        <div class="input_before mb5" style="margin-bottom: 10px;">
                            <textarea id="dr_content" name="at" class="input_tips"></textarea>
                        </div>
                        <div class="action clearfix">
                            <div class="kind">
                                <div class="right release">
                                    <a class="btn-grey-white" href="javascript:;" onclick="dr_post()"><span>发布</span></a>
                                </div>
                                <div class="acts">
                                    <a class="face-block" href="javascript:;" onclick="$('.talkPop').hide();$('#emotions').show(200)"><i class="face"></i>表情</a>
                                    <a class="at-block" href="javascript:;" onclick="$('.talkPop').hide();$('#atchoose').show(200)"><i class="at"></i>好友</a>
                                    <a class="image-block" href="javascript:;" onclick="$('.talkPop').hide();$('#multi_image').show(200)"><i class="image"></i>图片</a>
                                    <a class="topic-block" href="javascript:;" onclick="$('.talkPop').hide();$('#huati').show(200);$('#huati_name').focus()"><i class="topic"></i>话题</a>
                                </div>

                                <div class="talkPop alL" id="emotions" style="">
                                    <div class="wrap-layer">
                                        <div class="arrow arrow-t"></div>
                                        <div class="talkPop_box">
                                            <div class="close hd">
                                                <a onclick="$('#emotions').hide(200)" class="ico-close" href="javascript:void(0)" title="关闭"> </a><span>常用表情</span>
                                            </div>
                                            <div class="faces_box" id="emot_content">
                                                <?php if (is_array($emotion)) { $count=count($emotion);foreach ($emotion as $t) { ?>
                                                <a href="javascript:void(0)" onclick="dr_emotion('<?php echo basename($t, '.gif'); ?>')"><img src="<?php echo MEMBER_URL; ?>/statics/emotions/<?php echo $t; ?>" width="24" height="24"></a>
                                                <?php } } ?>
                                                <div class="c"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="talkPop alL" id="atchoose" style="left:40px;">
                                    <div class="wrap-layer">
                                        <div class="arrow arrow-t"></div>
                                        <div class="talkPop_box">
                                            <div class="close hd">
                                                <a onclick="$('#atchoose').hide()" class="ico-close" href="javascript:void(0)"></a>
                                            </div>
                                            <div class="faces_box" id="at_content">
                                                <div id="friendchoose" class="friend clearfix">
                                                    <ul id="groups" class="groups">
                                                        <li id="dr_group_0" onclick="dr_find_user(0)" style="cursor:pointer" class="current">
                                                            <i class="ico-at-group mr5"></i>&nbsp;未分组
                                                        </li>
                                                        <?php if (is_array($group)) { $count=count($group);foreach ($group as $i=>$t) { ?>
                                                        <li id="dr_group_<?php echo $t['id']; ?>" onclick="dr_find_user(<?php echo $t['id']; ?>)" style="cursor:pointer">
                                                            <i class="ico-at-group mr5"></i>&nbsp;<?php echo dr_strcut($t['title'],15); ?>
                                                        </li>
                                                        <?php } } ?>
                                                    </ul>
                                                    <div id="groupusers" class="groupusers">
                                                        <ul id="group-2">
                                                            加载中...
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="talkPop alL" id="multi_image" style="left:100px;">
                                    <div class="wrap-layer">
                                        <div class="arrow arrow-t"></div>
                                        <div class="talkPop_box">
                                            <div class="close hd">
                                                <a onclick="$('#multi_image').hide()" class="ico-close" href="javascript:;"></a>
                                                <span>
                                                    共&nbsp;
                                                    <em id="upload_num_weibo">
                                                        0
                                                    </em>
                                                    &nbsp;张，还能上传&nbsp;
                                                    <em id="total_num_weibo">
                                                        9
                                                    </em>
                                                    &nbsp;张（按住ctrl可选择多张）
                                                </span>
                                            </div>

                                            <div class="img-list clearfix">
                                                <ul id="ul_weibo">
                                                    <li id="btn_weibo"><input style="display:none" id="uploadify_weibo" type="file" /></li>
                                                </ul>
                                            </div>

                                            <div class="attach_div" style="display:none"></div>

                                        </div>
                                    </div>
                                </div>

                                <div class="talkPop alL" id="huati" style="left:150px">
                                    <div class="wrap-layer">
                                        <div class="arrow arrow-t">
                                        </div>
                                        <div class="talkPop_box">
                                            <div class="close hd">
                                                <a onclick="$('#huati').hide()" class="ico-close" href="javascript:void(0)"></a>
                                            </div>
                                            <div class="video-box" id="video_content">
                                                <input type="text" style="width: 320px; margin-right:10px" id="huati_name" class="s-txt left" />
                                                <input type="button" onclick="dr_huati_add()" value="添加" class="btn-green-big" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="feed-nav">
                    <div class="tab-menu">
                    <ul>
                        <?php if ($ta) { ?><li class="current"><span><a href="<?php echo dr_member_url('sns/index', array('uid'=>$ta['uid'])); ?>">【<?php echo $ta['username']; ?>】的动态</a></span></li><?php }  if ($topic) { ?><li class="current"><span><a href="<?php echo dr_member_url('sns/topic', array('id'=>$topic['id'])); ?>">相关动态</a></span></li><?php } ?>
                        <li <?php if (!$type) { ?>class="current"<?php } ?>><span><a href="<?php echo dr_member_url('sns/index', array('type'=>0)); ?>">我关注的</a></span></li>
                        <li <?php if ($type==1) { ?>class="current"<?php } ?>><span><a href="<?php echo dr_member_url('sns/index', array('type'=>1)); ?>">我赞过的</a></span></li>
                        <li <?php if ($type==2) { ?>class="current"<?php } ?>><span><a href="<?php echo dr_member_url('sns/index', array('type'=>2)); ?>">我收藏的</a></span></li>
                        <li <?php if ($type==3) { ?>class="current"<?php } ?>><span><a href="<?php echo dr_member_url('sns/index', array('type'=>3)); ?>">全站动态</a></span></li>
                    </ul>
                    </div>
                </div>

                <div id="feed-lists" class="feed_lists clearfix">
                    <?php if ($fn_include = $this->_include("sns_data.html")) include($fn_include); ?>
                </div>

                <div class="clearfix"></div>
                <div class="bk10" style="margin-top:20px"></div>
                <div id="dr_loading"></div>
                <div id="dr_loadmore" class="load-more"><a href="javascript:;">查 看 更 多</a></div>


            </div>
        </div>
    </div>
</div>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>