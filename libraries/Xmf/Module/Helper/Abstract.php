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
 * @version         $Id: Abstract.php 10888 2013-01-24 00:09:44Z trabis $
 */

defined("XOOPS_ROOT_PATH") or die("XOOPS root path not defined");

abstract class Xmf_Module_Helper_Abstract
{
    /**
     * @var array array containing references of instances
     */
    protected static $_instances = array();
    /**
     * @var string dirname of the module
     */
    protected $_dirname = '';
    /**
     * @var null|XoopsModule
     */
    private $_module = null;
    /**
     * @var bool
     */
    private $_debug = false;
    /**
     * @var array array of decorators Xmf_Module_Decorator_Abstract
     */
    private $_decorators = array();
    /**
     * @var float|int For logging proposes
     */
    private $_lastTime = 0;

    /**
     * Constructor
     * Class extending this class should use the init() method instead
     */
    final private function __construct()
    {
        // if called twice ....
        $calledClass = get_called_class();
        if (strtolower($calledClass) == 'xmf_module_helper_dummy') {
            $calledClass = Xmf::getInstance()->registry()->get('xmf_module_helper_dirname');
        }
        // throws an Exception
        if (isset(static::$_instances[$calledClass])) {
            throw new Exception("An instance of " . get_called_class() . " already exists.");
        }
        // init method via magic static keyword ($this injected)
        $this->_dirname = strtolower($calledClass);
        $this->_lastTime = $this->_microtime();
        static::init();
    }


    /**
     * no clone allowed, both internally and externally
     */
    final private function __clone()
    {
        throw new Exception("An instance of " . get_called_class() . " cannot be cloned.");
    }

    /**
     * @return Xmf_Module_Helper_Abstract
     */
    final public static function getInstance()
    {
        $calledClass = get_called_class();
        if (strtolower($calledClass) == 'xmf_module_helper_dummy') {
            $calledClass = Xmf::getInstance()->registry()->get('xmf_module_helper_dirname');
        }
        return isset(static::$_instances[$calledClass]) ? static::$_instances[$calledClass] : static::$_instances[$calledClass] = new static;
    }

    /**
     * Called after construction, use is optional
     */
    protected function init()
    {
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->_debug = (bool)$debug;
    }


    /**
     * @return null|XoopsModule
     */
    public function getModule()
    {
        if ($this->_module == null) {
            $this->_initModule();
        }
        if (!is_object($this->_module)) {
            $this->addLog("ERROR :: Module '{$this->_dirname}' does not exist");
        } else {
            $this->addLog('Loading module');
        }
        return $this->_module;
    }

