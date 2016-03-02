<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo dr_lang('html-001', SITE_NAME); ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" href="<?php echo SITE_PATH; ?>mantob/statics/css/bootstrap.css" />
<link rel="stylesheet" href="<?php echo SITE_PATH; ?>mantob/statics/css/bootstrap-responsive.css" />
<link rel="stylesheet" href="<?php echo SITE_PATH; ?>mantob/statics/css/style.css" />
<link rel="stylesheet" href="<?php echo SITE_PATH; ?>mantob/statics/css/font-awesome/css/font-awesome.css" />
<link rel="stylesheet" href="<?php echo SITE_URL; ?>mantob/statics/css/table_form.css" />
<script type="text/javascript">var siteurl = "<?php echo SITE_PATH;  echo SELF; ?>";var memberpath = "<?php echo MEMBER_PATH; ?>";</script>
<script type="text/javascript" src="<?php echo SITE_PATH; ?>member/statics/js/<?php echo SITE_LANGUAGE; ?>.js"></script>
<script type="text/javascript" src="<?php echo SITE_PATH; ?>member/statics/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo SITE_PATH; ?>member/statics/js/jquery.artDialog.js?skin=default"></script> 
<script type="text/javascript" src="<?php echo SITE_PATH; ?>member/statics/js/validate.js"></script>
<script type="text/javascript" src="<?php echo SITE_PATH; ?>member/statics/js/admin.js"></script>
<script type="text/javascript" src="<?php echo SITE_PATH; ?>member/statics/js/mantob.js"></script>
<script type="text/javascript">
$(function(){
    //var is_member_hide = 0;
    if ($('#navigation').height() >= 80) {
        //$("#dr_member").hide();
        //is_member_hide = 1;
        $(".dr_module_menu").remove();
        $("#dr_select_module").show();
    }
    /*
	if ($(window).width() <= 1024) {
		if ($('.main-nav').width() > 600 && is_member_hide == 0) {
            $("#dr_top_nav").hide();
        }
		$("#dr_search_submit").hide();
	} else if ($(window).width() < 1400) {
		if ($('.main-nav').width() > 800 && is_member_hide == 0) {
            $("#dr_top_nav").hide();
        }
	}
	*/
	$("#dr_select_site, #dr_select_module").bind({
		'mouseover':function(){
			$(this).addClass("open");
		},
		'mouseout':function(){
			$(this).removeClass("open");
		}
	});
	$("#dr_member").bind({
		'mouseover':function(){
			$(this).addClass("open");
		},
		'mouseout':function(){
			$(this).removeClass("open");
		}
	});
    $('.toggle-nav').click(function(e){
        e.preventDefault();
        hideNav();
    });
    wSize();
    $(".toggle-subnav").click(function (e) {
        e.preventDefault();
        var $el = $(this);
        $el['parents'](".subnav").toggleClass("subnav-hidden").find('.subnav-menu,.subnav-content').slideToggle("fast");
        $el['find']("i").toggleClass("icon-angle-down").toggleClass("icon-angle-right");
    
        if($("#left").hasClass("mobile-show") || $("#left").hasClass("sidebar-fixed")){
            getSidebarScrollHeight();
            $("#left").getNiceScroll().resize().show();
        }
    });
	
	$("#sitelist li").click(function(){
		var id=$(this).attr('id');
		art.dialog.confirm("<font color=red><b><?php echo lang('html-386'); ?></b></font>", function(){
			// ajax提交验证
			$.ajax({type: "POST",dataType:"json", url: "<?php echo dr_url('site/select'); ?>", data: {id: id},
				success: function(data) {
					if (data.status == 1) {
						//验证成功
						$.dialog.tips(data.code.msg, 3, 1);
						$("#rightMain").attr('src', $("#rightMain").attr('src'));
						$("title").html(data.code.title);
						$("#brand").attr('href', data.code.url);
						$("#site_homepage").attr('href', data.code.url);
						$("#site_homepage").attr('title', data.code.site);
						$("#site_member").attr('href', data.code.url+'member/');
					} else {
						$.dialog.tips(data.code, 5);
					}
					return true;
				},
				error: function(HttpRequest, ajaxOptions, thrownError) {

				}
			});
			return true;
             // window.location.reload();
		});
       

	});
});
function getSidebarScrollHeight(){
    var $el = $("#left"),
    $w = $(window),
    $nav = $("#navigation");
    var height = $w['height']();

    if(($nav['hasClass']("navbar-fixed-top") && $w['scrollTop']() == 0) || $w['scrollTop']() == 0) height -= 40;

    if($el['hasClass']("sidebar-fixed") || $el['hasClass']("mobile-show")){
        $el['height'](height);
    }
}
function hideNav(){
    $("#left").toggle().toggleClass("forced-hide");
    if($("#left").is(":visible")) {
        $("#main").css("margin-left", $("#left").width());
    } else {
        $("#main").css("margin-left", 0);
    }

    if($('.dataTable').length > 0){
        var table = $.fn.dataTable.fnTables(true);
        if ( table.length > 0 ) {
            $(table).each(function(){
                if($(this).hasClass("dataTable-scroller")){
                    $(this).dataTable().fnDraw();
                }
            });
            $(table).dataTable().fnAdjustColumnSizing();
        }
    }

    if($(".calendar").length > 0){
        $(".calendar").fullCalendar("render");
    }
}
if(!Array.prototype.map)
	Array.prototype.map = function(fn,scope) {
	  var result = [],ri = 0;
	  for (var i = 0,n = this.length; i < n; i++){
		if(i in this){
		  result[ri++]  = fn.call(scope ,this[i],i,this);
		}
	}
	return result;
};

