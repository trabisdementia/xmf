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
 * @version         $Id: Helper.php 10596 2012-12-29 01:51:05Z trabis $
 */
class Xmf_Module_Decorator
{
    /**
     * @param string      $name Decorator name
     * @param XoopsModule $module XoopsModule
     *
     * @return false|Xmf_Module_Decorator_Abstract
     */
    static function getDecorator($name, XoopsModule $module)
    {
        $uname = ucfirst($name);
        if (Xmf_Loader::loadFile(XMF_LIBRARIES_PATH . "/Xmf/Module/Decorator/{$uname}.php")) {
            $className = "Xmf_Module_Decorator_{$uname}";
            if (class_exists($className)) {
                $class = new $className($module);
                if ($class instanceof Xmf_Module_Decorator_Abstract) {
                    return $class;
                }
            }
        }
        return false;
    }
}