    /**
     * @return Xmf
     */
    public function xmf()
    {
        return Xmf::getInstance();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getConfig($name)
    {
        $name = strtolower($name);
        $result = $this->xmf()->getModuleConfig($name, $this->_dirname);
        if (is_null($result)) {
            $this->addLog("ERROR :: Config '{$name}' does not exist");
            return $result;
        }
        $str = $result;
        if (is_array($result)) {
            $str = implode(',', $result);
        }
        $this->addLog("Getting config '{$name}' : " . $str);
        return $result;

    }

    /**
     * @return mixed
     */
    public function getConfigs()
    {
        $this->addLog("Getting all config");
        return $this->xmf()->getModuleConfigs($this->_dirname);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setConfig($name, $value = null)
    {
        $this->xmf()->setModuleConfig($name, $value, $this->_dirname);
        $this->addLog("Setting config '{$name}' : " . $value);
    }

    /**
     * @param string $name
     *
     * @return false|XoopsObjectHandler|XoopsPersistableObjectHandler
     */
    public function getHandler($name)
    {
        $name = strtolower($name);
        if ($result = $this->xmf()->getModuleHandler($name, $this->_dirname)) {
            $this->addLog("Getting handler '{$name}'");
            return $result;
        }
        $this->addLog("ERROR :: Handler '{$name}' does not exist");
        return $result;
    }

    /**
     * @param string $name
     *
     * @return bool|Xmf_Module_Decorator_Abstract
     */
    public function getDecorator($name)
    {
        $ret = false;
        $name = strtolower($name);
        if (!isset($this->_decorators[$name])) {
            $this->_decorators[$name] = Xmf_Module_Decorator::getDecorator($name, $this->getModule());
        }

        if (!$this->_decorators[$name]) {
            $this->addLog("ERROR :: Decorator '{$name}' does not exist");
        } else {
            $this->addLog("Getting decorator '{$name}'");
            $ret = $this->_decorators[$name];
        }
        return $ret;
    }

    public function disableCache()
    {
        $this->xmf()->appendConfig('module_cache', array($this->getModule()->getVar('mid') => 0), true);
        $this->addLog("Disabling module cache");
    }

    /**
     * @return bool
     */
    public function isCurrentModule()
    {
        if ($this->xmf()->getModuleDirname() == $this->_dirname) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isUserAdmin()
    {
        if ($this->xmf()->isUser()) {
            return $this->xmf()->user()->isAdmin($this->getModule()->getVar('mid'));
        }
        return false;
    }

    public function getUserGroups()
    {
        return $this->xmf()->isUser() ? $this->xmf()->user()->getGroups() : XOOPS_GROUP_ANONYMOUS;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function url($url = '')
    {
        //do not add / if $url is empty
        $url = $url ? '/' . $url : '';
        return $this->xmf()->url('modules/' . $this->_dirname . $url);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function path($path = '')
    {
        return $this->xmf()->path('modules/' . $this->_dirname . '/' . $path);
    }

    /**
     * @return XoopsDatabase
     */
    public function db()
    {
        return $this->xmf()->db();
    }

    /**
     * @return MyTextSanitizer
     */
    public function myts()
    {
        return $this->xmf()->myts();
    }

    /**
     * Function to redirect a user to certain pages
     *
     * @param        $url
     * @param int    $time
     * @param string $message
     * @param bool   $addredirect
     * @param bool   $allowExternalLink
     *
     * @return void
     */
    public function redirect($url, $time = 3, $message = '', $addredirect = true, $allowExternalLink = false)
    {
        $this->xmf()->redirect($this->url($url), $time, $message, $addredirect, $allowExternalLink);
    }

    /**
     * @param string $name
     * @param null   $language
     *
     * @return bool
     */
    public function loadLanguage($name, $language = null)
    {
        if ($ret = $this->xmf()->loadLanguage($name, $this->_dirname, $language)) {
            $this->addLog("Loading language '{$name}'");
        } else {
            $this->addLog("ERROR :: Language '{$name}' could not be loaded");
        }
        return $ret;
    }

    /**
     * @param null|XoopsObject $obj
     * @param string           $name
     *
     * @return bool|XoopsForm
     */
    /*public function getForm($obj, $name)
    {
        $name = strtolower($name);
        $this->addLog("Loading form '{$name}'");
        return $this->xmf()->getModuleForm($obj, $name, $this->_dirname);
    }*/

    /**
     * Initialize module
     */
    private function _initModule()
    {
        if ($this->isCurrentModule()) {
            $this->_module = $this->xmf()->module();
        } else {
            $this->_module = $this->xmf()->getModuleByDirname($this->_dirname);
        }
    }

    /**
     * @param string $log
     */
    public function addLog($log)
    {
        if ($this->_debug) {
            if (is_object($this->xmf()->logger())) {
                $name = is_object($this->_module) ? $this->_module->getVar('name') : $this->_dirname;
                $microTime = $this->_microTime();
                $taking = sprintf('%0.6f',$microTime - $this->_lastTime);
                $this->_lastTime = $microTime;
                $log .= " from last: {$taking}";
                $this->xmf()->logger()->addExtra($name, $log);
            }
            /**
             * $this->xmf()->preload()->triggerEvent('core.module.addlog', array(
             * $this->getModule()->getVar('name'),
             * $log
             * ));*/
        }
    }

    private function _microtime() {
        $now = explode(' ', microtime());
        $ret = (float) $now[0] + (float) $now[1];
        return $ret;
    }

}