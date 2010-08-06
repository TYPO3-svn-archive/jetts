<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

/* variable init */ 
$thisPath = t3lib_extMgm::extPath('jetts');
$_EXTCONF = unserialize($_EXTCONF);

t3lib_div::loadTCA('pages');

/*
 * BEGIN: instantiate wizard
 * only if version of TYPO3 is above 4.3 (we need extJs)
 */
if(t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
	$TCA['tx_jetts_mapping'] = array (
	    'ctrl' => array (
	        'title'     => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping',        
	        'label'     => 'title',    
	        'tstamp'    => 'tstamp',
	        'crdate'    => 'crdate',
	        'cruser_id' => 'cruser_id',
	        'default_sortby' => 'ORDER BY crdate',    
	        'delete' => 'deleted',    
	        'enablecolumns' => array (        
	            'disabled' => 'hidden',
	        ),
	        'dividers2tabs' => true,
	        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
	        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
	        'selicon_field' => 'thumbnail',
	        'selicon_field_path' => 'uploads/tx_jetts',
	    ),
	);
	
	t3lib_extMgm::addLLrefForTCAdescr('tx_jetts_mapping','EXT:jetts/locallang_csh_mapping.xml');
	
	$tempColumns = array (
	    'tx_jetts_template_mapping' => array (        
	        'exclude' => 1,        
	        'label' => 'LLL:EXT:jetts/locallang_db.xml:pages.tx_jetts_template_mapping',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_jetts_mapping',
				'foreign_table_where' => ($_EXTCONF['useGRSP'] == '1') ? 'AND (tx_jetts_mapping.pid=###STORAGE_PID### OR tx_jetts_mapping.pid IN (###PAGE_TSCONFIG_IDLIST###))' : '',
				'foreign_table_loadIcons' => true
			)
	    ),
	    'tx_jetts_subtemplate_mapping' => array (        
	        'exclude' => 1,        
	        'label' => 'LLL:EXT:jetts/locallang_db.xml:pages.tx_jetts_subtemplate_mapping',        
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_jetts_mapping',
				'foreign_table_where' => ($_EXTCONF['useGRSP'] == '1') ? 'AND (tx_jetts_mapping.pid=###STORAGE_PID### OR tx_jetts_mapping.pid IN (###PAGE_TSCONFIG_IDLIST###))' : '',
				'foreign_table_loadIcons' => true
			)
	    ),
	);
	
	include_once($thisPath.'wizard/class.tx_jetts_wizard_tce.php');
	
	t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('pages','--div--;Jetts,tx_jetts_template_mapping,tx_jetts_subtemplate_mapping');

}
/*
 * END: instantiate wizard
 */

/*
 * BEGIN: instantiate "Typoscript template selector"
 * only if set in configuration or TYPO3 verison lower than 4.3
 */
if($_EXTCONF['enableTyposcriptSelector'] || (t3lib_div::int_from_ver(TYPO3_version) < 4003000)) {
	$tempColumns = array (
	    'tx_jetts_template' => array (        
	        'exclude' => 1,        
	        'label' => 'LLL:EXT:jetts/locallang_db.xml:pages.tx_jetts_template',        
	        'config' => array (
	            'type' => 'select',
	            'itemsProcFunc' => 'tx_jetts_templateSelector->main',
	            'items' => array(
	            	'0' => ''
	            ),
	            'size' => 1,  
	            'minitems' => 0,
	            'maxitems' => 1,
	        )
	    ),
	    'tx_jetts_subtemplate' => array (        
	        'exclude' => 1,        
	        'label' => 'LLL:EXT:jetts/locallang_db.xml:pages.tx_jetts_subtemplate',        
	        'config' => array (
	            'type' => 'select',
	            'itemsProcFunc' => 'tx_jetts_templateSelector->main',
	            'items' => array(
	            	'0' => ''
	            ),
	            'size' => 1,  
	            'minitems' => 0,
	            'maxitems' => 1,
	        )
	    ),
	);
	
	include_once($thisPath.'class.tx_jetts_templateSelector.php');

	t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('pages','tx_jetts_template,tx_jetts_subtemplate');
}
/*
 * END: instantiate "Typoscript template selector"
 */

/*
 * BEGIN: add new columns to TCA if necessary
 */
