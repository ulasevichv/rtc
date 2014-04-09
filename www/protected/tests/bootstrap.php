<?php

//// change the following paths if necessary
//$yiit=dirname(__FILE__).'/../../../framework/yiit.php';
//$config=dirname(__FILE__).'/../config/test.php';
//
//require_once($yiit);
//require_once(dirname(__FILE__).'/WebTestCase.php');
//
//Yii::createWebApplication($config);





////$yiit='/opt/yii/framework/yiit.php';
//$yiit=dirname(__FILE__).'/../../../framework/yiit.php';
//$config=dirname(__FILE__).'/../config/test.php';
//
//defined('YII_DEBUG') or define('YII_DEBUG', true);
//
//require_once($yiit);
//
//Yii::createWebApplication($config);
//Yii::getLogger()->autoFlush = 1;
//Yii::getLogger()->autoDump = true;
//
//require_once(dirname(__FILE__).'/WebTestCase.php');





defined('APP_TESTING') or define('APP_TESTING', true);
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 1);

function shutdown() {
	Yii::app()->end(0, false);
}

register_shutdown_function('shutdown');

$webroot = realpath(dirname(__FILE__).'/../../../');

$yiit = $webroot.'/framework/yiit.php';
$config = $webroot.'/www/protected/config/test.php';

require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');

Yii::createWebApplication($config);