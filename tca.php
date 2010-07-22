<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA['tx_jetts_mapping'] = array (
    'ctrl' => $TCA['tx_jetts_mapping']['ctrl'],
    'interface' => array (
        'showRecordFieldList' => 'hidden,title,description,thumbnail,html,llxml,mapping_json,mapping'
    ),
    'feInterface' => $TCA['tx_jetts_mapping']['feInterface'],
    'columns' => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'title' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.title',        
            'config' => array (
                'type' => 'input',    
                'size' => '30',    
                'eval' => 'required',
            )
        ),
        'description' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.description',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',    
                'rows' => '5',
            )
        ),
        'thumbnail' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.thumbnail',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'gif,png,jpeg,jpg',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => 'uploads/tx_jetts',
                'show_thumbs' => 1,    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'html' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.html',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'html,htm,tmpl',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => '',
                'minitems' => 1,
                'maxitems' => 1,
            )
        ),
        'llxml' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.llxml',        
            'config' => array (
                'type' => 'input',
            )
        ),
        'mapping' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.mapping',        
            'config' => array (
//				'type' => 'text',
        		'type' => 'user',
        		'pass_content' => '1',
        		'userFunc' => 'user_jetts_wizard->main',
        		'cols' => '30',
                'rows' => '5'
            )
        ),
		'mapping_json' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.mapping_json',        
            'config' => array (
//				'type' => 'text',
        		'type' => 'passthrough',
                'wizards' => array(
                    'JETTS' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 0,
                        'type'          => 'script',
                        'title'         => 'Jetts',
                        'icon'          => 'EXT:jetts/ext_icon.gif',
                        'script'        => 'EXT:jetts/wizard/wizard.php',
                    ),
                )
            )
        ),
		'header' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.header',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'html,htm,tmpl',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => '',
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
		'css' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.css',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'css',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => '',
                'minitems' => 0,
                'maxitems' => 99,
            )
        ),
        'js' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.js',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'js',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => '',
                'minitems' => 0,
                'maxitems' => 99,
            )
        ),
        'js_bottom' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.js_bottom',        
            'config' => array (
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'js',    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => '',
                'minitems' => 0,
                'maxitems' => 99,
            )
        ),
    ),
    'types' => array (
        '0' => array('showitem' => '
        	--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.general,hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3, thumbnail, html,
        	--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.mapping,mapping_json, mapping,
    		--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.ll,llxml'
    	)
    ),
    'palettes' => array (
        '1' => array('showitem' => '')
    )
);

//   		--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.header,header,
//  		--div--;LLL:EXT:jetts/locallang_db.xml:tx_jetts_mapping.tab.css_js,css,js,js_bottom,

?>