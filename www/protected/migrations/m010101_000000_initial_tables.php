<?php

class m010101_000000_initial_tables extends CDbMigration
{
	public function up()
	{
		$db = Yii::app()->db;
		
		$query = "CREATE TABLE IF NOT EXISTS `user` (".
			" `id` int(11) NOT NULL AUTO_INCREMENT,".
			" `firstName` varchar(128) NOT NULL,".
			" `lastName` varchar(128) NOT NULL,".
			" `email` varchar(128) NOT NULL,".
			" `password` varchar(128) NOT NULL,".
			" PRIMARY KEY (`id`)".
			" ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$db->createCommand($query)->execute();
		
		$query = "CREATE TABLE IF NOT EXISTS `yii_sourcemessage` (".
			" `id` int(11) NOT NULL AUTO_INCREMENT,".
			" `category` varchar(32) DEFAULT NULL,".
			" `message` text,".
			" PRIMARY KEY (`id`)".
			" ) ENGINE=MyISAM AUTO_INCREMENT=1840 DEFAULT CHARSET=utf8;";
		$db->createCommand($query)->execute();
		
		$query = "CREATE TABLE IF NOT EXISTS `yii_message` (".
			" `id` int(11) NOT NULL,".
			" `language` varchar(16) NOT NULL DEFAULT '',".
			" `translation` text,".
			" PRIMARY KEY (`id`,`language`),".
			" CONSTRAINT `yii_message_ibfk_1` FOREIGN KEY (`id`) REFERENCES `yii_sourcemessage` (`id`) ON DELETE CASCADE".
			" ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$db->createCommand($query)->execute();
		
		$query = "INSERT INTO `user` (`id`, `firstName`, `lastName`, `email`, `password`) VALUES".
			" (1, 'Admin', 'Admin', 'admin@nomail.com', '8e42a6338021335f2492983f8f178ad9:Jbx8TpAalSHIdaX8');";
		$db->createCommand($query)->execute();
	}
	
	public function down()
	{
		echo "m140409_093635_initial_tables does not support migration down.\n";
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