<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2009 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Wizard
 *
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   81: class SC_wizard_jetts
 *   99:     function init()
 *  123:     function main()
 *  285:     function printContent()
 *  298:     function checkEditAccess($table,$uid)
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

define('TYPO3_MOD_PATH', '../typo3conf/ext/jetts/wizard/');

$BACK_PATH='../../../../typo3/';

require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');

t3lib_BEfunc::lockRecords();











/**
 * Script Class for rendering the full screen RTE display
 *
 * @author	Grégory Duchesnes <contact@ilomedia.net>
 * @package TYPO3
 * @subpackage jetts
 */
class SC_wizard_jetts {

		// Internal, dynamic:
	/**
	 * document template object
	 *
	 * @var mediumDoc
	 */
	var $doc;
	var $content;				// Content accumulation for the module.

		// Internal, static: GPvars
	var $P;						// Wizard parameters, coming from TCEforms linking to the wizard.
	var $popView;				// If set, launch a new window with the current records pid.
	var $R_URI;					// Set to the URL of this script including variables which is needed to re-display the form. See main()




	/**
	 * Initialization of the class
	 *
	 * @return	void
	 */
	function init()	{
		
			// Setting GPvars:
		$this->P = t3lib_div::_GP('P');
		$this->P['field'] = 'mapping_json';
		$this->popView = t3lib_div::_GP('popView');
		$this->R_URI = t3lib_div::linkThisScript(array('popView' => ''));

			// Starting the document template object:
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		
		$this->relativePath = t3lib_extMgm::extRelPath('jetts');
		$this->pageRecord = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		
		$this->doc->setModuleTemplate(TYPO3_MOD_PATH.'template.html');
		$this->doc->divClass = '';	// Need to NOT have the page wrapped in DIV since if we do that we destroy the feature that the RTE spans the whole height of the page!!!
		$this->doc->form='<form action="'.$GLOBALS['BACK_PATH'].'tce_db.php" method="post" enctype="'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'].'" name="editform" id="TBE_EDITOR.formname" onsubmit="return TBE_EDITOR.checkSubmit(1);">';
	}

