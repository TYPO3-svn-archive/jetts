<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009  <contact@ilomedia.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Plugin 'parser' for the 'jetts' extension.
 *
 * @author	 <contact@ilomedia.net>
 * @package	TYPO3
 * @subpackage	tx_jetts
 */
class tx_jetts_parser {
	
	/**
	 * The main method of the PlugIn
	 * shall not be called when using this plugin from another plugin
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{

		return $this->parse($conf);
	}
	
	/**
	 * 
	 * @param	string		
	 * 
	 */
	public function parse($conf) {
		$starttime = microtime();
		
    // Set the localization keys
		if ($GLOBALS['TSFE']->config['config']['language'])	{
			$this->LLkey = $GLOBALS['TSFE']->config['config']['language'];
			if ($GLOBALS['TSFE']->config['config']['language_alt'])	{
				$this->altLLkey = $GLOBALS['TSFE']->config['config']['language_alt'];
			}
		}

		// Check if the cache is on. By defaut the cache is on
		$cache = (isset($conf['cache']) ? intval($conf['cache']) : 1);

		if ($cache == 1) {
      // Add the language key and serialize the configuration
			$hashConf = $conf;
			$hashConf['LLkey'] = $this->LLkey;
			$hashContent = serialize($hashConf);
			
			// Check if the content must be added to the hashContent. By defaut, it is added.
			$addContentToHash = (isset($conf['cache.']['addContentToHash']) ? intval($conf['cache.']['addContentToHash']) : 1);
			if ($addContentToHash == 1) {
				$content = $this->cObj->cObjGetSingle($conf['content'], $conf['content.'], 'content');
				$hashContent .= $content;
			}
			// Compute the hash key
			$hash = md5($hashContent);
			
			// Get the cached content
			$cachedContent = $GLOBALS['TSFE']->sys_page->getHash($hash);
			
			// If the cached context exists, return it
			if ($cachedContent)	{
				$cachedContent = unserialize($cachedContent);
				
				if (TYPO3_DLOG) t3lib_div::devLog('time taken', $this->extKey, '1', array('totaltime'=>(microtime() - $starttime)));

				return $cachedContent['content'];
			}
		}

		// The cache is either off or has returned nothing
		$this->conf = $conf;
		
		// Load the content if not already loaded. It is loaded  when cache=1 && addContentToHash=1.
		if (!$content) {
			$content = $this->cObj->cObjGetSingle($this->conf['content'], $this->conf['content.'], 'content');
		}
		
		// Process the content if it exists
		if ($content) {
			// first xpath magic
			$type = ($this->conf['type'] == 'XML') ? 'XML' : 'HTML';

			$this->initTemplate($content, false, $type);

			$this->createSubparts();
			$this->createMarks();
			$this->stdWraps();
			$this->substituteLinks();

			$content = $this->saveTemplate();

			// then locallang substitution
			$content = $this->substituteLocallangMarks($content);
			
			// Store the content in the cache
			if ($hash) {
				$GLOBALS['TSFE']->sys_page->storeHash($hash, serialize(
					array(
						'content' => $content
					)
				),'tx_jetts_parser');
			}			
			
			if (TYPO3_DLOG) t3lib_div::devLog('time taken', $this->extKey, '1', array('totaltime'=>(microtime() - $starttime)));
			
			return $content;

		} else {
			if (TYPO3_DLOG) t3lib_div::devLog('No template specified', $this->extKey, '3');
		}
	}
	
	/**
	 * create DOMDocument and DOMXpath objects on templateCode

	 * @param	string		$templateCode: The template
	 * @param	array		$conf: The local PlugIn configuration
	 * @param	string		$type: the type of template XML or HTML (defaults to HTML)
	 * @return	void
	 */
	public function initTemplate($templateCode, $conf = FALSE, $type = 'HTML') {
		
		// if you use this plugin in your own plugin, $conf will override TS conf
		if ($conf) {
      $this->conf = $conf;
    }
		
		$this->type = $type;		
		$this->DomDoc = new DOMDocument();
		
		// replace windows and mac newlines by unix newline
		$mac_newline = chr(13);
		$win_newline = chr(13) . chr(10);
		$templateCode = str_replace($win_newline, chr(10), $templateCode);
		$templateCode = str_replace($mac_newline, chr(10), $templateCode);
		
		// XML compliance : replaces & in templateCode by &amp; but preserve entities
		$templateCode = preg_replace('/&(?![a-zA-Z0-9#]+;{1})/', '&amp;', $templateCode);

		if ($this->type == 'XML') {
			$this->DomDoc->loadXML($templateCode);
		} else {
			$this->DomDoc->loadHTML($templateCode);
		}
		
		$this->xpath = new DOMXPath($this->DomDoc);
		
	}
	
	/**
	 * outputs the DOMDocument (to be used after all your parsing)
	 * 
	 * @return XML or HTML content
	 */
	public function saveTemplate() {
		if ($this->type = 'XML') {
			return $this->DomDoc->saveXML();
		} else {
			return $this->DomDoc->saveHTML();
		}
	}
	
