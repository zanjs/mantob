<?php if (is_array($list)) { $count=count($list);foreach ($list as $t) { ?>
<dl class="feed_list" id="dr_row_<?php echo $t['id']; ?>">
    <dt class="face memberinfo_avatar">
        <a href="<?php echo dr_sns_feed_url($t['id']); ?>" event-node="face_card" uid="<?php echo $t['uid']; ?>"><img src="<?php echo dr_avatar($t['uid']); ?>" /></a>
    </dt>
    <dd class="content">
        <p class="hd">
            <a href="<?php echo dr_sns_feed_url($t['id']); ?>" class="name" event-node="face_card" uid="<?php echo $t['uid']; ?>"><?php echo $t['username']; ?></a>
            <span class="remark"></span>
        </p>
        <span class="contents">
            <?php echo dr_sns_content($t['content']);  $images = $t['images'] ? @explode('|', $t['images']) : '';  if ($images) { ?>
            <br>
            <div class="feed_img_lists" rel="small">
                <ul class="small">
                    <?php if (is_array($images)) { $count=count($images);foreach ($images as $img) { ?>
                    <li style="margin-top:10px">
                        <a href="<?php echo dr_get_file($img); ?>" target="_blank"><img class="imgicon" src="<?php echo dr_get_file($img); ?>" width="100" height="100"></a>
                    </li>
                    <?php } } ?>
                </ul>
            </div>
            <?php }  if ($t['repost_id'] && $repost = dr_sns_feed($t['repost_id'])) { ?>
            <dl class="comment">
                <dt class="arrow bgcolor_arrow"><em class="arrline">◆</em><span class="downline">◆</span></dt>
                <dd class="name">
                    <a event-node="face_card" uid="<?php echo $repost['uid']; ?>" href="javascript:;" class="name" target="_self">@<?php echo $repost['username']; ?></a></dd>
                <dd><?php echo dr_sns_content($repost['content']); ?></dd>
                <p class="info">
				    <span class="right">
					<a href="<?php echo dr_sns_feed_url($repost['id']); ?>">原文转发(<?php echo $repost['repost']; ?>)</a><i class="vline">|</i>
					<a href="<?php echo dr_sns_feed_url($repost['id']); ?>">原文评论(<?php echo $repost['comment']; ?>)</a>
				    </span>
                    <span><a href="<?php echo dr_sns_feed_url($repost['id']); ?>" class="date"><?php echo dr_fdate($repost['inputtime']); ?></a>
                        <span><?php echo $repost['source']; ?></span>
                    </span>
                </p>
            </dl>
            <?php } ?>
        </span>
        <p class="info">
            <span class="right">
                <a href="javascript:void(0);" onclick="dr_sns_repost(<?php echo $t['id']; ?>)">转发</a>
                <i class="vline">|</i>
                <a href="javascript:;" onclick="dr_sns_favorite(<?php echo $t['id']; ?>)" id="dr_favorite_<?php echo $t['id']; ?>"><?php if (@in_array($t['id'], $favorite)) { ?>取消收藏<?php } else { ?>收藏<?php } ?></a>
                <i class="vline">|</i>
                <a href="javascript:;" onclick="dr_sns_digg(<?php echo $t['id']; ?>)">赞(<span id="dr_digg_<?php echo $t['id']; ?>" style="margin:0"><?php echo $t['digg']; ?></span>)</a>
                <i class="vline">|</i>
                <a href="javascript:void(0)" onclick="dr_sns_list_comment(<?php echo $t['id']; ?>)">评论(<?php echo $t['comment']; ?>)</a>
            </span>
            <span>
                <span class="date">
                    <a href="<?php echo dr_sns_feed_url($t['id']); ?>" class="date"><?php echo dr_fdate($t['inputtime']); ?></a>
                </span>
                <span><?php echo $t['source']; ?></span>
                <?php if ($member['adminid'] || $t['uid']==$member['uid']) { ?>
                <em class="hover">
                    &nbsp;&nbsp;<a href="javascript:void(0)" onclick="dr_sns_delete(<?php echo $t['id']; ?>)">删除</a>
                </em>
                <?php } ?>
            </span>
        </p>
        <div class="repeat clearfix" id="dr_comment_<?php echo $t['id']; ?>" style="display:none">
            <div class="input" model-node="comment_textarea">
                <div class="input_before1" model-node="mini_editor">
                    <textarea class="input_tips" id="comment_content_<?php echo $t['id']; ?>" style="width:99%"></textarea>
                </div>
                <div class="action clearfix">
                    <div><a href="javascript:void(0);" onclick="dr_sns_comment_post(<?php echo $t['id']; ?>)" class="btn-green-small right"><span>回复</span></a></div>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="commentlist_<?php echo $t['id']; ?>" class="comment_lists">
                <img src="<?php echo MEMBER_THEME_PATH; ?>sns/loading.gif" />
            </div>
        </div>
    </dd>
</dl>
<?php } } ?>
<script>
    // 监听会员资料
    $(function(){
        $('a[event-node="face_card"]').mouseenter(function(){
            var uid = $(this).attr('uid');
            var obj = $(this);
            dr_facecard.init();
            dr_facecard.show(obj, uid);
        });
        $('a[event-node="face_card"]').mouseleave(function(){
            dr_facecard.hide();
        });
        $('a[event-node="face_card"]').blur(function(){
            dr_facecard.hide();
        });
        //
    });
</script>