$nbCols = intval($_EXTCONF['nbCols']);

if($_EXTCONF['localizeDefaultColumns']) {
	$colPosLabels = array(
		'0' => 'NORMAL',
		'1' => 'LEFT',
		'2' => 'RIGHT',
		'3' => 'BORDER'
	);
	for($i = 0; $i < 4; $i++) {
		$TCA['tt_content']['columns']['colPos']['config']['items'][$i] = array('LLL:EXT:user_jetts_ll/locallang_db.xml:colPos.I.'.$i,$i);
	}
}

if($nbCols > 4) {
	for($i = 4; $i < $nbCols; $i++) {
		$TCA['tt_content']['columns']['colPos']['config']['items'][$i] = array('LLL:EXT:user_jetts_ll/locallang_db.xml:colPos.I.'.$i,$i);
		$colPosLabels[$i] = 'COLUMN '.($i+1);
		$colPosList .= ','.$i;
	}
	t3lib_extMgm::addPageTSConfig('
		mod.SHARED.colPos_list = 1,0,2,3'.$colPosList.'
	');
}

$jetts_ll_path = t3lib_div::getFileAbsFileName('typo3conf/ext/user_jetts_ll/');

if(t3lib_extMgm::isLoaded('user_jetts_ll') && $colPosLabels) {

	$llXmlArray = array(
		'meta' => array(
			'type' => 'database',
			'description' => 'Language labels for columns'
		),
		'data' => array(
			'default' => array()
		)
	);
	
	reset($colPosLabels);
	foreach($colPosLabels as $key=>$value) {
		$llXmlArray['data']['default']['colPos.I.'.$key] = $value;
	}
	
	// preserve localized label if file already translated
	if($orig_xml = t3lib_div::getUrl($jetts_ll_path.'locallang_db.xml')) {
		$orig_xml = t3lib_div::xml2array($orig_xml);
		$llXmlArray = t3lib_div::array_merge_recursive_overrule($llXmlArray,$orig_xml);
	}
	
	$options = array(
		'parentTagMap' => array(
			'data' => 'languageKey',
			'languageKey' => 'label'
		)
	);
	
	$xmlContent .= t3lib_div::array2xml($llXmlArray,'',0,'T3locallang',0,$options);
	t3lib_div::writeFile(t3lib_extMgm::extPath('user_jetts_ll').'locallang_db.xml',$xmlContent);

}
/*
 * END: add new columns to TCA if necessary
 */

/*
 * BEGIN: control columns display from jetts mapping record
 */
if(t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
	t3lib_extMgm::addPageTSConfig('
	    '.tx_jetts_wizard_tce::getPageTSconfig().'
	');
}
/*
 * END: control columns display from jetts mapping record
 */

/*
 * 	BEGIN: create user_jetts_ll place-holder extension for localized files
 */
if(!@is_file($jetts_ll_path.'ext_emconf.php')) {
	t3lib_div::upload_copy_move($thisPath.'res/user_jetts_ll/ext_emconf.php.skel',$jetts_ll_path.'ext_emconf.php');
	t3lib_div::upload_copy_move($thisPath.'res/user_jetts_ll/locallang.xml.skel',$jetts_ll_path.'locallang.xml');
	t3lib_div::upload_copy_move($thisPath.'res/user_jetts_ll/ext_icon.gif.skel',$jetts_ll_path.'ext_icon.gif');
}

// if extension folder is created but extension is not loaded, warn user
if(@is_file($jetts_ll_path.'ext_emconf.php') && (!t3lib_extMgm::isLoaded('user_jetts_ll'))) {
	// if Typo3 >= 4.3 use a flash message to tell the user to install user_jetts_ll
	if(t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
		$message = t3lib_div::makeInstance('t3lib_FlashMessage', 
			'Jetts has automatically created the extension user_jetts_ll for you. This extension will allow you to the administrate the back-end column names (if this option is activated in Jetts) and your own labels for your templates using an extension such as llxmltranslate or lfeditor',
			'Install user_jetts_ll extension', 
			t3lib_FlashMessage::WARNING,
			FALSE
		);
		t3lib_FlashMessageQueue::addMessage($message);
	}
}
/*
 * 	END: create user_jetts_ll place-holder extension for localized files
 */

?>