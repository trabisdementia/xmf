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
abstract class Xmf_Module_Controller_Abstract
{
    private $_templatePath = '';
    /**
     * @var bool
     */
    private $_render = true;
    /**
     * @var bool|XoopsTpl
     */
    private $_viewRenderer = false;
    /**
     * @var array
     */
    private $_actions = array();
    private $_action = '';

    public $params = array();

    abstract function init();

    public function __construct()
    {
        $this->_templatePath = XOOPS_ROOT_PATH . '/modules/system/templates/system_dummy.html';
        $this->init();
        if ($this->_render) {
            $this->_viewRenderer = $this->getViewRenderer();
        }
    }

    public function dispatch()
    {
        $actions = $this->getActions();
        foreach ($actions as $action) {
            if (isset($_REQUEST[$action])) {
                return $this->executeAction($action);
            }
        }
        $action = Xmf_Request::getString('action', 'index', $this->getActions());
        return $this->executeAction($action);
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    public function executeAction($action)
    {
        $actions = $this->getActions();
        if (in_array($action, $actions)) {
            $this->_action = $action;
            $action = Xmf_Utility_Inflector::camelize($action) . 'Action';
            if (method_exists($this, $action)) {
                return $this->$action();
            }
        }
        $this->_action = 'index';
        return $this->indexAction();
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function indexAction()
    {
        echo 'NO ACTION DETECTED';
        return true;
    }

    /**
     * @param string $url
     * @param int $time
     * @param string $message
     */
    public function redirect($url, $time, $message)
    {
        redirect_header($url, $time, $message);
    }

    public function forward($action)
    {
        return $this->executeAction($action);
    }

    /**
     * @param string $url
     */
    public function location($url)
    {
        header('location: ' . preg_replace("/[&]amp;/i", '&', $url));
    }

    public function getViewRenderer()
    {
        if (!$this->_viewRenderer) {
            include_once XOOPS_ROOT_PATH . '/class/template.php';
            $this->_viewRenderer = new XoopsTpl();
        }
        return $this->_viewRenderer;
    }

    public function setNoRender()
    {
        $this->_render = false;
    }

    public function disableErrorReporting()
    {
        Xmf::getInstance()->disableErrorReporting();
    }

    public function xmf()
    {
        return Xmf::getInstance();
    }

    public function setTemplatePath($path)
    {
        $this->_templatePath = $path;
    }

    public function getTemplatePath()
    {
        return $this->_templatePath;
    }

    /**
     * @param array $callBacks
     */
    public function display($callBacks = array())
    {
        if ($this->_render) {
            $this->header();
            if ($this->xmf()->isAdminSide()) {
                $this->displayAdminMenu();
            }
            foreach ($callBacks as $callBack) {
                if (method_exists($this, $callBack)) {
                    $this->$callBack();
                }
            }
            $this->_viewRenderer->display($this->_templatePath);
            $this->footer();
        }
    }

    /**
     * @param array $actions
     */
    public function setActions($actions)
    {
        $this->_actions = (array)$actions;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->_actions;
    }

    public function displayAdminMenu()
    {
        $menu = new Xmf_Template_Adminmenu();
        $menu->display();
    }

    public function header()
    {
        $this->xmf()->header();
    }

    public function footer()
    {
        $this->xmf()->footer();
    }
}