	/**
	 * parses content with xptah expression and create subparts
	 * 
	 * example TS config : subparts.MAIN = //div[@id='main']
	 * 
	 * example output : <div id="main"><!--###MAIN### begin-->main content<!--###MAIN### end--></div>
	 *
	 * @return	DOMDocument with subparts inserted in relevant nodes
	 */
	public function createSubparts() {
		if ($this->conf['subparts.']) {
			foreach ($this->conf['subparts.'] as $key => $subpart) {
				$elements = $this->xpathQuery($subpart);
				if ($elements) {
					$beginMark = new DOMComment('###' . $key . '### begin');
					$endMark = new DOMComment('###' . $key . '### end');
					foreach ($elements as $el) {
						$el->insertBefore($beginMark, $el->firstChild);
						$el->appendChild($endMark);
					}
				}
			}
		}
	}
	
	/**
	 * parses content with xptah expression and create marks
	 * 
	 * example TS configs :
	 * this will replace an attribute : marks.LOGO_ALT = //p[@id='logo']/img/@alt
	 * this will replace the whole tag content : marks.LOGO = //p[@id='logo']
	 * 
	 * example outputs :
	 * - <p id="logo"><img>###LOGO###</p>
	 * - <p id="logo"><img src="..." alt="###LOGO_ALT###" /></p>
	 *
	 * @return	DOMDocument with marks inserted in relevant nodes
	 */
	public function createMarks() {
		if ($this->conf['marks.']) {
			foreach ($this->conf['marks.'] as $key => $mark) {

				$elements = $this->xpathQuery($mark);

				if ($elements) {
					foreach ($elements as $el) {
						$el->nodeValue = '###' . $key . '###';
					}
				}
			}
		}
	}
	
	/**
	 * parses content with xptah expression and execute stdWrap
	 * 
	 * example TS configs : 
	 * stdWraps.MYTEXT = //p[@id='someid']
	 * stdWraps.MYTEXT.crop = 20 | ...
	 * 
	 * example outputs :
	 * - <p id="someid"><img>cropped to 20 caract...</p>
	 *
	 * @return	DOMDocument with stdWrap executed on found nodes
	 */
	function stdWraps() {
		if ($this->conf['stdWraps.']) {
			foreach ($this->conf['stdWraps.'] as $key => $stdWrap) {
				if (!is_array($stdWrap)) {
					
					$elements = $this->xpathQuery($stdWrap);
					
					if ($elements) {
						foreach ($elements as $el) {
							$el->nodeValue = $this->cObj->stdWrap($el->nodeValue, $this->conf['stdWraps.'][$key . '.']);
						}
					}
				}
			}
		}
	}
	
	/**
	 * find any link starting with 'index.php?id='
	 * and generates nice links with typolink
	 * 
	 * as usual, 'config.linkVars = L' must be set in your TS
	 * if you want language attribute to be preserved
	 *
	 * @return	DOMDocument link replaced
	 */
	public function substituteLinks() {
		//select all attributes starting with 'index.php?id=' (is it too greedy?)
		$elements = $this->xpath->query('//*/@*[starts-with(.,\'index.php?id=\')]');
		
		foreach ($elements as $el) {
			$query = parse_url($el->nodeValue);
			parse_str($query['query'], $params);
			
			$parameter = $params['id'];
			
			unset($params['id']);
			$additionalParams = http_build_query($params);
			if($additionalParams) $additionalParams = '&' . $additionalParams;
			
			$url = $this->cObj->typoLink_URL(
				array(
					'parameter' => $parameter,
					'additionalParams' => $additionalParams,
				)
			);
			$el->nodeValue = str_replace('&', '&amp;', $url);
		}
		
		return $this->DomDoc;
	}
	
	/**
	 * loops through given locallang file and replace marks with matching names
	 *
	 * @param	string		$content: the template content
	 * @return	template content with substituted labels
	 */
	public function substituteLocallangMarks($content) {
		
		if ($this->conf['locallangFile']) {
			
			$LLfile = $this->conf['locallangFile'];
		
			// check if locallang file exists
			if($this->cObj->fileResource($LLfile)) {
				
				$markerArray = array();
								
				include_once($BACK_PATH.'typo3/sysext/lang/lang.php');
				$LLObj = t3lib_div::makeInstance('language');				
				$LLObj->init($this->LLkey);
				$LL = $LLObj->includeLLFile($LLfile, 0);
		
				if ($LL) {
					foreach($LL['default'] as $key => $label) {
						$markerKey = str_replace('.', '_', $key);
						$markerKey = strtoupper($markerKey);
						$markerArray['###' . $markerKey . '###'] = $LLObj->getLLL($key, $LL);
					}
				}
				
				$content = $this->cObj->substituteMarkerArray($content, $markerArray);
			} else {
				if (TYPO3_DLOG) t3lib_div::devLog('locallang file not found', $this->extKey, '3', $LLfile);
			}
		}
		
		return $content;
	}
	
	function xpathQuery($query) {
		$elements = @$this->xpath->query($query);
		if ($elements) {
			if ($elements->length == 0) {
				if (TYPO3_DLOG) t3lib_div::devLog('xpath returns no result', $this->extKey, '1', array($query));
			}
			return $elements;
		} else {
			if (TYPO3_DLOG) t3lib_div::devLog('invalid xpath query', $this->extKey, '2', array($query));
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/pi/class.tx_jetts_parser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/pi/class.tx_jetts_parser.php']);
}

?>
