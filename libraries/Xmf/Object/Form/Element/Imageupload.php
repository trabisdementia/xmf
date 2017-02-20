<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformimageuploadelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Imageupload extends Xmf_Object_Form_Element_Upload
{
    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        parent::__construct($object, $key);
        // Override name for upload purposes
        $this->setName('upload_' . $key);
    }

    /**
     * prepare HTML for output
     *
     * @return    string    HTML
     */
    public function render()
    {
        return "<input type='hidden' name='MAX_FILE_SIZE' value='" . $this->getMaxFileSize() . "' />
        <input type='file' name='" . $this->getName() . "' id='" . $this->getName() . "'" . $this->getExtra() . " />
        <input type='hidden' name='smart_upload_image[]' id='smart_upload_image[]' value='" . $this->getName() . "' />";
    }
}