	/**
	 * Main function, rendering the document with the iframe with the RTE in.
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH;

			// translate id to the workspace version:
		if ($versionRec = t3lib_BEfunc::getWorkspaceVersionOfRecord($GLOBALS['BE_USER']->workspace, $this->P['table'], $this->P['uid'], 'uid'))	{
			$this->P['uid'] = $versionRec['uid'];
		}

			// If all parameters are available:
		if ($this->P['table'] && $this->P['field'] && $this->P['uid'] && $this->checkEditAccess($this->P['table'],$this->P['uid']))	{

				// Getting the raw record (we need only the pid-value from here...)
			$rawRec = t3lib_BEfunc::getRecord($this->P['table'],$this->P['uid']);

				// Fetching content of record:
			$trData = t3lib_div::makeInstance('t3lib_transferData');
			$trData->lockRecords=1;
			$trData->fetchRecord($this->P['table'],$this->P['uid'],'');
			
				// Getting the processed record content out:
			reset($trData->regTableItems_data);
			$rec = current($trData->regTableItems_data);
			$rec['uid'] = $this->P['uid'];
			$rec['pid'] = $rawRec['pid'];
			
			if($rec['llxml'] != '') {
				$LL = $LANG->includeLLFile($rec['llxml'], 0);
				$LLList = array();
				foreach($LL['default'] as $key=>$value) {
					$LLList[] = array($key,$key);
				}
			}
			$TSparser = t3lib_div::makeInstance('t3lib_TSparser');
			$TSparser->regComments = true;
			$TSparser->parse($rec['mapping']);
			
				// Setting JavaScript, including the pid value for viewing:
			$this->doc->JScode = $this->doc->wrapScriptTags('
					function jumpToUrl(URL,formEl)	{	//
						if (document.editform)	{
							if (!TBE_EDITOR.isFormChanged())	{
								window.location.href = URL;
							} else if (formEl) {
								if (formEl.type=="checkbox") formEl.checked = formEl.checked ? 0 : 1;
							}
						} else window.location.href = URL;
					}
					TBE_EDITOR.formname = "editform";
					TBE_EDITOR.formnameUENC = "'.rawurlencode('editform').'";
					var htmlTemplate = \''.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$rec['html'].'?'.uniqid().'\';
					var LLList = '.((!empty($LLList) ? json_encode($LLList) : '[]')).'
					var mapping_json = '.((json_decode($rec['mapping_json'])) ? $rec['mapping_json'].';' : '{tags:[],attrs:[]};')
				.($this->popView ? t3lib_BEfunc::viewOnClick($rawRec['pid'],'',t3lib_BEfunc::BEgetRootLine($rawRec['pid'])) : '').'
					
'
);
			
			$GLOBALS['TBE_STYLES']['extJS']['all'] = 'contrib/extjs/resources/css/ext-all.css';
			$this->doc->getPageRenderer()->loadExtJS();
			
			if(t3lib_div::int_from_ver(TYPO3_version) >= 4004000) {
				//$this->loadJavaScript($this->relativePath . 'wizard/js/miframe-2.1.2.js');
				$this->doc->getPageRenderer()->addJsFile($BACK_PATH.$this->relativePath . 'wizard/js/miframe-2.1.2.js');
			}else{
				//$this->loadJavaScript($this->relativePath . 'wizard/js/miframe-2.0.1.js');
				$this->doc->getPageRenderer()->addJsFile($BACK_PATH.$this->relativePath . 'wizard/js/miframe-2.0.1.js');
			}
			//$this->loadJavaScript($this->relativePath . 'wizard/js/wizard.js');
			$this->doc->getPageRenderer()->addJsFile($BACK_PATH.$this->relativePath . 'wizard/js/wizard.js');
			
			// provide language labels to ExtJS
			$llFile = t3lib_extMgm::extPath('jetts').'locallang_db.xml';
			$LOCAL_LANG = $LANG->includeLLFile($llFile,0);
			foreach($LOCAL_LANG['default'] as $key => $label) {
				$ek = explode('.',$key);
				if($ek[0] == 'tx_jetts_mapping' && $ek[1] == 'wizard')
				$this->doc->getPageRenderer()->addInlineLanguageLabel($ek[2],$LANG->getLLL($key,$LOCAL_LANG));
			}
		

				// Initialize TCeforms - for rendering the field:
			$tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
			$tceforms->initDefaultBEMode();	// Init...
			$tceforms->disableWizards = 1;	// SPECIAL: Disables all wizards - we are NOT going to need them.
			$tceforms->colorScheme[0]=$this->doc->bgColor;	// SPECIAL: Setting background color of the RTE to ordinary background

			$config = array(
				'config' => array(
					'type' => 'text'
				),
				'itemFormElValue' => $rec['mapping_json'],
				'itemFormElName' => 'data[tx_jetts_mapping]['.$rec['uid'].'][mapping_json]',
			);
			$formContent = $tceforms->getSingleField_typeText(
				'tx_jetts_mapping',
				'mapping_json',
				$rec,
				$config
			);
			
			$formContent = '


			<!--
				RTE wizard:
			-->
				<table border="0" cellpadding="0" cellspacing="0" width="'.$width.'" id="typo3-rtewizard">
					<tr>
						<td width="'.$width.'" colspan="2" id="c-formContent">'.$formContent.'</td>
						<td></td>
					</tr>
				</table>';

				// Adding hidden fields:
			$formContent.= '<input type="hidden" name="redirect" value="'.htmlspecialchars($this->R_URI).'" />
						<input type="hidden" name="_serialNumber" value="'.md5(microtime()).'" />';
			
			

				// Finally, add the whole setup:
			$this->content.=
				$tceforms->printNeededJSFunctions_top().
				$formContent;
				$tceforms->printNeededJSFunctions();
				
			//$this->content.= '<iframe src="/'.$rec['html'].'"></iframe>';
		} else {
				// ERROR:
			$this->content.=$this->doc->section($LANG->getLL('forms_title'),'<span class="typo3-red">'.$LANG->getLL('table_noData',1).'</span>',0,1);
		}

		// Setting up the buttons and markers for docheader
		$docHeaderButtons = $this->getButtons();
		$markers['CONTENT'] = $this->content;
		$markers['NOTES'] = (trim($rec['notes']) != '') ? '<!--'.$rec['notes'].'-->' : '' ;

		// Build the <body> for the module
		$this->content = $this->doc->startPage('');
		$this->content.= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);

	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}

	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return array all available buttons as an assoc. array
	 */
	protected function getButtons() {
		$buttons = array(
			'close' => '',
			'save' => '',
			'save_close' => ''
		);

		if ($this->P['table'] && $this->P['field'] && $this->P['uid'] && $this->checkEditAccess($this->P['table'],$this->P['uid'])) {
			$closeUrl = $this->P['returnUrl'];

			// Getting settings for the undo button:
			$undoButton = 0;
			$undoRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tstamp', 'sys_history', 'tablename=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->P['table'], 'sys_history') . ' AND recuid=' . intval($this->P['uid']), '', 'tstamp DESC', '1');
			if ($undoButtonR = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($undoRes))	{
				$undoButton = 1;
			}

