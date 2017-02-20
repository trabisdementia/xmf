<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: $
 */

defined('XMF_EXEC') or die('Xmf was not detected');

class Xmf_Object_Decorator_QuickForm extends Xmf_Object_Decorator_Abstract
{
    protected $_dirname;
    protected $_formClass = 'XoopsThemeForm';
    protected $_formTitle = 'Some Title';
    protected $_formName = 'form';
    protected $_formAction = 'index.php';
    protected $_formMethod = 'post';
    protected $_formToken = false;
    /* @var XoopsForm|XoopsThemeForm */
    protected $_form;
    protected $_requiredFields = array();
    /* @var string Current key */
    protected $_key;

    public function init()
    {
        Xmf_Locale::load('common', $this->_dirname);
        xoops_load('xoopsformloader');
    }

    public function setForm(XoopsForm $form)
    {
        $this->_form = $form;
        return $this->_form;
    }

    public function form()
    {
        if (!$this->_form) {
            $this->_form = new $this->_formClass($this->_formTitle, $this->_formName, $this->_formAction, $this->_formMethod, $this->_formToken);
        }
        return $this->_form;
    }

    public function setRequiredFields($array)
    {
        $this->_requiredFields = $array;
    }

    public function render()
    {
        return $this->form()->render();
    }

    public function display()
    {
        $this->form()->display();
    }

    public function addButton($key, $type = 'button')
    {
        $this->_key = $key;
        $this->_add(new XoopsFormButton('', $key, $this->_getValue(), $type));
    }

    public function addCheckBox($key, $options = array(), $delimeter = '&nbsp;')
    {
        $this->_key = $key;
        $element = new XoopsFormCheckBox('', $key, $this->_getValue(), $delimeter);
        $element->addOptionArray($options);
        $this->_add($element);
    }

    public function addColorPicker($key)
    {
        $this->_key = $key;
        $this->_add(new XoopsFormColorPicker('', $key, $this->_getValue()));
    }

    public function addDateTime($key, $showtime = true)
    {
        $this->_key = $key;
        $this->_add(new XoopsFormDateTime('', $key, 15, $this->_getValue(), $showtime));
    }

    public function addDhtmlTextArea($key, $options = array())
    {
        $this->_key = $key;
        $this->_add(new XoopsFormDhtmlTextArea('', $key, $this->_getValue(), 5, 60, "{$key}HiddenText", $options));
    }

    public function addEditor($key, $editor, $noHtml = false, $onFailure = '')
    {
        $this->_key = $key;
        $configs = array(
                'editor'     => $editor,
                'caption'    => $this->_getTitle(),
                'name'       => $key,
                'value'      => $this->_getValue(),
                'rows'       => 5,
                'cols'       => 6,
                'hiddentext' => "{$key}HiddenText"
        );
        $this->_add(new XoopsFormEditor('', $key, $configs, $noHtml, $onFailure));
    }

    public function addFile($key, $maxfilesize)
    {
        $this->_key = $key;
        $this->_add(new XoopsFormFile('', $key, $maxfilesize));
    }

    public function addHidden($key)
    {
        $this->_key = $key;
        $this->_add(new XoopsFormHidden($key, $this->_getValue()));
    }

    public function addLabel($key)
    {
        $this->_key = $key;
        $this->_add(new XoopsFormLabel('', $key, $this->_getValue()));
    }

    public function addRadio($key, $options = array(), $delimiter = '&nbsp;')
    {
        $this->_key = $key;
        $element = new XoopsFormRadio('', $key, $this->_getValue(), $delimiter);
        $element->addOptionArray($options);
        $this->_add($element);
    }

    public function addRadioYN($key, $options = array(1 => _YES, 0 => _NO))
    {
        $this->_key = $key;
        $element = new XoopsFormRadio('', $key, $this->_getValue());
        $element->addOptionArray($options);
        $this->_add($element);
    }

    public function addSelect($key, $options = array(), $size = 1, $multiple = false)
    {
        $this->_key = $key;
        $element = new XoopsFormSelect('', $key, $this->_getValue(), $size, $multiple);
        $element->addOptionArray($options);
        $this->_add($element);
    }

