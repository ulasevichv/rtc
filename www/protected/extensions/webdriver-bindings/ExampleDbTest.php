<?php

/**
 * This test is supposed to be run in Yii Framework testing environment.
 * It assumes bootstrap.php has already load Yii Application stack and 
 * Yii functions and object loader are in place.
 **/

define( 'TEST_BASE_URL', 'http://www.google.com/' );
 
Yii::import( 'ext.webdriver-bindings.CWebDriverDbTestCase' );
 
class ExampleDbTest extends CWebDriverDbTestCase {

	public $baseUrl = TEST_BASE_URL;

	public function testGoogle() {
		$this->get( 'http://www.google.com/' );
		
		$qElem = $this->findElementBy( LocatorStrategy::name, 'q' );
		$this->assertNotNull( $qElem, 'There is no "query" element!' );
		
		$qElem->sendKeys( array( 'yii framework' ) );
		
        $qElem->submit();
        sleep( 1 );
		
        $elem = $this->findElementBy( LocatorStrategy::className, 'vsc' );
		$this->assertNotNull( $elem, 'Results not found!' );
		
        $this->assertTrue( $this->isTextPresent( 'Yii Framework' ), 'The is no "Yii Framework" text on result page!' );
    }
}
