<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout = '//layouts/default';
	
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu = array();
	
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs = array();
	
//	public function redirect($url, $terminate=true, $statusCode=302)
//	{
//		if (strpos($url, '/index.php') === 0) $url = str_replace('/index.php', '/', $url);
//		if (strpos($url, '//') === 0) $url = str_replace('//', '/', $url);
//		
//		if (is_array($url))
//		{
//			$route = (isset($url[0]) ? $url[0] : '');
//			$url = $this->createUrl($route, array_splice($url, 1));
//		}
//		
//		Yii::app()->getRequest()->redirect($url, $terminate, $statusCode);
//	}
//
//	public function createUrl($route,$params=array(),$ampersand='&')
//	{
//		if($route==='')
//			$route=$this->getId().'/'.$this->getAction()->getId();
//		elseif(strpos($route,'/')===false)
//			$route=$this->getId().'/'.$route;
//		if($route[0]!=='/' && ($module=$this->getModule())!==null)
//			$route=$module->getId().'/'.$route;
//		
////		echo $route;
////		die;
//		
//		return Yii::app()->createUrl(trim($route,'/'),$params,$ampersand);
//	}
}