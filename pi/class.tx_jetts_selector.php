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

require_once (t3lib_extMgm::extPath('jetts') . 'pi/class.tx_jetts_parser.php');


/**
 * Plugin 'pi' for the 'jetts' extension.
 *
 * @author	 <contact@ilomedia.net>
 * @package	TYPO3
 * @subpackage	tx_jetts
 */
class tx_jetts_selector extends tx_jetts_parser {
	var $prefixId      = 'tx_jetts_selector';		// Same as class name
	var $scriptRelPath = 'pi/class.tx_jetts_selector.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'jetts';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 * shall not be called when using this plugin from another plugin
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{

    // get the key of the template
		if (isset($conf['mapping'])) {
      $keyTemplate = $conf['mapping'];
		} else {
			$keyTemplate = $this->getkeyTemplate();
		}
		
		// Get the configuration from the key
		$templateConf = $conf[$keyTemplate . '.'];

		// parse the configuration
		return $this->parse($templateConf);
	}
	
	
	function getkeyTemplate() {
	
		// if a mapping is defined on page return it
		if($GLOBALS['TSFE']->page['tx_jetts_template'] != '') {
			return $GLOBALS['TSFE']->page['tx_jetts_template'];
		}else{
			// walks through rootline to find a mapping defined for a page or subpages
			foreach($GLOBALS['TSFE']->rootLine as $level) {
				$p = $GLOBALS['TSFE']->sys_page->getPage($level['uid']);
				if($p['tx_jetts_subtemplate'] != '') {
					return $p['tx_jetts_subtemplate'];
				}elseif($p['tx_jetts_template'] != '') {
					return $p['tx_jetts_template'];
				}
			}
			
			return 'default';
		}
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/pi/class.tx_jetts_selector.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/pi/class.tx_jetts_selector.php']);
}

?>