    public function addCheckGroup($key)
    {
        $this->_key = $key;
        $element = new XoopsFormSelectCheckGroup('', $key, $this->_getValue());
        $this->_add($element);
    }

    public function addSelectCountry($key, $size)
    {
        $this->_key = $key;
        $element = new XoopsFormSelectCountry('', $key, $this->_getValue(), $size);
        $this->_add($element);
    }

    public function addSelectGroup($key, $include_anon = false, $size = 1, $multiple = false)
    {
        $this->_key = $key;
        $element = new XoopsFormSelectGroup('', $key, $include_anon, $this->_getValue(), $size, $multiple);
        $this->_add($element);
    }

    public function addSelectLang($key, $size = 1)
    {
        $this->_key = $key;
        $element = new XoopsFormSelectLang('', $key, $this->_getValue(), $size);
        $this->_add($element);
    }

    public function addSelectUser($key, $include_anon = false, $size = 1, $multiple = false)
    {
        $this->_key = $key;
        $element = new XoopsFormSelectUser('', $key, $include_anon, $this->_getValue(), $size, $multiple);
        $this->_add($element);
    }

    public function addText($key)
    {
        $this->_key = $key;
        $maxLength = $this->_object->vars[$this->_key]['maxlength']? $this->_object->vars[$this->_key]['maxlength'] :255;
        $size = $maxLength < 50 ? $maxLength : 50;
        $this->_add(new XoopsFormText('', $key, $size, $maxLength, $this->_getValue()));
    }

    public function addTextArea($key)
    {
        $this->_key = $key;
        $this->_add(new XoopsFormTextArea('', $key, $this->_getValue(), 5, 50));
    }

    public function addTextDateSelect($key)
    {
        $this->_key = $key;
        $this->_add(new XoopsFormTextDateSelect($this->_getTitle(), $key, 15, $this->_getValue()));
    }

    public function addSelectTree($key, $objects, $prefix = '--', $addEmptyOption = true, $startID = 0, $extra = '')
    {
        $this->_key = $key;
        $handler = $this->_helper->getHandler($this->_handlerName);
        $mytree = new Xmf_Object_Tree($objects, $handler->keyName, $key);
        $cat_select = $mytree->makeSelBox($key, $handler->identifierName, $prefix, $this->_object->getVar($key), $addEmptyOption, $startID, $extra);
        $this->_add(new XoopsFormLabel($this->_getTitle(), $cat_select));
    }

    protected function _getTitle()
    {
        //For module 'news', handler 'topics', key 'topic_id'
        //_CO_NEWS_OBJ_ID, if we have standard handler to object
        $value = Xmf_Locale::translate("_CO_{$this->_dirname}_OBJ_{$this->getUnprefixedKey()}", Xmf_Utility_Inflector::humanize($this->_key));
        //Or _CO_NEWS_TOPICS_TOPIC_ID
        return Xmf_Locale::translate("_CO_{$this->_dirname}_{$this->_handlerName}_{$this->_key}", $value);
    }

    protected function _getDescription()
    {
        //For module 'news', handler 'topics', key 'topic_id'
        //_CO_NEWS_OBJ_ID_DSC, if we have standard handler to object
        $value = Xmf_Locale::translate("_CO_{$this->_dirname}_OBJ_{$this->getUnprefixedKey()}_DSC", '');
        //Or _CO_NEWS_TOPICS_TOPIC_ID_DSC
        return Xmf_Locale::translate("_CO_{$this->_dirname}_{$this->_handlerName}_{$this->_key}_DSC", $value);
    }

    protected function getUnprefixedKey()
    {
        // handler 'topics' becomes 'topic_'
        $keyPrefix = substr($this->_handlerName, 0, -1) . '_';
        // Remove prefix from key if it exists
        return str_replace($keyPrefix, '', $this->_key);
    }

    protected function _getValue()
    {
        return $this->_object->getVar($this->_key, 'e');
    }

    protected function _isRequired()
    {
        return ($this->_object->vars[$this->_key]['required'] || in_array($this->_key, $this->_requiredFields));
    }

    protected function _add(XoopsFormElement $element)
    {
        $element->setCaption($this->_getTitle());
        if ($description = $this->_getDescription()) {
            $element->setDescription($description);
        }

        $this->_form->addElement($element, $this->_isRequired());
    }
}