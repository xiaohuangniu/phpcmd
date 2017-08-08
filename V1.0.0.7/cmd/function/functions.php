<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 公共函数库
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.2
 + Initial-Time : 2017-5-8 18:48
 + Last-time    : 2017-5-8 18:48 + 小黄牛
 + Desc         : 用于V1.0.0.2版本后的公共函数存储
 +----------------------------------------------------------------------
*/

/**
 * 处理返回值
 * @param  string : $admin  管理员账号
 * @param  string : $txt    命令行
 * @param  bool
*/
function Admin_Log($admin, $txt){
    $url     = "config/operation_log/{$admin}.log";
    $content = $txt.'|-|'.date('Y-m-d H:i:s', time())."\r\n";
    return error_log($content, 3, $url);
}