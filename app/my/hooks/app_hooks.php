<?php
/* 
* @Author: zan
* @Date:   2014-09-05 19:25:09
* @Last Modified by:   zan
* @Last Modified time: 2014-09-05 19:29:00
*/
class app_hooks {
    public $ci;
     
    /**
     * 构造函数
     */
    function __construct() {
        $this->ci = &get_instance();
    }
     
    // 第一个钩子
    function reg1($data) {
        log_message('error', '这是执行的是钩子1');
    }
     
    // 第二个钩子
    function reg2($data) {
        log_message('error', '这是执行的是钩子2');
    }
}
