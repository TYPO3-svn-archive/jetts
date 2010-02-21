<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

/* variable init */ 
$thisPath = t3lib_extMgm::extPath('jetts');
$_EXTCONF = unserialize($_EXTCONF);



/* add Jetts fields to Page form */
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


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages','--div--;Jetts,tx_jetts_template,tx_jetts_subtemplate');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:jetts/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi',
	''
),'list_type');



/* include template selector class */

include_once($thisPath.'class.tx_jetts_templateSelector.php');


/* add new columns to TCA if necessary */

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

if(!@is_file($jetts_ll_path.'ext_emconf.php')) {
	/* create place-holder extension for localized files */
	t3lib_div::upload_copy_move($thisPath.'res/user_jetts_ll/ext_emconf.php.skel',$jetts_ll_path.'ext_emconf.php');
	t3lib_div::upload_copy_move($thisPath.'res/user_jetts_ll/locallang.xml.skel',$jetts_ll_path.'locallang.xml');
}

// if extension folder created but extension is not loaded, warn user
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


?>