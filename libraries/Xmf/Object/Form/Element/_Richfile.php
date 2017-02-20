<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformrichfileelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Richfile extends XoopsFormElementTray
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
        if ($object->getVar('url') != '') {
            $caption = $object->getVar('caption') != '' ? $object->getVar('caption') : $object->getVar('url');
            $this->addElement(new XoopsFormLabel('', _OBJ_XMF_CURRENT_FILE . "<a href='" . str_replace('{XOOPS_URL}', XOOPS_URL, $object->getVar('url')) . "' target='_blank' >" . $caption . "</a><br/><br/>"));
            //$this->addElement( new XoopsFormLabel( '', "<br/><a href = '".SMARTOBJECT_URL."admin/file.php?op=del&fileid=".$object->id()."'>"._OBJ_XMF_DELETE_FILE."</a>"));
        }

        if ($object->isNew()) {
            $this->addElement(new Xmf_Object_Form_Element_Fileupload($decorator, $key));
            $this->addElement(new XoopsFormLabel('', '<br/><br/><small>' . _OBJ_XMF_URL_FILE_DSC . '</small>'));
            $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_URL_FILE));
            $this->addElement(new Xmf_Object_Form_Element_Text($decorator, 'url_' . $key));
        }
        $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_CAPTION));
        $this->addElement(new Xmf_Object_Form_Element_Text($decorator, 'caption_' . $key));
        $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_DESC . '<br/>'));
        $this->addElement(new XoopsFormTextArea('', 'desc_' . $key, $object->getVar('description')));

        if (!$object->isNew()) {
            $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_CHANGE_FILE));
            $this->addElement(new Xmf_Object_Form_Element_Fileupload($decorator, $key));
            $this->addElement(new XoopsFormLabel('', '<br/><br/><small>' . _OBJ_XMF_URL_FILE_DSC . '</small>'));
            $this->addElement(new XoopsFormLabel('', '<br/>' . _OBJ_XMF_URL_FILE));
            $this->addElement(new Xmf_Object_Form_Element_Text($decorator, 'url_' . $key));
        }
    }
}