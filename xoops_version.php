<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * XOOPS version
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         xmf
 * @since           0.1
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id$
 */

defined("XOOPS_ROOT_PATH") or die("XOOPS root path not defined");

$modversion['name'] = 'XMF';
$modversion['version'] = 1.1;
$modversion['description'] = '';
$modversion['author'] = "XMF";
$modversion['credits'] = "trabis";
$modversion['help'] = "";
$modversion['license'] = "GNU General Public License (GPL)";
$modversion['official'] = 0;
$modversion['dirname'] = "xmf";

$modversion['image'] = "images/xmf.png";

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "";
$modversion['adminmenu'] = "";

// Search
$modversion['hasSearch'] = 0;
// Menu
$modversion['hasMain'] = 0;

// Configs
$i = 0;
$modversion['config'][$i]['name'] = 'rewrite_enabled';
$modversion['config'][$i]['title'] = '_MI_XMF_CONFIG_REWRITE';
$modversion['config'][$i]['description'] = '_MI_XMF_CONFIG_REWRITE_DESC';
$modversion['config'][$i]['formtype'] = 'yesno';
$modversion['config'][$i]['valuetype'] = 'int';
$modversion['config'][$i]['default'] = 0;
