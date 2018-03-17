<?php

//应用路径
define('APP_ROOT', dirname(__FILE__));
//基础路径  （注意 不是框架路径）
define('BASE_ROOT', dirname(__FILE__));

//应用个性化配置
//域名
define('DOMAIN', 'https://www.shop.com/');


//获取全局配置
$config = include(BASE_ROOT.'/config/main.php');
//调用框架
$nice  = BASE_ROOT.'/framework/nice.php';

require_once($nice);

Nice::initApplication($config);

Nice::app()->run();
