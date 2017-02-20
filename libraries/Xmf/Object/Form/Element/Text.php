<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformtextelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Text extends XoopsFormText
{
    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        $control = $decorator->getControl($key);
        $var = $object->vars[$key];
        $form_maxlength = isset($control['maxlength']) ? $control['maxlength'] : (isset($var['maxlength']) ? $var['maxlength'] : 255);
        $form_size = isset($control['size']) ? $control['size'] : 50;
        parent::__construct($var['form_caption'], $key, $form_size, $form_maxlength, $object->getVar($key, 'e'));
    }
}