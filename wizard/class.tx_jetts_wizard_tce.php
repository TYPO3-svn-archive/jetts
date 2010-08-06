<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003 Kasper Skaarhoj (kasper@typo3.com)
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

class tx_jetts_wizard_tce {

	public function getSubparts(&$params,&$pObj)    {
		$mapping = json_decode($params['row']['mapping_json']);
		if(!is_null($mapping)) {
			foreach($mapping->tags as $tag) {
				$params['items'][] = array($tag->TSKey,$tag->TSKey);
			}
		}
	}
	
	public function getColumns(&$params,&$pObj)    {
		global $TCA,$LANG;
		foreach($TCA['tt_content']['columns']['colPos']['config']['items'] as $col) {
			$params['items'][] = array($LANG->sL($col[0]),$col[1]);
		}
	}
	
	public function getPageTSconfig()    {
		global $TCA;
		$rootline = t3lib_BEfunc::BEgetRootLine(t3lib_div::_GP('id'));
		foreach($rootline as $page) {
			$mappingId = 0;
			$p = t3lib_BEfunc::getRecordWSOL('pages',$page['uid']);
			if ($p['tx_jetts_subtemplate_mapping'] != '') {
				$mappingId = $p['tx_jetts_subtemplate_mapping'];
			} elseif ($p['tx_jetts_template_mapping'] != '') {
				$mappingId = $p['tx_jetts_template_mapping'];
			}
			if($mappingId != 0) {
				$m = t3lib_BEfunc::getRecordWSOL('tx_jetts_mapping',$mappingId);
				if($m['show_columns'] != '') {
					$TSconfig = 'mod.SHARED.colPos_list = '.$m['show_columns'];

					$colPosList = explode(',',$m['show_columns']);
					$origColPostList = array();
					foreach($TCA['tt_content']['columns']['colPos']['config']['items'] as $col) {
						$origColPostList[] = $col[1];
					}
					$TSconfig .= '
TCEFORM.tt_content.colPos.removeItems = '.implode(',',array_diff($origColPostList,$colPosList));
					return $TSconfig;
				}
			}
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/wizard/class.tx_jetts_wizard_tce.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/wizard/class.tx_jetts_wizard_tce.php']);
}
?>
