/*活动滑动*/
function setDiv(name,cursel,n){
	for(i=1;i<=n;i++){
		var menu=document.getElementById(name+i);
		var con=document.getElementById("con_"+name+"_"+i);
		menu.className=i==cursel?"a":"";
		con.style.display=i==cursel?"block":"none";
	}
}
$(function(){
	
	//Banner效果
	$('.bannermenu').find('li:first').addClass('cur');
	$('.bannermenu ul li a').mouseover(function(){
		var curIndex = $(this).parent().index();
		$(this).parent().siblings().removeClass('cur');
		$(this).parent().addClass('cur');
		var currMl = curIndex*1920+960;
		$('.bannerimg').animate({marginLeft:-1*currMl},500);
		return false;
	});
	
	$('.banner .bn_prev').click(function(){
		var nextObj = $('.bannermenu li.cur').prev();
		if(nextObj.length == 0){
			nextObj = $('.bannermenu li').last();
		}
		nextObj.find('a').trigger('mouseover');
		return false;
	});
	$('.banner .bn_next').click(function(){
		var nextObj = $('.bannermenu li.cur').next();
		if(nextObj.length == 0){
			nextObj = $('.bannermenu li').first();
		}
		nextObj.find('a').trigger('mouseover');
		return false;
	});
	
	setInterval(function(){
		var nextObj = $('.bannermenu li.cur').next();
		if(nextObj.length == 0){
			nextObj = $('.bannermenu li:first');
		}
		nextObj.find('a').trigger('mouseover');
	},10000);
	
	

	//案例上下过度效果
	$('.caseindex ul li a').hover(function(){
		$(this).find('span').stop().animate({bottom:0},600);
	},function(){
		var spanheight = $(this).find('span').height();
		$(this).find('span').stop().animate({bottom:spanheight},600);
	});
	$('.case ul li a').hover(function(){
		$(this).find('span').stop().animate({bottom:0},500);
	},function(){
		var spanheight = $(this).find('span').height();
		$(this).find('span').stop().animate({bottom:spanheight},500);
	});
	//视频上下过度效果
	$('.videoone a').hover(function(){
		$(this).find('span').stop().animate({bottom:0},500);
	},function(){
		var spanheight = $(this).find('span').height();
		$(this).find('span').stop().animate({bottom:-spanheight},500);
	});
	
	//积分商品介绍上下过度效果
	$('.jfindexlist ul li a').hover(function(){
		$(this).find('.shopneirong').stop().animate({bottom:0},400);
	},function(){
		var spanheight = $(this).find('.shopneirong').height();
		$(this).find('.shopneirong').stop().animate({bottom:-spanheight},400);
	});
	
	//新闻切换效果	
	$('.newsmenu a').mouseover(function(){
		$(this).addClass('cur').siblings().removeClass('cur');
		$('.newsindexlist').hide();
		$('.newsindexlist').eq($(this).index()).show();
		return false;
	});
	
	//案例左侧案例切换
	$('.caseleftmenu a').mouseover(function(){
		$(this).addClass('cur').siblings().removeClass('cur');
		$('.caseleftimg a').hide().eq($(this).index()).show();
		return false;
	});
	setInterval(function(){
		var nextObj = $('.caseleftmenu a.cur').next();
		if(nextObj.length == 0){
			nextObj = $('.caseleftimg a').first();
		}
		nextObj.mouseover();
	},3000);
	
	//热门团购top10
	$('.hothotel>dl').hover(function(){
		$(this).addClass('cur').siblings().removeClass('cur');
	});
	//兑换记录top10
	$('.hotdh>dl').hover(function(){
		$(this).addClass('cur').siblings().removeClass('cur');
	});
	
	 /*团购倒计时*/
	if($('.tuanEndTime').length > 0){
	    setInterval(function(){
	      $(".tuanEndTime").each(function(){
	        var obj = $(this);
	        var enddateTime = $(this).attr('value');
	        var endTime = new Date(parseInt(obj.attr('value')) * 1000);
	        var nowTime = new Date();
	        var nMS=endTime.getTime() - nowTime.getTime();
	        var myD=Math.floor(nMS/(1000 * 60 * 60 * 24));
	        var myH=Math.floor(nMS/(1000*60*60)) % 24;
	        var myM=Math.floor(nMS/(1000*60)) % 60;
	        var myS=Math.floor(nMS/1000) % 60;
	        var myMS=Math.floor(nMS/100) % 10;
	        if(myD>= 0){
				var str = myD+"天"+myH+"小时"+myM+"分"+myS+"."+myMS+"秒";
	        }else{
				var str = "已结束！";	
			}
			obj.html(str);
	      });
	    }, 100);
    }
    
	
	/*首页团队*/
	$('.teamindex a').mouseenter(function(e) {
		$(this).children('span').slideToggle(800);
		$(this).children('img').fadeTo(800, 0.1);
	}).mouseleave(function(e) {
		$(this).children('span').slideToggle(800);
		$(this).children('img').fadeTo(800, 1.0);
	});
	/*
	$('.teamindex a').fadeIn(800);
	$('.teamindex a').hover(function(){
		$(this).find('img').fadeTo('slow', 0.1);
	},function(){
		$(this).find('img').fadeTo('slow', 1.0);
	});
	*/
	
});