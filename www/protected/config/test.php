<?php

return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'preload' => array('log'),
		'components' => array(
			'fixture' => array(
				'class' => 'system.test.CDbFixtureManager',
			),
			'errorHandler' => array(
				'errorAction'=>'site/error',
			),
			'assetManager' => array(
				'linkAssets' => true,
			),
//			'db' => array(
//				'connectionString' => 'mysql:host=localhost;dbname=qaces_new_test',
//				'username' => 'root',
//				'password' => '',
//				'charset' => 'utf8',
//				'tablePrefix' => '',
//				'emulatePrepare' => true,
//				'enableParamLogging' => true,
//				'enableProfiling' => true,
//			),
			'log' => array(
				'class' => 'CLogRouter',
				'routes' => array(
					array(
						'class' => 'CFileLogRoute',
						'levels' => 'error, warning',
						'categories' => '',
						'except' => 'test.*',
						'logFile' => 'testing_application_errors.log',
					),
					array(
						'class' => 'CFileLogRoute',
						'levels' => 'error, warning, info, trace, profile',
						'categories' => 'test.*',
						'logFile' => 'testing_application_debug.log',
					),
				),
			),
		),
	)
);