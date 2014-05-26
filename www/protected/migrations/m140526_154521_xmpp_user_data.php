<?php

class m140526_154521_xmpp_user_data extends CDbMigration
{
	public function up()
	{
		$db = Yii::app()->db;
		
		$query = "ALTER TABLE `user` ADD `xmppUserName` varchar(128) NOT NULL DEFAULT '' AFTER `password`";
		$db->createCommand($query)->execute();
		
		$query = "ALTER TABLE `user` ADD `xmppUserPassword` varchar(128) NOT NULL DEFAULT '' AFTER `xmppUserName`";
		$db->createCommand($query)->execute();
	}
	
	public function down()
	{
		echo "m140526_154521_xmpp_user_data does not support migration down.\n";
		return false;
	}
	
	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}
	
	public function safeDown()
	{
	}
	*/
}