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
 * @package         Xmf
 * @since           0.1
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id$
 */

defined('XMF_EXEC') or die('Xmf was not detected');

include_once XOOPS_ROOT_PATH . '/class/template.php';

abstract class Xmf_Object_Template_Abstract extends Xmf_Template_Abstract
{
    /**
     * @var Xmf_Object_Decorator_Form
     */
    protected $_decorator;

    /**
     * @var XoopsObject
     */
    protected $_object;

    /**
     * Constructor
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator)
    {
        $this->_decorator = $decorator;
        $this->_object = $decorator->getObject();
        parent::__construct();
    }

}