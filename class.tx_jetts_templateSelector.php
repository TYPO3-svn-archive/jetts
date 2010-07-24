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

require_once(PATH_t3lib . 'class.t3lib_tsparser.php');

class tx_jetts_templateSelector {

	public $prefix = 'Static: ';

  public function main(&$params,&$pObj)    {

    // Creates a local TS parser
    $TSparser = t3lib_div::makeInstance('t3lib_TSparser');
    
    // Gets the page id
    $edit = t3lib_div::_GET('edit');
    $pages = $edit['pages'];
    $id = key($pages);
    
    // Gets the rootLine
    $rootLine = t3lib_BEfunc::BEgetRootLine($id);

    // Gets the config for all pages in the rootline.
    unset($rootLine[0]);
    foreach($rootLine as $page) {
      // Get the config of the page
      $row = t3lib_BEfunc:: getRecordRaw(
        'sys_template',
        'pid=' . $page['uid'] .
          t3lib_BEfunc::BEenableFields('sys_template') .
          t3lib_BEfunc::deleteClause('sys_template'),
        'config,basedOn'
      );

      // Parses the config if it exists
      $TSparser->parse($TSparser->checkIncludeLines(
        $this->getTyposcriptConfiguration($row)
        )
      );

    }

    // Gets the plugin. part
    $plugins = $TSparser->setup['plugin.'];
          
    // Gets the jetts_selector
    if (is_array($plugins['tx_jetts_selector.'])) {
      // Gets the label
      foreach ($plugins['tx_jetts_selector.'] as $key => $value) {
        $type = explode(',', $value['templateType']);
        $icon = ($value['icon'] ? '../' . $value['icon'] : '');
        foreach ($type as $valueType) {
          switch (trim($valueType)) {
            case 'sub':
              if ($params['field'] == 'tx_jetts_subtemplate') {
                $params['items'][] = array($pObj->sL($value['label']), substr($key, 0, -1), $icon);
              }
              break;
            case 'main':
            default:
              if ($params['field'] == 'tx_jetts_template') {
                $params['items'][] = array($pObj->sL($value['label']), substr($key, 0, -1), $icon);
              }
              break;
          }
        }
      }
    }
  }

  /**
   * Gets the typoscript configuration, including those in the included basis templates
   *
   * @param	array	$row	The row in which the typoscript configuration are searched.
   * @return	string The typoscript configuration
   */
  protected function getTyposcriptConfiguration($row) {
    $typoscriptConfiguration = '';

    if (is_array($row)) {
      // Checks if there are included basis templates
      if (!empty($row['basedOn'])) {

        $includedBasisTemplates = explode(',', $row['basedOn']);
        foreach($includedBasisTemplates as $includedBasisTemplate) {
          $templateRow = t3lib_BEfunc::getRecordRaw(
            'sys_template',
            'uid=' . $includedBasisTemplate .
            t3lib_BEfunc::BEenableFields('sys_template') .
            t3lib_BEfunc::deleteClause('sys_template'),
            'config,basedOn'
          );
          $typoscriptConfiguration .= $this->getTyposcriptConfiguration($templateRow);
        }
      }

      // Checks if there is a TS configuration
      if (!empty($row['config'])) {
        $typoscriptConfiguration .= $row['config'];
      }
    }
    return $typoscriptConfiguration;
  }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/class.tx_jetts_templateSelector.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/class.tx_jetts_templateSelector.php']);
}
?>
