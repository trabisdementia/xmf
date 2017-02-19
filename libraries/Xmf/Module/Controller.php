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
 * @version         $Id: $
 */
class Xmf_Module_Controller
{

    /**
     * @param  string $name
     * @param string  $dirname
     * @param string  $folder
     *
     * @return bool|Xmf_Module_Controller_Abstract
     */
    public static function getController($name, $dirname = 'system', $folder = '')
    {
        $folders = array('admin' => 'AdminController', 'blocks' => 'BlocksController');
        $name = strtolower(trim($name));
        $dirname = strtolower(trim($dirname));
        $path = 'controller' . (array_key_exists($folder, $folders) ? '/' . $folder : '');
        if (Xmf_Loader::loadFile(XOOPS_ROOT_PATH . "/modules/{$dirname}/class/{$path}/{$name}.php")) {
            $className = ucfirst($dirname) . ucfirst($name);
            $className .= array_key_exists($folder, $folders) ? $folders[$folder] : 'Controller';
            if (class_exists($className)) {
                $class = new $className();
                if ($class instanceof Xmf_Module_Controller_Abstract) {
                    return $class;
                }
            }
        }
        return false;
    }
}
/*
    public static function getController($name, $dirname = 'system', $folder = '')
    {
        $instanceName = strtolower("{$name}-{$dirname}-{$folder}");
        if (isset(self::$instances[$instanceName])) {
            return self::$instances[$instanceName];
        }
        $folders = array('admin' => 'AdminController', 'blocks' => 'BlocksController');
        $name = strtolower(trim($name));
        $dirname = strtolower(trim($dirname));
        $path = 'controller' . (array_key_exists($folder, $folders) ? '/' . $folder : '');
        if (Xmf_Loader::loadFile(XOOPS_ROOT_PATH . "/modules/{$dirname}/class/{$path}/{$name}.php")) {
            $className = ucfirst($dirname) . ucfirst($name);
            $className .= array_key_exists($folder, $folders) ? $folders[$folder] : 'Controller';
            if (class_exists($className)) {
                $class = new $className();
                if ($class instanceof Xmf_Module_Controller_Abstract) {
                    self::$instances[$instanceName] = $class;
                    return self::$instances[$instanceName];
                }
            }
        }
        self::$instances[$instanceName] = false;
        return self::$instances[$instanceName];
    }
*/