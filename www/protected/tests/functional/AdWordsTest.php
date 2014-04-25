<?php

//require_once('z:/usr/local/php5/PEAR/PHPUnit/Extensions/Selenium2TestCase.php');

//class AdWordsTest extends PHPUnit_Extensions_Selenium2TestCase
//{
////	public static $browsers = array(
//////		array(
//////			'name'    => 'Firefox on Linux',
//////			'browser' => '*firefox /usr/lib/firefox/firefox-bin',
//////			'host'    => 'my.linux.box',
//////			'port'    => 4444,
//////			'timeout' => 30000
//////		),
////		array(
////			'name'    => 'firefox',
////			'browser' => '*firefox d:/Programs/Mozilla Firefox/firefox.exe',
////			'host'    => 'localhost',
////			'port'    => 4444,
////			'timeout' => 10000
////		),
////	);
//
//	protected function setUp()
//	{
////		$this->setBrowser('firefox');
//
//
////		$this->setBrowser('firefox', '*firefox3 d:/Programs/Mozilla Firefox/firefox.exe', 'localhost', 4444, 10000);
//
////		$this->setBrowser('Firefox', '*firefox3 d:/Programs/Mozilla Firefox/firefox.exe', 'localhost', 4444, 10000);
//
//
//		$this->setBrowserUrl('http://www.example.com/');
//
//
//	}
//
//	public function testTitle()
//	{
//		$this->url('http://www.example.com/');
//		$this->assertEquals('Example WWW Page', $this->title());
//	}
//
//}

class AdWordsTest extends WebTestCase
{
	private $rootTestUrl = 'http://adwords.google.com/d/AdPreview';
	private $jQueryFilePath = '';
	private $jQueryScript = '';
	private $numLinksLimit = 30;
	
	protected function setUp()
	{
		parent::setUp();
		
		$this->setBrowserUrl($this->rootTestUrl);
		$this->jQueryFilePath = 'Z:\home\mgh\www\themes\magellan\assets\js\jquery-1.9.1.min.js';
	}
	
