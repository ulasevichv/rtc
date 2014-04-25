<?php

/**
 * This test is supposed to be run in Yii Framework testing environment.
 * It assumes bootstrap.php has already load Yii Application stack and 
 * Yii functions and object loader are in place.
 **/

Yii::import( 'ext.webdriver-bindings.CWebDriverTestCase' );

class ExampleTest extends CWebDriverTestCase {

	protected function setUp() {
		parent::setUp( 'localhost', 4444, 'firefox' );
	}

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
