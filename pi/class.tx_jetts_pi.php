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
class tx_jetts_pi extends tx_jetts_parser {

  // Variables
  public $conf;         // Configuration variable
  
	function main($content,$conf)	{

    // get the key of the template
		if (isset($conf['mapping'])) {
			$keyTemplate = $conf['mapping_id'];
		} else {
			$keyTemplate = $this->getkeyTemplate();
		}
		
		if(!$keyTemplate) return '<h1>No template found</h1>';
		
		$mapping = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_jetts_mapping',
			'uid='.$keyTemplate.$this->cObj->enableFields('tx_jetts_mapping')
		);
		$mapping = $mapping[0]['mapping'];
		$mapping_id = md5($mapping);
		$mapping = 'jetts_'.$mapping_id.' {'."\n".$mapping;
		$mapping.= "\n".'}'."\n";

		$TSparser = t3lib_div::makeInstance('t3lib_TSparser');

		if (is_array($GLOBALS['TSFE']->tmpl->setup)) {
			foreach ($GLOBALS['TSFE']->tmpl->setup as $tsObjectKey => $tsObjectValue) {
				if ($tsObjectKey !== intval($tsObjectKey)) {
					$TSparser->setup[$tsObjectKey] = $tsObjectValue;
				}
			}
		}
		
		$TSparser->parse($mapping);
		$template = $this->parse($TSparser->setup['jetts_'.$mapping_id.'.']['template.']);
		unset($TSparser->setup['jetts_'.$mapping_id.'.']['template.']);

		$TEMPLATEConfig = array_merge(
			array(
				'template' => 'HTML',
				'template.' => array('value' => $template)
			),
			$TSparser->setup['jetts_'.$mapping_id.'.']
		);
		return $this->cObj->TEMPLATE($TEMPLATEConfig);
		
	}
	
	
	function getkeyTemplate() {

		// if a mapping is defined on page returns it
		if ($GLOBALS['TSFE']->page['tx_jetts_template'] != '') {
			return $GLOBALS['TSFE']->page['tx_jetts_template'];
		} else {
			// walks through rootline to find a mapping defined for a page or subpages
			foreach ($GLOBALS['TSFE']->rootLine as $level) {
				$p = $GLOBALS['TSFE']->sys_page->getPage($level['uid']);
				if ($p['tx_jetts_subtemplate'] != '') {
					return $p['tx_jetts_subtemplate'];
				} elseif ($p['tx_jetts_template'] != '') {
					return $p['tx_jetts_template'];
				}
			}
		}
		
		return false;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/pi/class.tx_jetts_pi.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/pi/class.tx_jetts_pi.php']);
}

?>
