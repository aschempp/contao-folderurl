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
 * Hooks
 */
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = array('FolderURL', 'validateRegexp');
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('FolderURL', 'findAlias');


/**
 * URL Keywords
 */
$GLOBALS['URL_KEYWORDS'][] = 'items';
$GLOBALS['URL_KEYWORDS'][] = 'articles';
$GLOBALS['URL_KEYWORDS'][] = 'events';
$GLOBALS['URL_KEYWORDS'][] = 'page';
$GLOBALS['URL_KEYWORDS'] = array_unique(array_merge($GLOBALS['URL_KEYWORDS'], trimsplit(',', $GLOBALS['TL_CONFIG']['urlKeywords'])));

