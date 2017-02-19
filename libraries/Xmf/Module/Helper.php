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
class Xmf_Module_Helper
{
    /**
     * @param string $dirname
     *
     * @return bool|Xmf_Module_Helper_Abstract
     */
    static function getHelper($dirname = 'system')
    {
        static $modules = array();

        $dirname = strtolower($dirname);
        if (!isset($modules[$dirname])) {
            $modules[$dirname] = false;
            $xmf = Xmf::getInstance();
            if ($xmf->isActiveModule($dirname)) {
                //Load Module helper if available
                if (Xmf_Loader::loadFile($xmf->path("modules/{$dirname}/class/helper.php"))) {
                    $className = ucfirst($dirname);
                    if (class_exists($className)) {
                        $reflectionClass = new ReflectionClass($className);
                        if ($reflectionClass->isSubclassOf('Xmf_Module_Helper_Abstract')) {
                            $modules[$dirname] = $className::getInstance();
                        }
                    }
                } else {
                    //Create Module Helper
                    Xmf::getInstance()->registry()->set('xmf_module_helper_dirname', $dirname);
                    $modules[$dirname] = Xmf_Module_Helper_Dummy::getInstance();
                }
            }
        }
        return $modules[$dirname];
    }
}