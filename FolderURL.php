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
 * @author     Leo Unglaub <leo@leo-unglaub.net>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class FolderURL extends Frontend
{

	/**
	 * Parse url fragments to see if they are a parameter or part of the alias
	 *
	 * @param	array
	 * @return	array
	 * @link	http://www.contao.org/hooks.html?#getPageIdFromURL
	 */
	public function findAlias(array $arrFragments)
	{
		if (!count($arrFragments))
		{
			return $arrFragments;
		}
		
		$strAlias = array_shift($arrFragments);

		while( ($strFragment = array_shift($arrFragments)) !== null )
		{
			// Found an url parameter, stop generating the alias
			if (in_array($strFragment, $GLOBALS['URL_KEYWORDS']))
			{
				array_unshift($arrFragments, $strFragment);
				break;
			}

			$strAlias .= '/'.$strFragment;
		}

		$strPosition = 'none';
		$strLanguage = '';

		// if there is a slash at the third position, the first two could be a language identifier
		if (strpos($strAlias, '/') === 2)
		{
			$strLanguage = substr($strAlias, 0, 2);
			$strAlias = substr($strAlias, 3);
			$strPosition = 'left';
		}
		
		// if the 3th-last character is a dot, the last 2 could be the language identifier
		elseif (strrpos($strAlias, '.') === (strlen($strAlias)-3))
		{
			$strLanguage = substr($strAlias, -2);
			$strAlias = substr($strAlias, 0, -3);
			$strPosition = 'right';
		}

		if ($strLanguage != '')
		{
			$time = time();

			// Get the current page object
			$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE (id=? OR alias=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))
									  ->execute((is_numeric($strAlias) ? $strAlias : 0), $strAlias);
	
			// Check the URL of each page if there are multiple results
			if ($objPage->numRows > 1)
			{
				$objNewPage = null;
				$strHost = $this->Environment->host;
	
				while ($objPage->next())
				{
					$objCurrentPage = $this->getPageDetails($objPage->id);
					$objRootPage = $this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)$objCurrentPage->rootId);

					// Look for a root page whose domain name matches the host name
					if (($objCurrentPage->domain == $strHost || $objCurrentPage->domain == 'www.' . $strHost) && $objRootPage->language == $strLanguage && $objRootPage->languageAlias == $strPosition)
					{
						$strAlias = $objCurrentPage->id;
						break;
					}

					// Fall back to a root page without domain name
					if ($objCurrentPage->domain == '' && $objRootPage->language == $strLanguage && $objRootPage->languageAlias == $strPosition)
					{
						$strAlias = $objCurrentPage->id;
					}
				}
			}
		}

		array_unshift($arrFragments, $strAlias);

		return $arrFragments;
	}


	/**
	 * Append language identifier to the frontend URL if enabled in the root page settings
	 *
	 * @param	array
	 * @param	string
	 * @param	string
	 * @return	string
	 * @link	http://www.contao.org/hooks.html?#generateFrontendUrl
	 */
	public function appendLanguageIdentifier($arrRow, $strParams, $strUrl)
	{
		// No need to add the language if we don't use url aliases
		if ($GLOBALS['TL_CONFIG']['disableAlias'])
		{
			return $strUrl;
		}
		
		$intRootId = $arrRow['rootId'];
		if (!$intRootId)
		{
			$objPage = $this->getPageDetails($arrRow['id']);
			$intRootId = $objPage->rootId;
		}
		
		$objRoot = $this->Database->execute("SELECT * FROM tl_page WHERE id=".(int)$intRootId);
		
		if ($objRoot->languageAlias == 'left')
		{
			$strUrl = ($GLOBALS['TL_CONFIG']['rewriteURL'] ? '' : 'index.php/') . $objRoot->language . '/' . (strlen($arrRow['alias']) ? $arrRow['alias'] : $arrRow['id']) . $strParams . $GLOBALS['TL_CONFIG']['urlSuffix'];
		}
		elseif ($objRoot->languageAlias == 'right')
		{
			$strUrl = ($GLOBALS['TL_CONFIG']['rewriteURL'] ? '' : 'index.php/') . (strlen($arrRow['alias']) ? $arrRow['alias'] : $arrRow['id']) . '.' . $objRoot->language . $strParams . $GLOBALS['TL_CONFIG']['urlSuffix'];
		}
		
		return $strUrl;
	}


	/**
	 * Validate a folderurl alias.
	 * The validation is identical to the regular "alnum" except that it also allows for slashes (/).
	 *
	 * @param	string
	 * @param	mixed
	 * @param	Widget
	 * @return	bool
	 */
	public function validateRegexp($strRegexp, $varValue, Widget $objWidget)
	{
		if ($strRegexp == 'folderurl')
		{
			if (!preg_match('/^[\pN\pL \.\/_-]*$/u', $varValue))
			{
				$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['alnum'], $objWidget->label));
			}

			if (preg_match('#/' . implode('/|/', $GLOBALS['URL_KEYWORDS']) . '/|/' . implode('$|/', $GLOBALS['URL_KEYWORDS']) . '$#', $varValue, $match))
			{
				$strError = str_replace('/', '', $match[0]);
				$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['folderurl'], $strError, implode(', ', $GLOBALS['URL_KEYWORDS'])));
			}

			return true;
		}

		return false;
	}
}

