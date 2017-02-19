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
 * @version         $Id: Plugin.php 10605 2012-12-29 14:19:09Z trabis $
 */

class Xmf_Module_Plugin
{
    /**
     * @param string $dirname
     * @param string $pluginName
     * @param bool $force
     *
     * @return bool|Xmf_Module_Plugin_Abstract false if plugin does not exist
     */
    static function getPlugin($dirname, $pluginName = 'system', $force = false)
    {
        $inactiveModules = false;
        if ($force) {
            $inactiveModules = array($dirname);
        }
        $available = self::getPlugins($pluginName, $inactiveModules);
        if (!in_array($dirname, array_keys($available))) {
            return false;
        }
        return $available[$dirname];
    }

    /**
     * @param string $pluginName
     * @param array|bool $inactiveModules
     *
     * @return mixed
     */
    static function getPlugins($pluginName = 'system', $inactiveModules = false)
    {
        static $plugins = array();
        if (!isset($plugins[$pluginName])) {
            $plugins[$pluginName] = array();
            $xmf = Xmf::getInstance();

            //Load interface for this plugin
            if (!Xmf_Loader::loadFile($xmf->path("modules/{$pluginName}/class/plugin/interface.php"))) {
                return $plugins[$pluginName];
            }

            $dirnames = $xmf->getActiveModules();
            if (is_array($inactiveModules)) {
                $dirnames = array_merge($dirnames, $inactiveModules);
            }
            foreach ($dirnames as $dirname) {
                if (Xmf_Loader::loadFile($xmf->path("modules/{$dirname}/class/plugin/{$pluginName}.php"))) {
                    $className = ucfirst($dirname) . ucfirst($pluginName) . 'Plugin';
                    $interface = ucfirst($pluginName) . 'PluginInterface';
                    $class = new $className($dirname);
                    if ($class instanceof Xmf_Module_Plugin_Abstract && $class instanceof $interface) {
                        $plugins[$pluginName][$dirname] = $class;
                    }
                }
            }
        }
        return $plugins[$pluginName];
    }
}