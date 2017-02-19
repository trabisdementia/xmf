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
 * XOOPS
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @package         class
 * @since           2.6.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          formuss
 * @version         $Id: Xoops.php 11012 2013-02-10 00:51:04Z trabis $
 */

defined('XOOPS_ROOT_PATH') or die('Restricted access');

class Xmf extends xos_kernel_Xoops2
{
    /**
     * Holding module configs
     *
     * @var array
     */
    private $_moduleConfigs = array();
    /**
     * Holding XoopsOptions
     *
     * @var array
     */
    private $_options = array();
    /**
     * @var false|XoopsModule
     */
    private $_module = false;
    /**
     * @var string
     */
    private $_moduleDirname = 'system';
    /**
     * @var false|XoopsUser
     */
    private $_user = false;
    /**
     * @var bool
     */
    private $_userIsAdmin = false;

    /**
     * Access the only instance of this class
     *
     * @staticvar Xmf
     * @return Xmf
     */
    static public function &getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    public function __construct()
    {
        parent::__construct();
        $this->_moduleConfigs['system'] =& $GLOBALS['xoopsConfig'];
        $this->_options =& $GLOBALS['xoopsOption'];
        if (isset($GLOBALS['xoopsModule'])) {
            $this->_module =& $GLOBALS['xoopsModule'];
            $this->_moduleDirname = $this->_module->getVar('dirname');
            $this->_moduleConfigs[$this->_moduleDirname] =& $GLOBALS['xoopsModuleConfig'];
        }
        if (isset($GLOBALS['xoopsUser'])) {
            $this->_user =& $GLOBALS['xoopsUser'];
        }
        $this->_userIsAdmin = isset($GLOBALS['xoopsUserIsAdmin']) ? $GLOBALS['xoopsUserIsAdmin'] : false;
    }

    /**
     * Convert a XOOPS path to a physical one
     *
     * @param string $url
     * @param bool   $virtual
     *
     * @return string
     */
    public function path($url, $virtual = false)
    {
        $url = str_replace('\\', '/', $url);
        $url = str_replace(XOOPS_ROOT_PATH, '', $url);
        $url = ltrim($url, '/');
        $parts = explode('/', $url, 2);
        $root = isset($parts[0]) ? $parts[0] : '';
        $path = isset($parts[1]) ? $parts[1] : '';
        if (!isset($this->paths[$root])) {
            list($root, $path) = array('www', $url);
        }
        if (!$virtual) { // Returns a physical path
            $path = $this->paths[$root][0] . '/' . $path;
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            return $path;
        }
        return !isset($this->paths[$root][1]) ? '' : ($this->paths[$root][1] . '/' . $path);
    }

