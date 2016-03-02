/*!
 * jquery-powerFloat.js
 * jQuery 万能浮动层插件
 * http://www.zhangxinxu.com/wordpress/?p=1328
 * © by zhangxinxu  
 * 2010-12-06 v1.0.0 插件编写，初步调试
 * 2010-12-30 v1.0.1 限定尖角字符字体，避免受浏览器自定义字体干扰
 * 2011-01-03 v1.1.0 修复连续获得焦点显示后又隐藏的bug
       修复图片加载正则判断不准确的问题
 * 2011-02-15 v1.1.1 关于居中对齐位置判断的特殊处理
 * 2011-04-15 v1.2.0 修复浮动层含有过高select框在IE下点击会隐藏浮动层的问题，同时优化事件绑定   
 * 2011-09-13 v1.3.0  修复两个菜单hover时间间隔过短隐藏回调不执行的问题
 * 2012-01-13 v1.4.0 去除ajax加载的存储
                     修复之前按照ajax地址后缀判断是否图片的问题
      修复一些脚本运行出错
      修复hover延时显示时，元素没有显示但鼠标移出依然触发隐藏回调的问题
 * 2012-02-27 v1.5.0 为无id容器创建id逻辑使用错误的问题
       修复无事件浮动出现时同页面点击空白区域浮动层不隐藏的问题
      修复点击与hover并存时特定时候o.trigger报为null错误的问题
 * 2012-03-29 v1.5.1 修复连续hover时候后面一个不触发显示的问题
 * 2012-05-02 v1.5.2 点击事件 浮动框再次点击隐藏的问题修复
 * 2012-11-02 v1.6.0 兼容jQuery 1.8.2
 */
;(function($){$.fn.slidy=function(settings){if(this.length==0){debug('Selector invalid or missing!');return;}else if(this.length>1){return this.each(function(){$.fn.slidy.apply($(this),[settings]);});}
var opt=$.extend({},$.fn.slidy.defaults,settings),$this=$(this),id=this.attr('id'),elements=$this.children(opt.children),quantity=elements.length,images=(opt.children=='img')?elements:elements.find('img'),timer=0,isAnimate=false;if(id===undefined){id='slidy-'+$this.index();$this.attr('id',id);}
$this.data('options',opt).css({'cursor':opt.cursor,'height':opt.height+'px','overflow':'hidden','position':'relative','width':opt.width+'px'});elements.each(function(i){$(this).css({'position':'absolute','z-index':quantity-i}).attr('id',$this.attr('id')+'-'+(i+1))});images.attr({height:opt.height,width:opt.width}).css('border','0');if(opt.children=='a'&&opt.target!=''){elements.attr('target',opt.target);}
elements.hide().first().show();if(opt.menu){$menu=$('<ul/>',{id:id+'-slidy-menu','class':'slidy-menu'}).insertAfter($this);}
var stop=function(){clearTimeout(timer);},overBanner=function(){stop();},overMenu=function(thiz){stop();var $this=$(this),index=$this.index(),$current=$this.parent().children('.slidy-link-selected'),last=$current.index();if(index!=last){$current.removeClass('slidy-link-selected');$this.addClass('slidy-link-selected');change(last,index);}},outBanner=function(thiz){go($(thiz.target).parent('a').index());},outMenu=function(){var $this=$(this),index=$this.index(),$current=$this.parent().children('.slidy-link-selected'),last=$current.index();go(last);},clickMenu=function(thiz){stop();var $this=$(this),index=$this.index(),$current=$this.parent().children('.slidy-link-selected'),last=$current.index();if(index!=last){$current.removeClass('slidy-link-selected');$this.addClass('slidy-link-selected');}
go(index);};if(opt.menu){var target=(opt.target!='')?'target="'+opt.target+'"':'',menu='',parent,img;images.each(function(){img=$(this);parent=img.parent(opt.children);menu+='<li><a href="'+parent.attr(parent.is('a')?'href':'title')+'" '+target+'>'+img.attr('title')+'</a></li>';});$menu.html(menu);var space=parseInt((opt.width/quantity)+(quantity-1)),diff=opt.width-(space*quantity),links=$menu.children('li');if(opt.action=='mouseenter'){links.mouseenter(overMenu).mouseleave(outMenu);}else if(opt.action=='click'){links.click(clickMenu);}else{debug('action attribute must to be "click" or "mouseenter"!');return;}
links.css('width',space).first().addClass('slidy-link-selected').end().last().css({'border-right':'0','width':(space+diff)-(quantity-1)});if(opt.animation=='slide'||opt.animation=='fade'){links.mousemove(function(){var $this=$(this);if(!$this.hasClass('slidy-link-selected')){$this.mouseenter();}});}}
go(0);if(opt.pause){$this.mouseenter(overBanner).mouseleave(outBanner);}
function go(index){var total=quantity-1,last=null;if(index>total){index=0;last=total;}else if(index<=0){index=0;last=total;}else{last=index-1;}
change(last,index);timer=setTimeout(function(){go(++index);},opt.time);}
function change(last,index){if(!isAnimate){isAnimate=true;if(opt.animation=='fade'){elements.eq(last).fadeOut(opt.speed).end().eq(index).fadeIn(opt.speed,function(){selectMenu(index);dataSrc(index);isAnimate=false;});}else if(opt.animation=='slide'){elements.css('z-index',0).eq(index).css('z-index',quantity).slideDown(opt.speed,function(){elements.eq(last).hide();selectMenu(index);dataSrc(index);isAnimate=false;});}else{elements.eq(last).hide().end().eq(index).show(0,function(){selectMenu(index);dataSrc(index);isAnimate=false;});}}};function dataSrc(num){var imgSrc=elements.eq(num).find('img').attr("src");var imgDataSrc=elements.eq(num).find('img').attr("data-src");if(typeof(imgDataSrc)!="undefined"){if(imgDataSrc!=''){if(imgSrc.toString()!=imgDataSrc.toString()){elements.eq(num).find('img').attr("src",imgDataSrc);}}}};function selectMenu(index){if(opt.menu){$this.next('ul.slidy-menu').children().removeClass('slidy-link-selected').eq(index).addClass('slidy-link-selected');}};return $this;};function debug(message){if(window.console&&window.console.log){window.console.log(message);}};$.fn.slidy.defaults={action:'mouseenter',animation:'normal',children:'img',cursor:'default',height:200,menu:false,pause:false,speed:600,target:'',time:3600,width:500};})(jQuery);