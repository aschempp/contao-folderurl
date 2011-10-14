<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2008-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */
 

/**
 * Replace core callbacks
 */
array_insert($GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'], 0, array(array('tl_page_folderurl', 'verifyAliases')));

foreach( $GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'] as $i => $arrCallback )
{
	if ($arrCallback[1] == 'generateArticle')
	{
		$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][$i][0] = 'tl_page_folderurl';
		break;
	}
}

foreach( $GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'] as $i => $arrCallback )
{
	if ($arrCallback[1] == 'generateAlias')
	{
		$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'][$i] = array('tl_page_folderurl', 'generateFolderAlias');
		break;
	}
}


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] .= ';{folderurl_legend},languageAlias,folderAlias,subAlias';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['eval']['rgxp'] = 'folderurl';
$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['load_callback'][] = array('tl_page_folderurl', 'hideParentAlias');

$GLOBALS['TL_DCA']['tl_page']['fields']['languageAlias'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_page']['languageAlias'],
	'inputType'		=> 'radio',
	'default'		=> 'none',
	'options'		=> array('none', 'left', 'right'),
	'reference'		=> &$GLOBALS['TL_LANG']['tl_page']['languageAlias'],
	'eval'			=> array('tl_class'=>'w50" style="height:auto'),
);

$GLOBALS['TL_DCA']['tl_page']['fields']['folderAlias'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_page']['folderAlias'],
	'inputType'		=> 'checkbox',
	'eval'			=> array('tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_page']['fields']['subAlias'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_page']['subAlias'],
	'inputType'		=> 'checkbox',
	'eval'			=> array('tl_class'=>'w50'),
);


class tl_page_folderurl extends tl_page
{
	
	/**
	 * Only use the last portion of the page alias for the article alias
	 * @param	DataContainer
	 * @return	void
	 * @link	http://www.contao.org/callbacks.html#onsubmit_callback
	 */
	public function generateArticle(DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if (!$dc->activeRecord)
		{
			return;
		}
		
		$arrAlias = explode('/', $dc->activeRecord->alias);
		$dc->activeRecord->alias = array_pop($arrAlias);
		
		parent::generateArticle($dc);
	}


	/**
	 * Replaces the default contao core function to auto-generate a page alias if it has not been set yet.
	 * @param	mixed
	 * @param	DataContainer
	 * @return	mixed
	 * @link	http://www.contao.org/callbacks.html#save_callback
	 */
	public function generateFolderAlias($varValue, $dc)
	{
		$folderAlias = false;
		$autoAlias = false;

		// Generate an alias if there is none
		if ($varValue == '')
		{
			$objPage = $this->Database->executeUncached("SELECT * FROM tl_page WHERE id=".$dc->id);
			
			$autoAlias = true;
			$varValue = standardize($objPage->title);
		}

		if (strpos($varValue, '/') === false)
		{
			$objPage = $this->getPageDetails($dc->id);
			$objRoot = $this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)$objPage->rootId);

			if ($objRoot->folderAlias)
			{
				$objParent = $this->Database->executeUncached("SELECT * FROM tl_page WHERE id=".(int)$objPage->pid);

				if ($objParent->type != 'root')
				{
					$folderAlias = true;
					$varValue = $objParent->alias . '/' . $varValue;
				}
			}
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_page WHERE id=? OR alias=?")
								   ->execute($dc->id, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			$arrDomains = array();

			while ($objAlias->next())
			{
				$_pid = $objAlias->id;
				$_type = '';

				do
				{
					$objParentPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
													->limit(1)
													->execute($_pid);

					if ($objParentPage->numRows < 1)
					{
						break;
					}

					$_pid = $objParentPage->pid;
					$_type = $objParentPage->type;
				}
				while ($_pid > 0 && $_type != 'root');

				if ($objParentPage->numRows && ($objParentPage->type == 'root' || $objParentPage->pid > 0))
				{
					$arrDomains[] = ($objParentPage->languageAlias == 'left' || $objParentPage->languageAlias == 'right') ? ($objParentPage->dns.'/'.$objParentPage->language) : $objParentPage->dns;
				}
				else
				{
					$arrDomains[] = '';
				}
			}

			$arrUnique = array_unique($arrDomains);

			if (count($arrDomains) != count($arrUnique))
			{
				if (!$autoAlias)
				{
					throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'.($folderAlias ? 'Folder' : '')], $varValue));
				}

				$varValue .= '-' . $dc->id;
			}
		}

		return $varValue;
	}


	/**
	 * Hide the parent alias from the user when editing the alias field
	 * @param	string
	 * @param	DataContainer
	 * @return	string
	 * @link	http://www.contao.org/callbacks.html#load_callback
	 */
	public function hideParentAlias($varValue, $dc)
	{
		$objPage = $this->getPageDetails($dc->id);
		$objRoot = $this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)$objPage->rootId);

		if ($objRoot->folderAlias)
		{
			$objParent = $this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)$objPage->pid);

			if ($objParent->type != 'root')
			{
				$varValue = str_replace($objParent->alias.'/', '', $varValue);
			}
		}

		return $varValue;
	}
	
	
	/**
	 * Generate the page alias even if the alias field is hidden from the user
	 * @param DataContainer
	 * @return void
	 * @link http://www.contao.org/callbacks.html#onsubmit_callback
	 */
	public function verifyAliases($dc)
	{
		if (!$dc->activeRecord)
		{
			return;
		}

		if ($dc->activeRecord->alias == '')
		{
			$strAlias = $this->generateFolderAlias('', $dc);
			$this->Database->prepare("UPDATE tl_page SET alias=? WHERE id=?")->execute($strAlias, $dc->id);
		}
		
		$objPage = $this->getPageDetails($dc->id);
		$objRoot = $this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)$objPage->rootId);
		
		if ($objRoot->subAlias)
		{
			$arrChildren = $this->getChildRecords($dc->id, 'tl_page', true);
			
			if (count($arrChildren))
			{
				$objChildren = $this->Database->execute("SELECT * FROM tl_page WHERE id IN (" . implode(',', $arrChildren) . ") AND alias='' ORDER BY id=" . implode(' DESC, id=', $arrChildren) . " DESC");
				
				while( $objChildren->next() )
				{
					$strAlias = $this->generateFolderAlias('', (object)array('id'=>$objChildren->id, 'activeRecord'=>$objChildren));
					$this->Database->prepare("UPDATE tl_page SET alias=? WHERE id=?")->execute($strAlias, $objChildren->id);
				}
			}
		}
	}
}

