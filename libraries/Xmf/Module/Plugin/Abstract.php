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
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: Abstract.php 10605 2012-12-29 14:19:09Z trabis $
 */

class Xmf_Module_Plugin_Abstract
{
    /**
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        Xmf::getInstance()->loadLanguage('modinfo', $dirname);
    }
}