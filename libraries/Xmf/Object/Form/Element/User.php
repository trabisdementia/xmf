<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformuserelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_User extends XoopsFormSelect
{
    protected $_multiple = false;

    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        $var = $object->vars[$key];
        $size = isset($var['size']) ? $var['size'] : ($this->_multiple ? 5 : 1);

        $this->XoopsFormSelect($var['form_caption'], $key, $object->getVar($key, 'e'), $size, $this->multiple);

        // Adding the options inside this SelectBox
        // If the custom method is not from a module, than it's from the core

        global $xoopsDB;
        $uArray = array();
        $sql = 'SELECT uid, uname FROM ' . $xoopsDB->prefix('users');
        $sql .= ' ORDER BY uname ASC';

        $result = $xoopsDB->query($sql);
        if ($result) {
            while ($myrow = $xoopsDB->fetchArray($result)) {
                $uArray[$myrow['uid']] = $myrow['uname'];
            }
        }
        $this->addOptionArray($uArray);
    }
}