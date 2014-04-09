<?php

Yii::import( 'ext.webdriver-bindings.CWebDriverTestCase' );

class AdWords2Test extends CWebDriverTestCase
{
	protected function setUp()
	{
		parent::setUp('localhost', 4444, 'firefox');
	}
	
	public function testOne()
	{
//		$this->setBrowserUrl('http://www.google.com');
		
//		$this->get('http://www.google.com/');
//		
//		$qElem = $this->findElementBy(LocatorStrategy::name, 'q');
//		$this->assertNotNull($qElem, 'There is no "query" element!');
//		
//		$qElem->sendKeys(array('yii framework'));
//		
//		$qElem->submit();
//		sleep(1);
//		
//		$elem = $this->findElementBy(LocatorStrategy::className, 'vsc');
//		$this->assertNotNull($elem, 'Results not found!');
//		
//		$this->assertTrue($this->isTextPresent('Yii Framework'), 'There is no "Yii Framework" text on result page!');
	}
}