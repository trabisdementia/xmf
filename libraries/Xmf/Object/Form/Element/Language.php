<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformlanguageelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Language extends XoopsFormSelectLang
{
    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        $var = $object->vars[$key];
        $control = $decorator->getControl($key);
        $all = isset($control['all']) ? true : false;

        parent::__construct($var['form_caption'], $key, $object->getVar($key, 'e'));
        if ($all) {
            $this->addOption('all', _ALL);
        }
    }
}