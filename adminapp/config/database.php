<?php
define('ENV', 'test');//develop 开发 test 测试  product 线上

function get_db_config()
{
	$_config                   = array();
	$_config['1']              = array();
	$_config['1']['dbhost']    = '127.0.0.1';
	$_config['1']['dbuser']    = 'root';
	$_config['1']['dbpw']      = '';
	$_config['1']['dbcharset'] = 'utf8';
	$_config['1']['pconnect']  = '0';
	$_config['1']['dbname']    = 'diancan';
	$_config['1']['tablepre']  = 'pre_';

    //db slave
    $_config['slave']                   = array();
    $_config['slave']['1']              = array();
    $_config['slave']['1']['dbhost']    = '127.0.0.1';
    $_config['slave']['1']['dbuser']    = 'root';
    $_config['slave']['1']['dbpw']      = '';
    $_config['slave']['1']['dbcharset'] = 'utf8';
    $_config['slave']['1']['pconnect']  = '0';
    $_config['slave']['1']['dbname']    = 'diancan';
    $_config['slave']['1']['tablepre']  = 'pre_';    

	return $_config;
}


function get_redis_config()
{
	$_config                        = array();
	$_config['prefix']              = '';
	$_config['redis']['server']     = '127.0.0.1';// redis 地址
	$_config['redis']['port']       = 6379;		// 端口
	$_config['redis']['pwd']        = '';			// 密码
	$_config['redis']['pconnect']   = 0;			// 长连接
	$_config['redis']['timeout']    = 0;		// 时间
	$_config['redis']['serializer'] = 1;			// 是否用这个压缩  	1 是
	$_config['redis']['selectdb']   = '0';			// 选择数据库

	return $_config;
}