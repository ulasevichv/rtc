<?php
return CMap::mergeArray(
	array(
		'basePath' => dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
		'name' => 'RTC',
		'theme' => 'main',
		'params' => array(
			'adminEmail' => 'admin@rtc.com',
			'adminEmailName' => 'RTC',
			'xmppServerIP' => '192.237.219.76',
//			'xmppServerIP' => 'teqcomm.ddns.net',
			'xmppAdminUsername' => 'admin',
			'xmppAdminPassword' => 'zxasqw12',
//			'opentok_api_key' => '44781472',
//			'opentok_api_secret' => '9c61ccfa9474404e8a6cd5daee02f2bf59e876b0',
			'opentok_api_key' => '44889622',
			'opentok_api_secret' => '2a4cd0242ecd8a1bc815765a1940d4ae214692e9',
		),
		
		// preloading 'log' component
		'preload' => array('log'),
		
		// autoloading model and component classes
		'import' => array(
			'application.models.*',
			'application.components.*',
			'ext.opentok.*',
		),
		
		'modules' => array(
			// uncomment the following to enable the Gii tool
			/*
			'gii'=>array(
				'class'=>'system.gii.GiiModule',
				'password'=>'Enter Your Password Here',
				// If removed, Gii defaults to localhost only. Edit carefully to taste.
				'ipFilters'=>array('127.0.0.1','::1'),
			),
			*/
		),
		
		// application components
		'components' => array(
			'user' => array(
				// enable cookie-based authentication
				'allowAutoLogin' => true,
			),
			// uncomment the following to enable URLs in path-format
			/*
			'urlManager' => array(
				'urlFormat' => 'path',
				'rules' => array(
					'<controller:\w+>/<id:\d+>'=>'<controller>/view',
					'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
					'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				),
			),
			*/
			'db' => array(
				'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
			),
			'openFireDb' => array(
				'connectionString' => 'mysql:host=192.237.219.76;dbname=openfire',
				'emulatePrepare' => true,
				'username' => 'root',
				'password' => '123456',
				'charset' => 'utf8',
				'tablePrefix' => '',
				'enableParamLogging' => true,
				'enableProfiling' => true,
				'class' => 'CDbConnection',
			),
			// uncomment the following to use a MySQL database
			/*
			'db' => array(
				'connectionString' => 'mysql:host=localhost;dbname=testdrive',
				'emulatePrepare' => true,
				'username' => 'root',
				'password' => '',
				'charset' => 'utf8',
			),
			*/
			'errorHandler' => array(
				// use 'site/error' action to display errors
				'errorAction' => 'site/error',
			),
			'log' => array(
				'class' => 'CLogRouter',
				'routes' => array(
					array(
						'class' => 'CFileLogRoute',
						'levels' => 'error, warning',
					),
					// uncomment the following to show log messages on web pages
					/*
					array(
						'class' => 'CWebLogRoute',
					),
					*/
				),
			),
		),
	),
	require(dirname(__FILE__) . '/main.custom.php')
);