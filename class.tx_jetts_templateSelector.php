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

  /**
   * itemsProcFunc for the mainTemplate selector
   *
   * @param	array	$params
   * @param	mixed	$pObj	.
   * @return	none
   */
  public function mainTemplateSelector(&$params,&$pObj) {

    // Creates a local TS parser
    $this->TSparser = t3lib_div::makeInstance('t3lib_TSparser');
    
    // Gets the page id
    $edit = t3lib_div::_GET('edit');
    $pages = $edit['pages'];
    $id = key($pages);

    // Gets the rootLine
    $rootLine = t3lib_BEfunc::BEgetRootLine($id);
    ksort($rootLine);

    // Gets the config for all pages in the rootline.
    unset($rootLine[0]);
    foreach($rootLine as $page) {
      // Get the config of the page
      $row = t3lib_BEfunc:: getRecordRaw(
        'sys_template',
        'pid=' . $page['uid'] .
          t3lib_BEfunc::BEenableFields('sys_template') .
          t3lib_BEfunc::deleteClause('sys_template'),
        'constants,config,basedOn,include_static_file'
      );

      // Parses the config if it exists
      $this->TSparser->parse(
        $this->getTyposcriptConfiguration($row)
      );
    }

    // Gets the plugin. part
    $plugins = $this->TSparser->setup['plugin.'];

    // Gets the jetts_selector
    if (is_array($plugins['tx_jetts_selector.'])) {
      // Intialises the selectors in pObj
      $pObj->jettsSelectors = array(
        'mainTemplate' => array(0 => ''),
        'subTemplate' => array(0 => '')
      );

      // Gets the label
      foreach ($plugins['tx_jetts_selector.'] as $key => $value) {
        $type = explode(',', $value['templateType']);
        $icon = $this->replaceTyposcriptConstants($value['icon']);
        $icon = ($icon ? '../' . $icon : '');
        $label = $this->replaceTyposcriptConstants($value['label']);
        foreach ($type as $valueType) {
          switch (trim($valueType)) {
            case 'sub':
              $pObj->jettsSelectors['subTemplate'][] = array($pObj->sL($label), substr($key, 0, -1), $icon);
              break;
            case 'main':
            default:
              $pObj->jettsSelectors['mainTemplate'][] = array($pObj->sL($label), substr($key, 0, -1), $icon);
              break;
          }
        }
      }
      $params['items'] = $pObj->jettsSelectors['mainTemplate'];
    }
  }

  /**
   * itemsProcFunc for the subTemplate selector
   *
   * @param	array	$params
   * @param	mixed	$pObj	.
   * @return	none
   */
  public function subTemplateSelector(&$params,&$pObj) {
    $params['items'] = $pObj->jettsSelectors['subTemplate'];
    unset($pObj->jettsSelectors);
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
      // Checks if there are TS contants
      if (!empty($row['constants'])) {
        $typoscriptConfiguration .= $row['constants'] . chr(10);
      }

      // Checks if there are included static files
      if (!empty($row['include_static_file'])) {
        $includedStaticFiles = explode(',', $row['include_static_file']);
        foreach($includedStaticFiles as $includedStaticFile) {
          $fileName =  t3lib_div::getFileAbsFileName($includedStaticFile) . 'setup.txt';
          if (@is_file($fileName)) {
            $typoscriptConfiguration .= $this->filterTyposcriptConfiguration(t3lib_div::getUrl($fileName));
          }
        }
      }
      
      // Checks if there are included basis templates
      if (!empty($row['basedOn'])) {
        $includedBasisTemplates = explode(',', $row['basedOn']);
        foreach($includedBasisTemplates as $includedBasisTemplate) {
          $templateRow = t3lib_BEfunc::getRecordRaw(
            'sys_template',
            'uid=' . $includedBasisTemplate .
            t3lib_BEfunc::BEenableFields('sys_template') .
            t3lib_BEfunc::deleteClause('sys_template'),
            'constants,config,basedOn,include_static_file'
          );
          $typoscriptConfiguration .= $this->getTyposcriptConfiguration($templateRow);
        }
      }
      
      // Checks if there is a TS configuration
      if (!empty($row['config'])) {
        $typoscriptConfiguration .= $this->filterTyposcriptConfiguration($row['config']);
      }
    }
    return $typoscriptConfiguration;
  }

  /**
   * Filter the typoscript configuration. It removes the typoscript if not related to tx_jetts_selector
   *
   * @param	string	$typoscript	The typoscript configuration to filter.
   * @return	string The typoscript configuration
   */
  protected function filterTyposcriptConfiguration($typoscript) {
    $typoscript = $this->TSparser->checkIncludeLines($typoscript);
    if (preg_match('/tx_jetts_selector[\s\t\n\r]*\{/', $typoscript)) {
      return $typoscript;
    } else {
      return '';
    }
  }

  /**
   * Replaces typoscript constants
   *
   * @param	string	$string String to process.
   * @return	string The string with replaced constants if any
   */

  protected function replaceTyposcriptConstants($string) {
    preg_match_all('/\{\$([^}]+)\}/', $string, $matches);
    foreach ($matches[0] as $matchKey => $match) {
      $string = str_replace($match, $this->TSparser->setup[$matches[1][$matchKey]], $string);
    }
    return $string;
  }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/class.tx_jetts_templateSelector.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jetts/class.tx_jetts_templateSelector.php']);
}
?>