var getWindowSize = function(){
	return ["Height","Width"].map(function(name){
	  return window["inner"+name] ||
		document.compatMode === "CSS1Compat" && document.documentElement[ "client" + name ] || document.body[ "client" + name ]
	});
}
window.onload = function (){
	if(!+"\v1" && !document.querySelector) { // for IE6 IE7
	  document.body.onresize = resize;
	} else { 
	  window.onresize = resize;
	}
	function resize() {
		wSize();
		return false;
	}
}
function wSize(){
	var str=getWindowSize();
	var strs= new Array(); //定义一数组
	strs=str.toString().split(","); //字符分割
	var heights = strs[0]-80,Body = $('body');$('#rightMain').height(heights);
}
function _M(mid, sid, url, name) {
	$('.main-nav > li, .dropdown-menu > li').removeClass("active");
	$('#_M_'+mid).addClass("active");
	$(".d_menu").hide();
	$("#D_M_"+mid).show();
	_MP(sid, url);
}
function _lizzM(url) {
    $('.main-nav > li').removeClass("active");
    $(this).addClass("active");
    // var zurl = $(this).attr('href');
    $("#rightMain").attr('src', url);
    // alert(url);
  
}

function _MP(id, url) {
	$("#rightMain").attr('src', url);
	$(".subnav-menu > li").removeClass("dropdown");
	$("#_MP_"+id).addClass("dropdown");
    $("#_MP_"+id).parent().show();
    $("#_MP_"+id).parent().parent().attr('class', 'subnav');
    if (url.indexOf('http') == -1) {
        dr_loading();
    }
}
function _MAP(mid, sid, url) {
	$('.main-nav > li').removeClass("active");
	$('#_M_'+mid).addClass("active");
	$(".d_menu").hide();
	$("#D_M_"+mid).show();
	dr_clear_map();
	_MP(sid, url);
}
function logout(){
	if (confirm("<?php echo lang('html-017'); ?>"))
	top.location = '<?php echo dr_url("login/logout"); ?>';
	return false;
}
function dr_get_map() {
	$("#dr_backdrop").show();
	$("#modal-map").show();
}
function dr_clear_map() {
	$("#dr_backdrop").hide();
	$("#modal-map").hide();
}
function dr_loading() {
	$('.page-loading').remove();
	$('body').append('<div class="page-loading"><img src="<?php echo SITE_PATH; ?>mantob/statics/images/loading-spinner-grey.gif"/>&nbsp;&nbsp;<span><?php echo lang('html-699'); ?></span></div>');
}
</script>
</head>
<body scroll="no" style="overflow:hidden">
<div class="modal hide" id="modal-map" aria-hidden="true">
    <div class="modal-header">
        <button  onClick="dr_clear_map()" class="close" type="button">×</button>
        <h3 id="user-infos"><i class="icon-sitemap"></i> <?php echo lang('html-025'); ?></h3>
    </div>
    <div class="modal-body">
        <div class="row-fluid">
           <?php echo $sitemap; ?>
        </div>
    </div>
