<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    LGPL
 */


class FolderURL extends Controller
{

	protected $arrKeywords;
	
	public function __construct()
	{
		parent::__construct();
		
		// Module Photoalbums
		if (in_array('photoalbums', $this->Config->getActiveModules()))
		{
			$GLOBALS['URL_KEYWORDS'][] = 'albums';
		}
		
		// Module Forum/Helpdesk
		if (in_array('helpdesk', $this->Config->getActiveModules()))
		{
			$GLOBALS['URL_KEYWORDS'] = array_merge($GLOBALS['URL_KEYWORDS'], array('category', 'topic', 'message', 'search', 'unread', 'markread'));
		}
			
		$this->arrKeywords = array_unique(array_merge($GLOBALS['URL_KEYWORDS'], trimsplit(',', $GLOBALS['TL_CONFIG']['urlKeywords'])));
	}
	
	
	function getPageIdFromURL($urlfragments)
	{
		if (is_string($urlfragments))
			return $urlfragments;
		
		$alias = array_shift($urlfragments);
		while( $fragment = array_shift($urlfragments) )
		{
			if (in_array($fragment, $this->arrKeywords))
			{
				return array_merge(array($alias, $fragment), $urlfragments);
			}
			
			$alias .= '/'.$fragment;
		}

		return array($alias);
	}
	
	
	function generateFolderAlias($varValue, DataContainer $dc)
	{
		if (!strlen($varValue) && ($GLOBALS['TL_CONFIG']['folderAlias'] || (strlen($GLOBALS['TL_CONFIG']['languageAlias']) && $GLOBALS['TL_CONFIG']['languageAlias'] != 'none')))
		{
			$this->import('Database');
			$objPage = $this->Database->prepare("SELECT pid,title,language FROM tl_page WHERE id=?")->execute($dc->id);
			
			$strAlias = standardize($objPage->title);
		
			if ($GLOBALS['TL_CONFIG']['folderAlias'] && $objPage->numRows && $objPage->pid > 0)
			{
				$objParent = $this->Database->prepare("SELECT pid, alias, language FROM tl_page WHERE id=?")->execute($objPage->pid);
				
				if ($objParent->numRows && $objParent->pid > 0 && strlen($objParent->alias) && in_array($objParent->alias, $this->arrKeywords) === false)
				{
					$strAlias = ($GLOBALS['TL_CONFIG']['languageAlias'] == 'right' && substr($objParent->alias, -3) == ('.'.$objParent->language) ? substr($objParent->alias, 0, -3) : $objParent->alias) . (substr($objParent->alias, -1) == '/' ? '' : '/') . $strAlias;
				}
			}
			
			if ($GLOBALS['TL_CONFIG']['languageAlias'] == 'right' && $objPage->numRows && strlen($objPage->language))
			{
				$strAlias .= '.'.$objPage->language;
			}
			elseif ($GLOBALS['TL_CONFIG']['languageAlias'] == 'left' && $objPage->numRows)
			{
				$objPage = $this->getPageDetails($dc->id);
				$objRootPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($objPage->rootId);
				
				$strAlias = (substr($strAlias, 0, 3) == ($objRootPage->language.'/') ? '' : (($objRootPage->numRows && strlen($objRootPage->language) ? $objRootPage->language : $objPage->language) . '/')) . $strAlias;
			}
		}
		else
		{
			$strAlias = $varValue;
		}
		
		try
		{
			$this->import('tl_page');
			$strAlias = $this->tl_page->generateAlias($strAlias, $dc);
		}
		catch (Exception $e)
		{
			$strAlias = $strAlias .= '.' . $dc->id;
		}

		return $strAlias;
	}
}

