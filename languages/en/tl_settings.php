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


$GLOBALS['TL_LANG']['tl_settings']['urlKeywords']		= array('URL Keywords', 'Please enter additional (comma separated) keywords the FolderURL Extension should detect as variables.');
$GLOBALS['TL_LANG']['tl_settings']['folderAlias']		= array('Generate folder alias', 'Check here if you want to generate page alias including parent page alias (folder-like).');
$GLOBALS['TL_LANG']['tl_settings']['languageAlias']		= array('Append language to alias', 'Select if you want to append the language to the page alias (eg. alias.en).');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_settings']['languageAlias_ref']['none']		=	 'Do not append';
$GLOBALS['TL_LANG']['tl_settings']['languageAlias_ref']['left']		=	 'Append left (z.B. en/alias)';
$GLOBALS['TL_LANG']['tl_settings']['languageAlias_ref']['right']	=	 'Append right (z.B. alias.en)';

