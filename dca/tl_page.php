<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
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
 * @copyright  Andreas Schempp 2008
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    LGPL
 */
 

/**
 * Patch tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['eval']['rgxp'] = 'url';


/**
 * Callbacks
 */
unset($GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'][0]);
$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'][] = array('FolderURL', 'generateFolderAlias');