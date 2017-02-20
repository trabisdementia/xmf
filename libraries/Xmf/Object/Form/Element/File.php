<?php

class Xmf_Object_Form_Element_File extends XoopsFormFile
{
    /**
     * @var XoopsObject
     */
    protected $_object;
    /**
     * @var string
     */
    protected $_key;

    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        $this->_object = $object;
        $this->_key = $key;
        parent::__construct($object->vars[$key]['form_caption'], $key, isset($object->vars[$key]['form_maxfilesize']) ? $object->vars[$key]['form_maxfilesize'] : 0);
        $this->setExtra(" size=50");
    }

    /**
     * prepare HTML for output
     *
     * @return    string    HTML
     */
    public function render()
    {
        $ret = '';
        if ($this->_object->getVar($this->_key) != '') {
            Xmf_Locale::load('object', 'xmf');
            $ret .= "<div>" . _OBJ_XMF_CURRENT_FILE . $this->_object->getVar($this->_key) . "</div>";
        }

        $ret .= "<div><input type='hidden' name='MAX_FILE_SIZE' value='" . $this->getMaxFileSize() . "' />
                <input type='file' name='" . $this->getName() . "' id='" . $this->getName() . "'" . $this->getExtra() . " />
                <input type='hidden' name='smart_upload_file[]' id='smart_upload_file[]' value='" . $this->getName() . "' /></div>";
        return $ret;
    }
}