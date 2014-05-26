<?php

class RegistrationController extends Controller
{
	public function actions()
	{
	}

	public function actionIndex()
	{
//		$this->render('index');
		
		$db = new CDbConnection('mysql:host=192.237.219.76;dbname=openfire', 'root', '123456');
		
		echo '<br/><b>$db: </b>';
		echo '<pre>';
		echo htmlspecialchars(print_r($db, true));
		echo '</pre>';
		
		$query = "SELECT * FROM `ofGroupUser`";
		$rows = $db->createCommand($query)->queryAll();
		foreach ($rows as $key => $row) { $rows[$key] = (object) $row; };

		echo '<br/><b>$rows: </b>';
		echo '<pre>';
		echo htmlspecialchars(print_r($rows, true));
		echo '</pre>';
	}
}