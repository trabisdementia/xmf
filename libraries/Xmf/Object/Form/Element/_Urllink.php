<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformurllinkelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Urllink extends XoopsFormElementTray
{
    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     * @param string                    $form_caption
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key, $form_caption)
    {
        Xmf_Locale::load('object', 'xmf');
        $object = $decorator->getObject();
        parent::__construct($form_caption, '&nbsp;');

        $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_URLLINK_URL));
        $this->addElement(new Xmf_Object_Form_Element_Text($decorator, 'url_' . $key));

        $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_CAPTION));
        $this->addElement(new SmartFormTextElement($decorator, 'caption_' . $key));

        $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_DESC . '<br/>'));
        $this->addElement(new XoopsFormTextArea('', 'desc_' . $key, $object->getVar('description')));

        $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_URLLINK_TARGET));
        $targ_val = $object->getVar('target');
        $targetRadio = new XoopsFormRadio('', 'target_' . $key, $targ_val != '' ? $targ_val : '_blank');
        $control = $decorator->getControl('target');
        $targetRadio->addOptionArray($control['options']);

        $this->addElement($targetRadio);
    }
}