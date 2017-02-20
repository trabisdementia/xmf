<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformfileuploadelement.php 2345 2008-05-21 17:49:10Z fx2024 $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Fileupload extends Xmf_Form_Element_Upload
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
        <input type='file' name='upload_" . $this->getName() . "' id='upload_" . $this->getName() . "'" . $this->getExtra() . " />
        <input type='hidden' name='smart_upload_file[]' id='smart_upload_file[]' value='" . $this->getName() . "' />";
    }
}