			// Close
			$buttons['close'] = '<a href="#" onclick="jumpToUrl(\''.$closeUrl.'\');">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/closedok.gif') . ' class="c-inputButton" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc', 1) . '" alt="" />' .
					'</a>';

			// Save
			$buttons['save'] = '<a href="#" onclick="TBE_EDITOR.checkAndDoSubmit(1); return false;">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedok.gif') . ' class="c-inputButton" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" alt="" />' .
				'</a>';

			// Save & Close
			$buttons['save_close'] = '<input type="image" class="c-inputButton" onclick="' . htmlspecialchars('document.editform.redirect.value=\'' . $closeUrl . '\'; TBE_EDITOR.checkAndDoSubmit(1); return false;') . '" name="_saveandclosedok"' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/saveandclosedok.gif', '') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.saveCloseDoc', 1) . '" />';

		}

		return $buttons;
	}

	/**
	 * Checks access for element
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @return	void
	 */
	function checkEditAccess($table,$uid)	{
		global $BE_USER;

		$calcPRec = t3lib_BEfunc::getRecord($table,$uid);
		t3lib_BEfunc::fixVersioningPid($table,$calcPRec);
		if (is_array($calcPRec))	{
			if ($table=='pages')	{	// If pages:
				$CALC_PERMS = $BE_USER->calcPerms($calcPRec);
				$hasAccess = $CALC_PERMS&2 ? TRUE : FALSE;
			} else {
				$CALC_PERMS = $BE_USER->calcPerms(t3lib_BEfunc::getRecord('pages',$calcPRec['pid']));	// Fetching pid-record first.
				$hasAccess = $CALC_PERMS&16 ? TRUE : FALSE;
			}

				// Check internals regarding access:
			if ($hasAccess)	{
				$hasAccess = $BE_USER->recordEditAccessInternals($table, $calcPRec);
			}
		} else $hasAccess = FALSE;

		return $hasAccess;
	}
	
	protected function loadJavaScript($fileName) {
		$fileName = t3lib_div::resolveBackPath($this->doc->backPath . $fileName);
		$this->doc->JScode.= "\t" . '<script language="javascript" type="text/javascript" src="' . $fileName . '"></script>' . "\n";
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3conf/ext/jetts/wizard/wizard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3conf/ext/jetts/wizard/wizard.php']);
}



// Make instance:
$SOBE = t3lib_div::makeInstance('SC_wizard_jetts');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

?>