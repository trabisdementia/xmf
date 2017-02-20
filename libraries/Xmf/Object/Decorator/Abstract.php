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
 * @version         $Id$
 */

defined('XMF_EXEC') or die('Xmf was not detected');

abstract class Xmf_Object_Decorator_Abstract
{
    /**
     * @var XoopsObject
     */
    protected $_object;
    /**
     * @var Xmf_Module_Helper_Abstract
     */
    protected $_helper;
    /**
     * @var string
     */
    protected $_handlerName;
    /**
     * @var string
     */
    protected $_dirname;

    /**
     * @param XoopsObject $object
     * @param string      $moduleDirname
     * @param string      $handlerName
     */
    public function __construct(XoopsObject $object, $moduleDirname, $handlerName = '')
    {
        $this->_dirname = $moduleDirname;
        $this->_object = $object;
        $this->_helper = Xmf_Module_Helper::getHelper($moduleDirname);
        $this->_handlerName = $handlerName;
        $this->init();
    }

    /**
     * @return string
     */
    public function getHandlerName()
    {
        return $this->_handlerName;
    }

    /**
     * @return \Xmf_Module_Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return \XoopsObject
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * @return bool|XoopsObjectHandler|XoopsPersistableObjectHandler
     */
    public function getHandler()
    {
        return $this->_helper->getHandler($this->_handlerName);
    }

    /**
     * @return Xmf
     */
    public function xmf()
    {
        return Xmf::getInstance();
    }

    abstract public function init();
}
