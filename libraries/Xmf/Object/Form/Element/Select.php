<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformselectelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Select extends XoopsFormSelect
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
        $control = $decorator->getControl($key);

        $size = isset($var['size']) ? $var['size'] : ($this->_multiple ? 5 : 1);

        // Adding the options inside this SelectBox
        // If the custom method is not from a module, than it's from the core
        $value = isset($control['value']) ? $control['value'] : $object->getVar($key, 'e');
        parent::__construct($var['form_caption'], $key, $value, $size, $this->_multiple);

        if (isset($control['options'])) {
            $this->addOptionArray($control['options']);
        } else {
            // let's find if the method we need to call comes from an already defined object
            if (isset($control['object'])) {
                if (method_exists($control['object'], $control['method'])) {
                    if ($option_array = $control['object']->$control['method']()) {
                        // Adding the options array to the XoopsFormSelect
                        $this->addOptionArray($option_array);
                    }
                }
            } else {
                // finding the itemHandler; if none, let's take the itemHandler of the $object
                if (isset($control['itemHandler'])) {
                    if (!$control['module']) {
                        // Creating the specified core object handler
                        $control_handler =& xoops_gethandler($control['itemHandler']);
                    } else {
                        $control_handler =& xoops_getmodulehandler($control['itemHandler'], $control['module']);
                    }
                } else {
                    $control_handler =& $decorator->getHandler();
                }

                // Checking if the specified method exists
                if (method_exists($control_handler, $control['method'])) {
                    // TODO : How could I pass the parameters in the following call ...
                    if ($option_array = $control_handler->$control['method']()) {
                        // Adding the options array to the XoopsFormSelect
                        $this->addOptionArray($option_array);
                    }
                }
            }
        }
    }
}