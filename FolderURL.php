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

		array_unshift($arrFragments, $strAlias);

		return $arrFragments;
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

