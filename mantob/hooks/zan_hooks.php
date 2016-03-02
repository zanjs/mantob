<?php
/* 
* @Author: zan
* @Date:   2014-09-05 19:01:43
* @Last Modified by:   zan
* @Last Modified time: 2014-09-05 19:10:17
*/
class zan_hooks{

    public $ci;
    /* 
    * 构造函数
    */
   function __construct(){
        $this->ci = &get_instance(); 
   }
   function reg($data){
        if($data['username'] == 'admin'){
            $this->ci->member_msg('亲,admin 不可以注册');
        }
   }
}
