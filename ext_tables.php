<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}


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

include_once(t3lib_extMgm::extPath('jetts').'class.tx_jetts_templateSelector.php');

?>
