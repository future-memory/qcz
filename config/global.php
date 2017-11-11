<?php

$_config = array();

//oauth config

$_config['weibo'] = array(
	'appid'        => '439445218',//'3341792891',
	'secret'       => '27a7ed80f79a17b393bd2e72f0abc996',//'3978d1e4597ab49c86db21d12fd4cb50',
	'callback_url' => DOMAIN.'index.php?mod=oauth&action=wbcallback',
);

$_config['weixin'] = array(
	'appid'        => 'wx2029f58dd54307c8',
	'secret'       => '13d94c3032fc571be998c5b756cf1c7b',
	'callback_url' => DOMAIN.'index.php?mod=oauth&action=wxcallback',
);

$_config['weapp'] = array(
	'appid'  => 'wxb25d4117afe3d02e',
	'secret' => '489eff15603c9f7a27a29e56156e4426'
);

return $_config;