	public function testIndex()
	{
		$fileName = $this->jQueryFilePath;
		$handle = fopen($fileName, 'r');
		$this->jQueryScript = fread($handle, filesize($fileName));
		fclose($handle);
		
		$this->open($this->rootTestUrl);
		
		$inputKeywordXpath = '//input[@class="aw-previewtool-searchterm-input"]';
		$btnPreviewXpath = '//button[contains(@class, "aw-previewtool-searchterm-button")][@gwtdebugid="preview-ads-button"]';
		$selectDomainXpath = '//select[@gwtdebugid="diagnose-keywords-domains"]';
		$selectLanguageXpath = '//select[@gwtdebugid="diagnose-keywords-languages"]';
		$linkLocationXpath = '//a[@gwtdebugid="location-edit-link"]';
		$inputLocationXpath = '//input[@gwtdebugid="geo-search-box"]';
		$tableLocationsXpath = '//table[@gwtdebugid="geotargets-table"]';
		
		$this->waitForElementPresent($inputKeywordXpath);
		
		$this->runScript($this->jQueryScript);
		
//		$this->pause(1000);
		
		$this->focus($inputKeywordXpath);
		$this->type($inputKeywordXpath, 'lawyer');
		$this->pause(100);
		$this->keyPressNative('16'); // Shift
		
		
		
//		
//		$this->click($inputKeywordXpath);
//		$this->focus($inputKeywordXpath);
//		
//		$this->pause(1000);
		
		
		
		
		
//		
//		
//		$this->pause(500);
//		
//		$this->runScript("$('button[gwtdebugid=\'preview-ads-button\']').removeAttr('disabled');");
		
		
		
		
		
//		$this->runScript("function test(value)
//		{
//			value = (typeof(value) != 'undefined' ? value : 'default');
//			
//			alert(value);
//		}");
		
//		$this->pause(100);
		
//		$this->runScript("test('new');");
		
//		$this->keyPress($inputKeywordXpath, '32');
		
//		$this->keyDown($inputKeywordXpath, "w");
//		$this->pause(1000);
//		$this->keyUp($inputKeywordXpath, "w");
		
//		$this->keyPressNative('l');
//		$this->keyPressNative('lawyer');
		
		
		
		
		
		
		
//		$this->click($btnPreviewXpath);
		
		$this->select($selectDomainXpath, 'value=com');
		$this->pause(20);
		
//		$requiredLanguageOptions = array('English', 'Английский');
		$requiredLanguageOptions = array('Английский');
		
		foreach ($requiredLanguageOptions as $requiredLanguageOption)
		{
			try
			{
				$this->select($selectLanguageXpath, 'value='.$requiredLanguageOption);
				
				break;
			}
			catch (Exception $ex) {}
		}
		
		$this->pause(20);
		
		$this->click($linkLocationXpath);
		$this->pause(20);
		
		$this->focus($inputLocationXpath);
		$this->type($inputLocationXpath, 'Los Angeles');
		$this->pause(100);
		$this->keyPressNative('16');
		$this->pause(400);
		
		// Get list of all matching locations.
		
		$this->runScript("function getMatchingLocations()
		{
			var jMatchingLocationsTable = $('div[gwtdebugid=\"geo-suggestions-pop-up\"] div[gwtdebugid=\"suggestions\"] table[gwtdebugid=\"geotargets-table\"]');
			
			var jRows = jMatchingLocationsTable.find('tbody > tr');
			
			var locations = [];
			
			for (var i = 0; i < jRows.length; i++)
			{
				var jRow = jRows.eq(i);
				
				var jLocationNameDiv = jRow.find('div[class=\"aw-geopickerv2-bin-target-name\"]');
				var jLocationTypeDiv = jRow.find('div[class=\"aw-geopickerv2-feature-type-box\"]');
				var jLocationLink = jRow.find('a[class=\"aw-geopickerv2-bin-action-link\"]');
				
				var locationName = String(jLocationNameDiv.html());
				var locationType = String(jLocationTypeDiv.html()).replace(' - ', '');
				
				locations.push({
					index : i,
					name : locationName,
					type : locationType
				});
				
				jLocationLink.attr('tempId', 'locationLink_'+i);
			}
			
			return JSON.stringify(locations);
		}");
		$this->pause(400);
		$locationsJson = $this->getEval("window.getMatchingLocations();");
		
//		Yii::log('LOCATIONS: '.$locationsJson, CLogger::LEVEL_TRACE, 'test');
		
		$locations = json_decode($locationsJson);
		
		if (count($locations) == 0)
		{
			Yii::log('ERROR: no suggested locations', CLogger::LEVEL_TRACE, 'test');
			return;
		}
		
		// Clicking first matching location.
		
		$firstLocationLinkXpath = '//a[@tempId="locationLink_'.$locations[0]->index.'"]';
		
		$this->click($firstLocationLinkXpath);
		$this->pause(100);
		
		// Calling preview.

		$this->click($btnPreviewXpath);
		$iframePreviewXpath = '//iframe[@gwtdebugid="diagnosticRootView-resultsPanel"]';
		
		$this->pause(1500);
		
		if (!$this->isElementPresent($iframePreviewXpath))
		{
			Yii::log('ERROR: no results iframe found', CLogger::LEVEL_TRACE, 'test');
			return;
		}
		
		// Getting iframe src.
		
		$this->runScript("function getResultsIframeSrc()
		{
			var jIframe = $('iframe[gwtdebugid=\"diagnosticRootView-resultsPanel\"]');
			
			return jIframe.attr('src');
		}");
		$this->pause(400);
		$resultsIframeSrc = $this->getEval("window.getResultsIframeSrc();");
		
		$this->open($resultsIframeSrc);
		
		// Getting links.
		
		$links = array();
		
		$pageIndex = 1;
		
		while (true)
		{
			if ($pageIndex != 1)
			{
				$linkXpath = '//table[@id="nav"]/tbody/tr/td[position()='.(1+$pageIndex).']/a';
				
				if (!$this->isElementPresent($linkXpath)) break;
				
				$this->click($linkXpath);
				$this->pause(500);
			}
			
			$pageLinks = $this->getPageLinks();
			$links = array_merge($links, $pageLinks);
			
			if (count($links) >= $this->numLinksLimit) break;
			
			$pageIndex++;
		}
		
		Yii::log('LINKS COUNT: '.count($links), CLogger::LEVEL_TRACE, 'test');
		Yii::log('LINKS: '.json_encode($links), CLogger::LEVEL_TRACE, 'test');
		
		$this->pause(8000);
		
		Yii::log('TEST COMPLETED', CLogger::LEVEL_TRACE, 'test');
	}
	
	private function getPageLinks()
	{
		$this->runScript($this->jQueryScript);
		$this->pause(200);
		
		$this->runScript("
		
		function trim(str)
		{
			if (typeof str !== 'string') str = str.toString();
			
			return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		}
		
		function getPageLinks()
		{
			var links = [];
			
			var jResultsDiv = $('#ires');
			
			jResultsDiv.css('border', '2px solid red');
			
			var jItemGroups = jResultsDiv.find('div.srg');
			
			for (var i = 0; i < jItemGroups.length; i++)
			{
				var jItemGroup = jItemGroups.eq(i);
				
				jItemGroup.css('border', '2px solid blue');
				
				var jItems = jItemGroup.find('li.g');
				
				for (var j = 0; j < jItems.length; j++)
				{
					var jItem = jItems.eq(j);
					
					jItem.css('border', '2px solid green');
					
					var jLink = jItem.find('div.s cite._Tc');
					
					jLink.css('border', '2px solid #000000');
					
					var linkUrl = jLink.html();
					
					var greaterThanIndex = linkUrl.indexOf('›');
					
					if (greaterThanIndex != -1)
					{
						linkUrl = linkUrl.substr(0, greaterThanIndex);
					}
					
					var doubleDotIndex = linkUrl.indexOf('..');
					
					if (doubleDotIndex != -1)
					{
						linkUrl = linkUrl.substr(0, doubleDotIndex);
					}
					
					linkUrl = linkUrl.replace('<b>', '');
					linkUrl = linkUrl.replace('</b>', '');
					
					if (linkUrl.lastIndexOf('/') == linkUrl.length - 1)
					{
						linkUrl = linkUrl.substr(0, linkUrl.length - 1);
					}
					
					linkUrl = trim(linkUrl);
					
					links.push({
						url : linkUrl
					});
				}
			}
			
			return JSON.stringify(links);
		}
		
		");
		$this->pause(400);
		$linksJson = $this->getEval("window.getPageLinks();");
		
		return json_decode($linksJson);
	}
}