    /**
     * @return bool
     */
    public function isAdminSide()
    {
        return ($this->getOption('pagetype') == 'admin') ? true : false;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getOption($key)
    {
        $ret = '';
        if (isset($this->_options[$key])) {
            $ret = $this->_options[$key];
        }
        return $ret;
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return void
     */
    public function setOption($key, $value = null)
    {
        if (!is_null($value)) {
            $this->_options[$key] = $value;
        }
    }

    public function header()
    {
        global $xoopsConfig, $xoopsOption, $xoopsTpl;
        if ($this->isAdminSide()) {
            xoops_cp_header();
        } else {
            include_once XOOPS_ROOT_PATH . '/header.php';
        }
    }

    public function footer()
    {
        global $xoopsConfig, $xoopsOption, $xoopsTpl;
        if ($this->isAdminSide()) {
            xoops_cp_footer();
        } else {
            include_once XOOPS_ROOT_PATH . '/footer.php';
        }
    }

    /**
     * @return XoopsModule|false
     */
    public function module()
    {
        return $this->_module;
    }

    /**
     * @return XoopsUser|false
     */
    public function user()
    {
        return $this->_user;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->_userIsAdmin;
    }

    /**
     * @return XoopsLogger
     */
    public function logger()
    {
        return XoopsLogger::getInstance();
    }

    /**
     * @return XoopsDatabase
     */
    public function db()
    {
        return XoopsDatabaseFactory::getDatabaseConnection();
    }

    /**
     * @return XoopsPreload
     */
    public function preload()
    {
        return XoopsPreload::getInstance();
    }

    /**
     * @return Xmf_Registry
     */
    public function registry()
    {
        return Xmf_Registry::getInstance();
    }

    /**
     * @param string $dirname
     *
     * @return Xmf_Module_Helper
     */
    public function getModuleHelper($dirname)
    {
        return Xmf_Module_Helper::getInstance($dirname);
    }

    /**
     * @param  string $name
     * @param string  $dirname
     *
     * @return bool|XoopsObjectHandler|XoopsPersistableObjectHandler
     */
    public function getModuleHandler($name, $dirname = '')
    {
        return xoops_getmodulehandler($name, $dirname);
    }

    /**
     * @return string
     */
    public function getModuleDirname()
    {
        return $this->_moduleDirname;
    }

    /**
     * @return XoopsModuleHandler
     */
    public function getHandlerModule()
    {
        return xoops_gethandler('module');
    }

    /**
     * @return XoopsGrouppermHandler
     */
    public function getHandlerGroupperm()
    {
        return xoops_gethandler('groupperm');
    }

    /**
     * @return XoopsMemberHandler
     */
    public function getHandlerMember()
    {
        return xoops_getHandler('member');
    }

    /**
     * @return XoopsUserHandler
     */
    public function getHandlerUser()
    {
        return xoops_getHandler('user');
    }

     /**
     * @return XoopsNotificationHandler
     */
    public function getHandlerNotification()
    {
        return xoops_gethandler('notification');
    }

    /**
     * @return XoopsSecurity
     */
    public function security()
    {
        return $GLOBALS['xoopsSecurity'];
    }

    /**
     * @return array
     */
    public function config()
    {
        return $this->_moduleConfigs['system'];
    }

    /**
     * @return Xmf_Request
     */
    public function request()
    {
        return new Xmf_Request;
    }

    /**
     * @return Xmf_Response_Http
     */
    public function response()
    {
        return Xmf_Response::getInstance();
    }


    /**
     * @return null|XoopsTpl
     */
    public function tpl()
    {
        return $GLOBALS['xoopsTpl'];
    }

    /**
     * @return null|xos_opal_Theme
     */
    public function theme()
    {
        return $GLOBALS['xoTheme'];
    }

    /**
     * @return MyTextSanitizer
     */
    public function myts()
    {
        return MyTextSanitizer::getInstance();
    }

    /**
     * @return bool
     */
    public function isModule()
    {
        return $this->module() instanceof XoopsModule ? true : false;
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        return $this->user() instanceof XoopsUser ? true : false;
    }

    /**
     * XOOPS language loader wrapper
     * Temporary solution, not encouraged to use
     *
     * @param   string $name         Name of language file to be loaded, without extension
     * @param   mixed  $domain       string: Module dirname; global language file will be loaded if $domain is set to 'global' or not specified
     *                               array:  example; array('Frameworks / moduleclasses / moduleadmin')
     * @param   string $language     Language to be loaded, current language content will be loaded if not specified
     *
     * @return  boolean
     */
    public function loadLanguage($name, $domain = '', $language = null)
    {
        return Xmf_Locale::load($name, $domain, $language);
    }

    /**
     * Get active modules from cache file
     *
     * @return array
     */
    public function getActiveModules()
    {
        return xoops_getActiveModules();
    }

    /**
     * Checks is module is installed and active
     *
     * @param $dirname
     *
     * @return bool
     */
    public function isActiveModule($dirname)
    {
        return xoops_isActiveModule($dirname);
    }

    /**
     * @param string $dirname dirname of the module
     *
     * @return bool|XoopsModule
     */
    public function getModuleByDirname($dirname)
    {
        /* @var $module_handler XoopsModuleHandler */
        $module_handler = xoops_getHandler('module');
        return $module_handler->getByDirname($dirname);
    }

    /**
     * @param int $id Id of the module
     *
     * @return bool|XoopsModule
     */
    public function getModuleById($id)
    {
        /* @var $module_handler XoopsModuleHandler */
        $module_handler = xoops_getHandler('module');
        return $module_handler->get($id);
    }

    /**
     * @param bool $closehead
     *
     * @return void
     */
    public function simpleHeader($closehead = true)
    {
        xoops_header($closehead);
    }

    /**
     * @return void
     */
    public function simpleFooter()
    {
        xoops_footer();
    }

    /**
     * @param mixed  $msg
     * @param string $title
     *
     * @return void
     */
    public function error($msg, $title = '')
    {
        xoops_error($msg, $title);
    }

    /**
     * @param mixed  $msg
     * @param string $title
     *
     * @return void
     */
    public function result($msg, $title = '')
    {
        xoops_result($msg, $title);
    }

    /**
     * @param mixed  $hiddens
     * @param mixed  $action
     * @param mixed  $msg
     * @param string $submit
     * @param bool   $addtoken
     *
     * @return void
     */
    public function confirm($hiddens, $action, $msg, $submit = '', $addtoken = true)
    {
        xoops_confirm($hiddens, $action, $msg, $submit, $addtoken);
    }

    /**
     * @param mixed  $time
     * @param string $timeoffset
     *
     * @return int
     */
    public function getUserTimestamp($time, $timeoffset = '')
    {
        return xoops_getUserTimestamp($time, $timeoffset);
    }

    /**
     * Function to calculate server timestamp from user entered time (timestamp)
     *
     * @param int  $timestamp
     * @param null $userTZ
     *
     * @return int
     */
    public function userTimeToServerTime($timestamp, $userTZ = null)
    {
        return userTimeToServerTime($timestamp, $userTZ);
    }

    /**
     * @return string
     */
    public function makePass()
    {
        return xoops_makePass();
    }

    /**
     * @param string $email
     * @param bool   $antispam
     *
     * @return bool|mixed
     */
    public function checkEmail($email, $antispam = false)
    {
        return checkEmail($email, $antispam);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function formatURL($url)
    {
        return formatUrl($url);
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
        redirect_header($url, $time, $message, $addredirect, $allowExternalLink);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getEnv($key)
    {
        $ret = '';
        if (array_key_exists($key, $_SERVER) && isset($_SERVER[$key])) {
            $ret = $_SERVER[$key];
            return $ret;
        }
        if (array_key_exists($key, $_ENV) && isset($_ENV[$key])) {
            $ret = $_ENV[$key];
            return $ret;
        }
        return $ret;
    }

    /**
     * @param $configs array
     * @param $dirname string
     *
     * @return void
     */
    public function addModuleConfigs($configs, $dirname = 'system')
    {
        $dirname = trim(strtolower($dirname));
        if (empty($dirname)) {
            $dirname = $this->isModule() ? $this->module()->getVar('dirname') : 'system';
        }
        $this->_moduleConfigs[$dirname] = array_merge($this->_moduleConfigs[$dirname], (array)$configs);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param string $dirname
     *
     * @return void
     */
    public function setModuleConfig($key, $value = null, $dirname = 'system')
    {
        if (!is_null($value)) {
            $dirname = trim(strtolower($dirname));
            if (empty($dirname)) {
                $dirname = $this->isModule() ? $this->module()->getVar('dirname') : 'system';
            }
            $this->_moduleConfigs[$dirname][$key] =& $value;
        }
    }

    /**
     * @param string $key
     * @param array  $values
     * @param bool   $appendWithKey
     * @param string $dirname
     *
     * @return void
     */
    public function appendModuleConfig($key, array $values, $appendWithKey = false, $dirname = 'system')
    {
        $dirname = trim(strtolower($dirname));
        if (empty($dirname)) {
            $dirname = $this->isModule() ? $this->module()->getVar('dirname') : 'system';
        }
        if ($appendWithKey) {
            foreach ($values as $key2 => $value) {
                $this->_moduleConfigs[$dirname][$key][$key2] =& $value;
            }
        } else {
            $this->_moduleConfigs[$dirname][$key][] =& $values;
        }
    }

    /**
     * @param string $key
     * @param array  $values
     * @param bool   $appendWithKey
     * @param string $dirname
     *
     * @return void
     */
    public function appendConfig($key, array $values, $appendWithKey = false, $dirname = 'system')
    {
        $dirname = trim(strtolower($dirname));
        if (empty($dirname)) {
            $dirname = $this->isModule() ? $this->_module->getVar('dirname') : 'system';
        }
        if ($appendWithKey) {
            foreach ($values as $key2 => $value) {
                $this->_moduleConfigs[$dirname][$key][$key2] =& $value;
            }
        } else {
            $this->_moduleConfigs[$dirname][$key][] =& $values;
        }
    }

    /**
     * @param string $key
     * @param string $dirname
     *
     * @return mixed
     */
    public function getModuleConfig($key, $dirname = '')
    {
        $dirname = trim(strtolower($dirname));
        if (empty($dirname)) {
            $dirname = $this->isModule() ? $this->module()->getVar('dirname') : 'system';
        }

        if (isset($this->_moduleConfigs[$dirname][$key])) {
            return $this->_moduleConfigs[$dirname][$key];
        }

        $this->getModuleConfigs($dirname);

        if (!isset($this->_moduleConfigs[$dirname][$key])) {
            $this->_moduleConfigs[$dirname][$key] = null;
        }
        return $this->_moduleConfigs[$dirname][$key];
    }

    /**
     * @param string $dirname
     *
     * @return array
     */
    public function getModuleConfigs($dirname = '')
    {
        $dirname = trim($dirname);
        if (empty($dirname)) {
            $dirname = $this->isModule() ? $this->module()->getVar('dirname') : 'system';
        }
        if (isset($this->_moduleConfigs[$dirname])) {
            return $this->_moduleConfigs[$dirname];
        }

        $this->_moduleConfigs[$dirname] = array();
        $module = $this->getModuleByDirname($dirname);
        if (is_object($module)) {
            /* @var $config_handler XoopsConfigHandler */
            $config_handler = xoops_gethandler('config');
            $configs = $config_handler->getConfigsByCat(0, $module->getVar('mid'));
            $this->_moduleConfigs[$dirname] =& $configs;
        }

        return $this->_moduleConfigs[$dirname];
    }

    /**
     * Disables page cache by overriding module cache settings
     *
     * @return void
     */
    public function disableModuleCache()
    {
        if ($this->isModule()) {
            $this->appendModuleConfig('module_cache', array($this->module()->getVar('mid') => 0), true);
        }
    }

    /**
     * Support for disabling error reporting
     */
    public function disableErrorReporting()
    {
        error_reporting(0);
        $this->logger()->activated = false;
    }
}