</div>
<div id="navigation">
    <div class="container-fluid">
        <a href="<?php echo SITE_URL; ?>" id="brand" target="_blank"><em><i style="color:#219805;">Ma</i>n<i style="color:#219805;">To</i>b</em></a>
        <a href="javascript:;" class="toggle-nav" rel="tooltip" data-placement="bottom"><i class="icon-reorder"></i></a>
        <ul class="main-nav">
            <?php $mark = $i=0;  if (is_array($top)) { $count=count($top);foreach ($top as $id=>$t) { ?>
                <li id="_M_<?php echo $id; ?>" class="<?php if ($i==0) { ?>active<?php }  if ($t['mark']) { ?> dr_module_menu<?php } ?>">
                    <a href="javascript:_M(<?php echo $id; ?>,'<?php echo $t['select']; ?>','<?php echo $t['selurl']; ?>','<?php echo $t['name']; ?>')">
                        <span><?php echo $t['name']; ?></span>
                    </a>
                </li>
            <?php $i++;$mark=1;  } } ?>
               <!--  <li class="z_formli active" >
                    <a href="javascript:_lizzM('moen.php?c=page&m=index')" >
                        <span><?php echo lang('html-1000'); ?></span>
                    </a>
                </li>
                
                <li class="z_formli" >
                    <a href="javascript:_lizzM('moen.php?c=navigator&m=index&type=0')" >
                        <span><?php echo lang('html-1002'); ?></span>
                    </a>
                </li>
                <li class="z_formli" >
                    <a href="javascript:_lizzM('moen.php?c=navigator&m=index&type=1')" >
                        <span><?php echo lang('html-1003'); ?></span>
                    </a>
                </li>
                <li class="z_formli" >
                    <a href="javascript:_lizzM('moen.php?c=block&m=index')" >
                        <span><?php echo lang('html-1004'); ?></span>
                    </a>
                </li>
                <li class="z_formli" >
                    <a href="javascript:_lizzM('moen.php?c=form&m=index')" >
                        <span><?php echo lang('html-1001'); ?></span>
                    </a>
                </li> -->
               
           <!--  <?php if ($mark) { ?>
            <li id="dr_select_module" style="display:none">
                <a href="#" data-toggle="dropdown" class="dropdown-toggle">
                    <span><?php echo lang('html-734'); ?></span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <?php $i=0;  if (is_array($top)) { $count=count($top);foreach ($top as $id=>$t) {  if ($t['mark']) { ?>
                    <li id="_M_<?php echo $id; ?>" >
                        <a href="javascript:_M(<?php echo $id; ?>,'<?php echo $t['select']; ?>','<?php echo $t['selurl']; ?>','<?php echo $t['name']; ?>')">
                            <span><?php echo $t['name']; ?></span>
                        </a>
                    </li>
                    <?php $i++;  }  } } ?>

                </ul>
            </li>
            <?php } ?> -->
            <?php if (count($mysite)>1) { ?>
            <li id="dr_select_site" class="">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <span><?php echo lang('html-008'); ?></span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" id="sitelist" style="max-height: 420px;overflow-y: auto;overflow-x:none;">
                    <?php if (is_array($mysite)) { $count=count($mysite);foreach ($mysite as $sid=>$name) { ?>
                    <li id="<?php echo $sid; ?>"><a href="javascript:;"><?php echo $name; ?></a></li>
                    <?php } } ?>
                </ul>
            </li>
            <?php } ?>
        </ul>
        <div class="user">
        	<ul class="icon-nav" id="dr_top_nav">
                <li class="dropdown">
                    <a class="dropdown-toggle" href="<?php echo SITE_URL; ?>" target="_blank" title="<?php echo SITE_NAME; ?>" id="site_homepage"><i class="icon-home"></i></a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="<?php echo dr_url('home/clear'); ?>" title="<?php echo lang('html-573'); ?>" target="right"><i class="icon-trash"></i></a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="<?php echo dr_url('home/cache'); ?>" title="<?php echo lang('html-434'); ?>" target="right"><i class="icon-refresh"></i></a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="<?php echo dr_url('route/index'); ?>" title="<?php echo lang('html-503'); ?>" target="right"><i class="icon-compass"></i></a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="javascript:;" onClick="dr_get_map()" title="<?php echo lang('html-025'); ?>"><i class="icon-sitemap"></i></a>
                </li>
            </ul>
            <div class="dropdown" id="dr_member">
                <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:;"><?php echo $admin['username']; ?>&nbsp;<img style="width:27px;height:27px;" src="<?php echo dr_avatar($uid); ?>" /></a>
                <ul class="dropdown-menu pull-right">
                    <li><a href="<?php echo MEMBER_URL; ?>index.php?c=api&m=member" target="_blank" id="site_member"><i class="icon-user"></i> <?php echo lang('html-007'); ?></a></li>
                    <li><a href="javascript:;" onClick="logout();"><i class="icon-signout"></i> <?php echo lang('html-005'); ?></a></li>
					<li><div class="dr_hr"></div></li>
                    <?php if ($member['adminid'] == 1) { ?>
                    <li><a href="<?php echo dr_url('check/index'); ?>" target="right"><i class="icon-zoom-out"></i> <?php echo lang('html-533'); ?></a></li>
                    <?php } ?>
                    <li><a href="javascript:;" onClick="dr_get_map()"><i class="icon-sitemap"></i> <?php echo lang('html-025'); ?></a></li>
					<li><a href="<?php echo dr_url('home/clear'); ?>" target="right"><i class="icon-trash"></i> <?php echo lang('html-573'); ?></a></li>
					<li><a href="<?php echo dr_url('route/index'); ?>" target="right"><i class="icon-compass"></i> <?php echo lang('html-503'); ?></a></li>
					<li><a href="<?php echo dr_url('home/cache'); ?>" target="right"><i class="icon-refresh"></i> <?php echo lang('html-434'); ?></a></li>
					<li><div class="dr_hr"></div></li>
                    <li><a href="http://www.zan3.com/" target="_blank"><i class="icon-cloud"></i> <?php echo lang('html-003'); ?></a></li>
                    <li><a href="http://www.zan3.com/" target="_blank"><i class="icon-book"></i> <?php echo lang('html-002'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid" id="content">
    <div id="left">
        <form action="http://www.zan3.com/" method="get" target="_blank" class="search-form" style="margin:9px 0 0;">
        	<input name="c" type="hidden" value="search" />
			<input name="m" type="hidden" value="index" />
            <div class="search-pane">
                <input type="text" name="keyword" placeholder="<?php echo lang('html-146'); ?>" />
                <button type="submit" id="dr_search_submit" title="<?php echo lang('html-146'); ?>"><i class="icon-search"></i></button>
            </div>
        </form>
        <?php echo $left; ?>
    </div>
    <div id="main">
        <iframe name="right" id="rightMain" src="<?php echo dr_url('home/main'); ?>" frameborder="false" scrolling="auto" style="border:none; margin-bottom:0px;" width="100%" height="auto" allowtransparency="true"></iframe>
        <div style="background-color:#EEEEEE; height:100px; padding-top:8px; text-align:right; padding-right:10px;margin-top: -5px;">
        &copy; 2013-<?php echo date('Y'); ?>&nbsp;&nbsp;<strong><a class="fine-cms" href="http://www.zan3.com/" target="_blank">mantob for<?php echo MAN_NAME; ?></a></strong>&nbsp;v<?php echo MAN_VERSION; ?> (<?php echo MAN_UPDATE; ?>)&nbsp;&nbsp;<span id="mantob_version"></span> &nbsp;&nbsp;&nbsp;&nbsp;
        </div>
    </div>
</div>
<div id="dr_backdrop" class="modal-backdrop in hide"></div>
</body>
</html>