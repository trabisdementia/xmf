<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformimageelement.php 799 2008-02-04 22:14:27Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Image extends XoopsFormElementTray
{
    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        Xmf_Locale::load('object', 'xmf');

        $object = $decorator->getObject();
        $var = $object->vars[$key];
        $object_imageurl = $decorator->getImageDir();
        parent::__construct($var['form_caption'], ' ');

        //todo, check this error
        $objectArray['image'] = str_replace('{XOOPS_URL}', XOOPS_URL, $objectArray['image']);

        if ($object->getVar($key) != '' && (substr($object->getVar($key), 0, 4) == 'http' || substr($object->getVar($key), 0, 11) == '{XOOPS_URL}')) {
            $this->addElement(new XoopsFormLabel('', "<img src='" . str_replace('{XOOPS_URL}', XOOPS_URL, $object->getVar($key)) . "' alt='' /><br/><br/>"));
        } elseif ($object->getVar($key) != '') {
            $this->addElement(new XoopsFormLabel('', "<img src='" . $object_imageurl . $object->getVar($key) . "' alt='' /><br/><br/>"));
        }
        $this->addElement(new Xmf_Object_Form_element_Fileupload($decorator, $key));
        $this->addElement(new XoopsFormLabel('<div style="height: 10px; padding-top: 8px; font-size: 80%;">' . _OBJ_XMF_URL_FILE_DSC . '</div>', ''));
        $this->addElement(new XoopsFormLabel('', '<br />' . _OBJ_XMF_URL_FILE));
        $this->addElement(new Xmf_Object_Form_element_Text($decorator, 'url_' . $key));
        $this->addElement(new XoopsFormLabel('', '<br /><br />'));
        $delete_check = new Xmf_Form_element_Check('', 'delete_' . $key);
        $delete_check->addOption(1, '<span style="color:red;">' . _OBJ_XMF_DELETE . '</span>');
        $this->addElement($delete_check);
    }
}