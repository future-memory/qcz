<?php

/*
* 首页
* 
* @author: 4061470@qq.com
*/


//应用路径
define('APP_ROOT', dirname(__FILE__));
//基础路径  （注意 不是框架路径）
define('BASE_ROOT', dirname(dirname(__FILE__)));

//获取配置
BASE_ROOT.'config/main.php';
$config = include(BASE_ROOT.'/config/main.php');

//调用框架
$nice  = BASE_ROOT.'/framework/nice.php';

require_once($nice);

Nice::initConsoleApplication($config);

set_time_limit(0);//不限制脚本运行时间

Nice::app()->run();
