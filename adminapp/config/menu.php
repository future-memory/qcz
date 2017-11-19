<?php
$menu = array(
	'menu' => array(
		'founder' => array(
			'name' => '管理',
			'key' => '',
			'submenu' => array(
				array(
					'name' => '权限管理',
					'mod'  => 'master',
					'url'  => 'index.php?mod=master',
					//'ctrl' => true
				),
				array(
					'name' => '站点管理',
					'mod'  => 'site',
					'url'  => 'index.php?mod=site'
				),		
				array(
					'name' => '运行记录',
					'mod'  => 'logs',
					'url'  => 'index.php?mod=logs'
				),
			),
		),
		'application' => array(
			'name'    => '应用',
			'key'     => '',
			'submenu' => array(
				array(
					'name' => '抽奖管理',
					'mod' => 'lottery',
					'url' => 'index.php?mod=lottery'
				),
				array(
					'name' => '商城管理',
					'mod' => 'shop',
					'url' => 'index.php?mod=shop'
				),
				array(
					'name' => '报名管理',
					'mod' => 'enroll',
					'url' => 'index.php?mod=enroll'
				),
			),
		),
		'conf' => array(
			'name'    => '配置',
			'key'     => '',
			'submenu' => array(
				array(
					'name' => '综合配置',
					'mod'  => 'misc',
					'url'  => 'index.php?mod=misc'
				),
				array(
					'name' => '促销抽奖',
					'mod'  => 'promotion',
					'url'  => 'index.php?mod=promotion'
				),
				
			),
		),
	),
);

return $menu;