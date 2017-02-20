<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformtimeelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Time extends XoopsFormSelect
{
    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        $var = $object->vars[$key];
        $timearray = array();
        for ($i = 0; $i < 24; $i++) {
            for ($j = 0; $j < 60; $j = $j + 10) {
                $key_t = ($i * 3600) + ($j * 60);
                $timearray[$key_t] = ($j != 0) ? $i . ':' . $j : $i . ':0' . $j;
            }
        }
        ksort($timearray);
        $this->XoopsFormSelect($var['form_caption'], $key, $object->getVar($key, 'e'));
        $this->addOptionArray($timearray);
    }
}