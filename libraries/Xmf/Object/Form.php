<?php

/**
 * Contains the class responsible for providing forms related to a SmartObject
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartobjectform.php 2085 2008-05-09 12:57:53Z fx2024 $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */

if (!defined("XOOPS_ROOT_PATH")) {
    die("XOOPS root path not defined");
}

/**
 * Including the XoopsFormLoader classes
 */
include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
Xmf_Locale::load('object', 'xmf');

/**
 * SmartForm base class
 * Base class representing a single form for a specific SmartObject
 *
 * @package SmartObject
 * @author  marcan <marcan@smartfactory.ca>
 * @link    http://smartfactory.ca The SmartFactory
 */
class Xmf_Object_Form extends XoopsThemeForm
{
    /**
     * @var null|XoopsObject
     */
    public $targetObject = null;
    /**
     * @var Xmf_Object_Decorator_Form
     */
    public $decorator;
    /**
     * @var null|array
     */
    public $form_fields = null;
    /**
     * @var bool
     */
    protected $_cancel_js_action = false;
    /**
     * @var bool|array
     */
    protected $_custom_button = false;
    /**
     * @var bool
     */
    protected $_captcha = false;
    /**
     * @var bool
     */
    protected $_form_name = false;
    /**
     * @var bool
     */
    protected $_form_caption = false;
    /**
     * @var bool
     */
    protected $_submit_button_caption = false;

    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $form_name
     * @param string                    $form_caption
     * @param string                    $form_action
     * @param null                      $form_fields
     * @param bool                      $submit_button_caption
     * @param bool                      $cancel_js_action
     * @param bool                      $captcha
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $form_name = '', $form_caption = '', $form_action = '', $form_fields = null, $submit_button_caption = false, $cancel_js_action = false, $captcha = false)
    {

        $this->targetObject =& $decorator->getObject();
        $this->decorator = $decorator;
        $this->form_fields = $form_fields;
        $this->_cancel_js_action = $cancel_js_action;
        $this->_captcha = $captcha;
        $this->_form_name = $form_name;
        $this->_form_caption = $form_caption;
        $this->_submit_button_caption = $submit_button_caption;

        if (!isset($form_action)) {
            $form_action = xoops_getenv('PHP_SELF');
        }

        parent::__construct($form_caption, $form_name, $form_action);
        $this->setExtra('enctype="multipart/form-data"');

        $this->createElements();

        if ($captcha) {
            $this->addCaptcha();
        }

        $this->createPermissionControls();

        $this->createButtons($form_name, $submit_button_caption);
    }

    public function addCaptcha()
    {
        XoopsLoad::load('xoopsformcaptcha');
        $this->addElement(new XoopsFormCaptcha(), true);
    }

    /**
     * @param string $name
     * @param string $caption
     * @param bool   $onclick
     */
    public function addCustomButton($name, $caption, $onclick = false)
    {
        $custom_button_array = array(
                'name'    => $name,
                'caption' => $caption,
                'onclick' => $onclick
        );
        $this->_custom_button[] = $custom_button_array;
    }

    /**
     * Add an element to the form
     *
     * @param XoopsFormElement $formElement
     * @param bool|string      $key
     * @param bool|string      $var
     * @param string           $required is this a "required" element?
     */
    public function addElement(XoopsFormElement $formElement, $key = false, $var = false, $required = 'notset')
    {
        if ($key) {
            $control = $this->decorator->getControl($key);
            if ($this->targetObject->vars[$key]['readonly']) {
                $formElement->setExtra('disabled="disabled"');
                $formElement->setName($key . '-readonly');
                // Since this element is disable, we still want to pass it's value in the form
                $hidden = new XoopsFormHidden($key, $this->targetObject->vars[$key]['value']);
                $this->addElement($hidden);
            }
            $formElement->setDescription($var['form_dsc']);
            if (isset($control[$key]['onSelect'])) {
                $hidden = new XoopsFormHidden('changedField', false);
                $this->addElement($hidden);
                $otherExtra = isset($var['form_extra']) ? $var['form_extra'] : '';
                $onchangedString = "this.form.elements.changedField.value='$key'; this.form.elements.op.value='changedField'; submit()";
                $formElement->setExtra('onchange="' . $onchangedString . '"' . ' ' . $otherExtra);
            } else {
                if (isset($var['form_extra'])) {
                    $formElement->setExtra($var['form_extra']);
                }
            }
            if (isset($control[$key]['js'])) {
                $formElement->customValidationCode[] = $control[$key]['js'];
            }
            parent::addElement($formElement, $required == 'notset' ? $var['required'] : $required);
        } else {
            parent::addElement($formElement, $required == 'notset' ? false : true);
        }
        unset($formElement);
    }

    public function createElements()
    {
        $vars = $this->targetObject->vars;
        foreach ($vars as $key => $var) {

            // If $displayOnForm is false OR this is the primary key, it doesn't
            // need to be displayed, then we only create an hidden field
            if (!$var['displayOnForm']) {
                $elementToAdd = new XoopsFormHidden($key, $var['value']);
                $this->addElement($elementToAdd, $key, $var, false);
                unset($elementToAdd);
                // If not, the we need to create the proper form control for this fields
            } else {
                // If this field has a specific control, we will use it

                if ($key == 'parentid') {
                    /**
                     * Why this ?
                     */
                }
                if ($control = $this->decorator->getControl($key)) {
                    /* If the control has name, it's because it's an object already present in the script
                    * for example, "user"
                    * If the field does not have a name, than we will use a "select" (ie XoopsFormSelect)
                    */
                    if (!isset($control['name']) || !$control['name']) {
                        $control['name'] = 'select';
                    }

                    $form_select = $this->getControl($control['name'], $key);

                    // Adding on the form, the control for this field
                    $this->addElement($form_select, $key, $var);
                    unset($form_select);
                    // If this field don't have a specific control, we will use the standard one, depending on its data type
                } else {
                    switch ($var['data_type']) {

                        case XOBJ_DTYPE_TXTBOX:

                            $form_text = $this->getControl("text", $key);
                            $this->addElement($form_text, $key, $var);
                            unset($form_text);
                            break;

                        case XOBJ_DTYPE_INT:
                            $this->decorator->setControl($key, array(
                                    'name' => 'text',
                                    'size' => '5'
                            ));
                            $form_text = $this->getControl("text", $key);
                            $this->addElement($form_text, $key, $var);
                            unset($form_text);
                            break;

                        case XOBJ_DTYPE_FLOAT:
                            $this->decorator->setControl($key, array(
                                    'name' => 'text',
                                    'size' => '5'
                            ));
                            $form_text = $this->getControl("text", $key);
                            $this->addElement($form_text, $key, $var);
                            unset($form_text);
                            break;

                        case XOBJ_DTYPE_LTIME:
                            $form_date_time = $this->getControl('date_time', $key);
                            $this->addElement($form_date_time, $key, $var);
                            unset($form_date_time);
                            break;

                        case XOBJ_DTYPE_STIME:
                            $form_date_time = $this->getControl('date', $key);
                            $this->addElement($form_date_time, $key, $var);
                            unset($form_date_time);
                            break;
/*
                        case XOBJ_DTYPE_TIME_ONLY:
                            $form_time = $this->getControl('time', $key);
                            $this->addElement($form_time, $key, $var);
                            unset($form_time);
                            break;

                        case XOBJ_DTYPE_CURRENCY:
                            $this->decorator->setControl($key, array(
                                    'name' => 'text',
                                    'size' => '15'
                            ));
                            $form_currency = $this->getControl("text", $key);
                            $this->addElement($form_currency, $key, $var);
                            unset($form_currency);
                            break;

                        case XOBJ_DTYPE_URLLINK:
                            $form_urllink = $this->getControl("urllink", $key);
                            $this->addElement($form_urllink, $key, $var);
                            unset($form_urllink);
                            break;

                        case XOBJ_DTYPE_FILE:
                            $form_file = $this->getControl("richfile", $key);
                            $this->addElement($form_file, $key, $var);
                            unset($form_file);
                            break;
*/
                        case XOBJ_DTYPE_TXTAREA:
                            $form_text_area = $this->getTextArea($key, $var);
                            $this->addElement($form_text_area, $key, $var);
                            unset($form_text_area);
                            break;

                        case XOBJ_DTYPE_ARRAY:
                            // TODO : To come...
                            break;
                        case XOBJ_DTYPE_SOURCE:
                            // TODO : To come...
                            break;
                        case XOBJ_DTYPE_FORM_SECTION:
                            $section_control = new Xmf_Form_Element_Section($key, $var['value']);
                            $this->addElement($section_control, $key, $var);
                            unset($section_control);
                            break;
                        case XOBJ_DTYPE_FORM_SECTION_CLOSE:
                            $section_control = new Xmf_Form_Element_SectionClose($key, $var['value']);
                            $this->addElement($section_control, $key, $var);
                            unset($section_control);
                            break;
                    }
                }
            }
        }
        // Add an hidden field to store the URL of the page before this form
        $this->addElement(new XoopsFormHidden('xmf_page_before_form', Xmf_Utilities::getPageBeforeForm()));
    }

    //todo, implement this
    public function createPermissionControls()
    {
        /*
        $smartModuleConfig = $this->targetObject->handler->getModuleConfig();

        $permissions = $this->targetObject->handler->getPermissions();

        if ($permissions) {
            $member_handler = & xoops_gethandler('member');
            $group_list = $member_handler->getGroupList();
            asort($group_list);
            foreach ($permissions as $permission) {
                if ($this->targetObject->isNew()) {
                    if (isset($smartModuleConfig['def_perm_' . $permission['perm_name']])) {
                        $groups_value = $smartModuleConfig['def_perm_' . $permission['perm_name']];
                    }
                } else {
                    $groups_value = $this->targetObject->getGroupPerm($permission['perm_name']);
                }
                $groups_select = new XoopsFormSelect($permission['caption'], $permission['perm_name'], $groups_value, 4, true);
                $groups_select->setDescription($permission['description']);
                $groups_select->addOptionArray($group_list);
                $this->addElement($groups_select);
                unset($groups_select);
            }
        }*/
    }

    /**
     * @param string $form_name
     * @param bool   $submit_button_caption
     */
    public function createButtons($form_name, $submit_button_caption = false)
    {
        $button_tray = new XoopsFormElementTray('', '');
        $button_tray->addElement(new XoopsFormHidden('op', $form_name));
        if (!$submit_button_caption) {
            if ($this->targetObject->isNew()) {
                $butt_create = new XoopsFormButton('', 'create_button', _OBJ_XMF_CREATE, 'submit');
            } else {
                $butt_create = new XoopsFormButton('', 'modify_button', _OBJ_XMF_MODIFY, 'submit');
            }
        } else {
            $butt_create = new XoopsFormButton('', 'modify_button', $submit_button_caption, 'submit');
        }
        $butt_create->setExtra('onclick="this.form.elements.op.value=\'' . $form_name . '\'"');
        $button_tray->addElement($butt_create);

        //creating custom buttons
        if ($this->_custom_button) {
            foreach ($this->_custom_button as $custom_button) {
                $butt_custom = new XoopsFormButton('', $custom_button['name'], $custom_button['caption'], 'submit');
                if ($custom_button['onclick']) {
                    $butt_custom->setExtra('onclick="' . $custom_button['onclick'] . '"');
                }
                $button_tray->addElement($butt_custom);
                unset($butt_custom);
            }
        }

        // creating the "cancel" button
        $butt_cancel = new XoopsFormButton('', 'cancel_button', _OBJ_XMF_CANCEL, 'button');
        if ($this->_cancel_js_action) {
            $butt_cancel->setExtra('onclick="' . $this->_cancel_js_action . '"');
        } else {
            $butt_cancel->setExtra('onclick="history.go(-1)"');
        }
        $button_tray->addElement($butt_cancel);

        $this->addElement($button_tray);
    }

    /**
     * @param string $controlName
     * @param string $key
     *
     * @return XoopsFormElement
     */
    public function getControl($controlName, $key)
    {
        $control = $this->decorator->getControl($key);
        switch ($controlName) {
            case 'check':
                $controlObj = new Xmf_Form_Element_Check($this->targetObject->vars[$key]['form_caption'], $key, $this->targetObject->getVar($key));
                $controlObj->addOptionArray($control['options']);
                return $controlObj;
                break;

            case 'color':
                $controlObj = new XoopsFormColorPicker($this->targetObject->vars[$key]['form_caption'], $key, $this->targetObject->getVar($key));
                return $controlObj;
                break;

            case 'radio':
                $controlObj = new XoopsFormRadio($this->targetObject->vars[$key]['form_caption'], $key, $this->targetObject->getVar($key));
                $controlObj->addOptionArray($control['options']);
                return $controlObj;
                break;

            case 'label':
                return new XoopsFormLabel($this->targetObject->vars[$key]['form_caption'], $this->targetObject->getVar($key));
                break;

            case 'textarea' :
                return $this->getTextArea($key);

            case 'theme':
                return $this->getThemeSelect($key, $this->targetObject->vars[$key]);

            case 'theme_multi':
                return $this->getThemeSelect($key, $this->targetObject->vars[$key], true);
                break;

            case 'timezone':
                return new XoopsFormSelectTimezone($this->targetObject->vars[$key]['form_caption'], $key, $this->targetObject->getVar($key));
                break;

            case 'group':
                return new XoopsFormSelectGroup($this->targetObject->vars[$key]['form_caption'], $key, false, $this->targetObject->getVar($key, 'e'), 1, false);
                break;

            case 'group_multi':
                return new XoopsFormSelectGroup($this->targetObject->vars[$key]['form_caption'], $key, false, $this->targetObject->getVar($key, 'e'), 5, true);
                break;

            /*case 'user':
                return new XoopsFormSelectUser($this->targetObject->vars[$key]['form_caption'], $key, false, $this->targetObject->getVar($key, 'e'), 1, false);
                break;*/

            case 'user_multi':
                return new XoopsFormSelectUser($this->targetObject->vars[$key]['form_caption'], $key, false, $this->targetObject->getVar($key, 'e'), 5, true);
                break;

            case 'password':
                return new XoopsFormPassword($this->targetObject->vars[$key]['form_caption'], $key, 50, 255, $this->targetObject->getVar($key, 'e'));
                break;

            case 'country':
                return new XoopsFormSelectCountry($this->targetObject->vars[$key]['form_caption'], $key, $this->targetObject->getVar($key, 'e'));
                break;
            /*
                        case 'urllink':
                            include_once(SMARTOBJECT_ROOT_PATH . "class/form/elements/smartformurllinkelement.php");
                            return new SmartFormUrlLinkElement($this->targetObject->vars[$key]['form_caption'], $key, $this->targetObject->getUrlLinkObj($key));
                            break;

                        case 'richfile':
                            include_once(SMARTOBJECT_ROOT_PATH . "class/form/elements/smartformrichfileelement.php");
                            return new SmartFormRichFileElement($this->targetObject->vars[$key]['form_caption'], $key, $this->targetObject->getFileObj($key));
                            break;
            */
            case 'section':
                return new Xmf_Form_Element_Section($key, $this->targetObject->vars[$key]['form_caption']);
                break;

            default:
                $classname = "Xmf_Object_Form_Element_" . ucfirst($controlName);
                if (!class_exists($classname)) {

                    // perhaps this is a control created by the module
                    $moduleName = $this->decorator->getDirname();
                    $moduleFormElementsPath = XOOPS_ROOT_PATH . "modules/{$moduleName}/class/form/elements/";
                    $classFileName = strtolower($classname) . ".php";
                    $classname = ucfirst($moduleName) . ucfirst($controlName) . "FormElement";

                    if (file_exists($moduleFormElementsPath . $classFileName)) {
                        include_once($moduleFormElementsPath . $classFileName);
                    } else {
                        trigger_error($classname . " Not found", E_USER_WARNING);
                        return new XoopsFormLabel(); //Empty object
                    }
                }
                return new $classname($this->decorator, $key);
                break;
        }
    }

    public function getTextArea($key)
    {
        $var = $this->targetObject->vars[$key];
        $controls = $this->decorator->getControl($key);
        // if no control has been created, let's create a default one
        if (!isset($controls[$key])) {
            $control = array(
                    'name'        => 'textarea',
                    'itemHandler' => false,
                    'method'      => false,
                    'module'      => false,
                    'form_editor' => 'default'
            );
        } else {
            $control = $this->decorator->getControl($key);
        }
        $xoops22 = false; //smart_isXoops22();

        $form_editor = isset($control['form_editor']) ? $control['form_editor'] : 'textarea';
        /**
         * If the editor is 'default', retreive the default editor of this module
         */
        if ($form_editor == 'default') {
            global $xoopsModuleConfig;
            $form_editor = isset($xoopsModuleConfig['default_editor']) ? $xoopsModuleConfig['default_editor'] : 'textarea';
        }

        $caption = $var['form_caption'];
        $name = $key;

        $value = $this->targetObject->getVar($key);

        // $value = $this->targetObject->getValueFor($key, true);

        $editor_configs = array();
        $editor_configs["name"] = $name;
        $editor_configs["value"] = $value;
        if ($form_editor != 'textarea') {
            $editor_configs["rows"] = 35;
            $editor_configs["cols"] = 60;
        }

        if (isset($control['rows'])) {
            $editor_configs["rows"] = $control['rows'];
        }
        if (isset($control['cols'])) {
            $editor_configs["cols"] = $control['cols'];
        }

        $editor_configs["width"] = "100%";
        $editor_configs["height"] = "400px";

        $dhtml = true;
        $xoopseditorclass = XOOPS_ROOT_PATH . '/class/xoopsform/formeditor.php';

        if (file_exists($xoopseditorclass)) {
            include_once($xoopseditorclass);
            $editor = new XoopsFormEditor($caption, $form_editor, $editor_configs, $nohtml = false, $onfailure = "textarea");
        } else {

            switch ($form_editor) {

                case 'tiny' :
                    if (!$xoops22) {
                        if (is_readable(XOOPS_ROOT_PATH . "/class/xoopseditor/tinyeditor/formtinytextarea.php")) {
                            include_once(XOOPS_ROOT_PATH . "/class/xoopseditor/tinyeditor/formtinytextarea.php");
                            $editor = new XoopsFormTinyTextArea(array(
                                    'caption' => $caption,
                                    'name'    => $name,
                                    'value'   => $value,
                                    'width'   => '100%',
                                    'height'  => '300px'
                            ), true);
                        } else {
                            if ($dhtml) {
                                $editor = new XoopsFormDhtmlTextArea($caption, $name, $value, 20, 60);
                            } else {
                                $editor = new XoopsFormTextArea($caption, $name, $value, 7, 60);
                            }
                        }
                    } else {
                        $editor = new XoopsFormEditor($caption, "tinyeditor", $editor_configs);
                    }
                    break;

                case 'dhtmltextarea' :
                case 'dhtmltext' :
                    $editor = new XoopsFormDhtmlTextArea($var['form_caption'], $key, $this->targetObject->getVar($key, 'e'), 20, 60);
                    if ($var['form_dsc']) {
                        $editor->setDescription($var['form_dsc']);
                    }
                    break;

                case 'fckeditor' :
                    if (!$xoops22) {
                        if (is_readable(XOOPS_ROOT_PATH . "/class/xoopseditor/fckeditor/formfckeditor.php")) {
                            include_once(XOOPS_ROOT_PATH . "/class/xoopseditor/fckeditor/formfckeditor.php");
                            $editor = new XoopsFormFckeditor(array(
                                    'caption' => $caption,
                                    'name'    => $name,
                                    'value'   => $value,
                                    'width'   => '100%',
                                    'height'  => '300px'
                            ), true);
                        } else {
                            if ($dhtml) {
                                $editor = new XoopsFormDhtmlTextArea($caption, $name, $value, 20, 60);
                            } else {
                                $editor = new XoopsFormTextArea($caption, $name, $value, 7, 60);
                            }
                        }
                    } else {
                        $editor = new XoopsFormEditor($caption, "fckeditor", $editor_configs);
                    }
                    break;

                case 'inbetween' :
                    if (!$xoops22) {
                        if (is_readable(XOOPS_ROOT_PATH . "/class/xoopseditor/inbetween/forminbetweentextarea.php")) {
                            include_once(XOOPS_ROOT_PATH . "/class/xoopseditor/inbetween/forminbetweentextarea.php");
                            $editor = new XoopsFormInbetweenTextArea(array(
                                    'caption' => $caption,
                                    'name'    => $name,
                                    'value'   => $value,
                                    'width'   => '100%',
                                    'height'  => '300px'
                            ), true);
                        } else {
                            if ($dhtml) {
                                $editor = new XoopsFormDhtmlTextArea($caption, $name, $value, 20, 60);
                            } else {
                                $editor = new XoopsFormTextArea($caption, $name, $value, 7, 60);
                            }
                        }
                    } else {
                        $editor = new XoopsFormEditor($caption, "inbetween", $editor_configs);
                    }
                    break;

                case 'koivi' :
                    if (!$xoops22) {
                        if (is_readable(XOOPS_ROOT_PATH . "/class/wysiwyg/formwysiwygtextarea.php")) {
                            include_once(XOOPS_ROOT_PATH . "/class/wysiwyg/formwysiwygtextarea.php");
                            $editor = new XoopsFormWysiwygTextArea($caption, $name, $value, '100%', '400px');
                        } else {
                            if ($dhtml) {
                                $editor = new XoopsFormDhtmlTextArea($caption, $name, $value, 20, 60);
                            } else {
                                $editor = new XoopsFormTextArea($caption, $name, $value, 7, 60);
                            }
                        }
                    } else {
                        $editor = new XoopsFormEditor($caption, "koivi", $editor_configs);
                    }
                    break;

                case "spaw":
                    if (!$xoops22) {
                        if (is_readable(XOOPS_ROOT_PATH . "/class/spaw/formspaw.php")) {
                            include_once(XOOPS_ROOT_PATH . "/class/spaw/formspaw.php");
                            $editor = new XoopsFormSpaw($caption, $name, $value);
                        }
                    } else {
                        $editor = new XoopsFormEditor($caption, "spaw", $editor_configs);
                    }
                    break;

                case "htmlarea":
                    if (!$xoops22) {
                        if (is_readable(XOOPS_ROOT_PATH . "/class/htmlarea/formhtmlarea.php")) {
                            include_once(XOOPS_ROOT_PATH . "/class/htmlarea/formhtmlarea.php");
                            $editor = new XoopsFormHtmlarea($caption, $name, $value);
                        }
                    } else {
                        $editor = new XoopsFormEditor($caption, "htmlarea", $editor_configs);
                    }
                    break;

                default:
                case 'textarea':
                    $form_rows = isset($control['rows']) ? $control['rows'] : 5;
                    $form_cols = isset($control['cols']) ? $control['cols'] : 60;

                    $editor = new XoopsFormTextArea($var['form_caption'], $key, $this->targetObject->getVar($key, 'e'), $form_rows, $form_cols);
                    if ($var['form_dsc']) {
                        $editor->setDescription($var['form_dsc']);
                    }
                    break;
            }
        }

        return $editor;
    }

    /**
     * @param string $key
     * @param string $var
     * @param bool   $multiple
     *
     * @return XoopsFormSelect
     */
    public function getThemeSelect($key, $var, $multiple = false)
    {

        $size = $multiple ? 5 : 1;
        $theme_select = new XoopsFormSelect($var['form_caption'], $key, $this->targetObject->getVar($key), $size, $multiple);

        $handle = opendir(XOOPS_THEME_PATH . '/');
        $dirlist = array();
        while (false !== ($file = readdir($handle))) {
            if (is_dir(XOOPS_THEME_PATH . '/' . $file) && !preg_match("/^[.]{1,2}$/", $file) && strtolower($file) != 'cvs') {
                $dirlist[$file] = $file;
            }
        }
        closedir($handle);
        if (!empty($dirlist)) {
            asort($dirlist);
            $theme_select->addOptionArray($dirlist);
        }

        return $theme_select;
    }
    /*
        function &getElementById($keyname)
        {
            foreach ($this->_elements as $eleObj) {
                if ($eleObj->getName() == $keyname) {
                    $ret =& $eleObj;
                    break;
                }
            }
            return isset($ret) ? $ret : false;
        }
    */
    /**
     * create HTML to output the form as a theme-enabled table with validation.
     *
     * @return    string
     */
    public function render()
    {
        $required =& $this->getRequired();
        $ret = "
			<form name='" . $this->getName() . "' id='" . $this->getName() . "' action='" . $this->getAction() . "' method='" . $this->getMethod() . "' onsubmit='return xoopsFormValidate_" . $this->getName() . "(this);'" . $this->getExtra() . ">
			<table width='100%' class='outer' cellspacing='1'>
			<tr><th colspan='2'>" . $this->getTitle() . "</th></tr>
		";
        $hidden = '';
        $class = 'even';
        foreach ($this->getElements() as $ele) {
            /* @var $ele XoopsFormElement */
            if (!is_object($ele)) {
                $ret .= $ele;
            } elseif (!$ele->isHidden()) {
                //$class = ( $class == 'even' ) ? 'odd' : 'even';
                $ret .= "<tr id='" . $ele->getName() . "' valign='top' align='left'><td class='head'>" . $ele->getCaption();
                if ($ele->getDescription() != '') {
                    $ret .= '<br /><br /><span style="font-weight: normal;">' . $ele->getDescription() . '</span>';
                }
                $ret .= "</td><td class='$class'>" . $ele->render() . "</td></tr>\n";
            } else {
                $hidden .= $ele->render();
            }
        }
        $ret .= "</table>\n$hidden\n</form>\n";
        $ret .= $this->renderValidationJS(true);
        return $ret;
    }

    /**
     * assign to smarty form template instead of displaying directly
     *
     * @param object $tpl reference to a {@link Smarty} object
     * @param bool   $smartyName
     */
    public function assign($tpl, $smartyName = false)
    {
        $i = 0;
        $elements = array();
        foreach ($this->getElements() as $ele) {
            /* @var $ele XoopsFormElement */
            $n = ($ele->getName() != "") ? $ele->getName() : $i;
            $elements[$n]['name'] = $ele->getName();
            $elements[$n]['caption'] = $ele->getCaption();
            $elements[$n]['body'] = $ele->render();
            $elements[$n]['hidden'] = $ele->isHidden();
            $elements[$n]['section'] = strToLower(get_class($ele)) == strToLower('Xmf_Form_Element_Section');
            $elements[$n]['section_close'] = get_class($ele) == 'Xmf_Form_Element_Sectionclose';
            $elements[$n]['hide'] = isset($this->targetObject->vars[$n]['hide']) ? $this->targetObject->vars[$n]['hide'] : false;
            if ($ele->getDescription() != '') {
                $elements[$n]['description'] = $ele->getDescription();
            }
            $i++;
        }
        $js = $this->renderValidationJS();
        if (!$smartyName) {
            $smartyName = $this->getName();
        }

        $tpl->assign($smartyName, array(
                'title'      => $this->getTitle(),
                'name'       => $this->getName(),
                'action'     => $this->getAction(),
                'method'     => $this->getMethod(),
                'extra'      => 'onsubmit="return xoopsFormValidate_' . $this->getName() . '(this);"' . $this->getExtra(),
                'javascript' => $js,
                'elements'   => $elements
        ));
    }

    /**
     * @param bool $withtags
     *
     * @return string
     */
    public function renderValidationJS($withtags = true)
    {
        $js = "";
        if ($withtags) {
            $js .= "\n<!-- Start Form Validation JavaScript //-->\n<script type='text/javascript'>\n<!--//\n";
        }
        $myts =& MyTextSanitizer::getInstance();
        $formname = $this->getName();
        $js .= "function xoopsFormValidate_{$formname}(myform) {";
        // First, output code to check required elements
        $elements = $this->getRequired();
        foreach ($elements as $elt) {
            /* @var $elt XoopsFormElement */
            $eltname = $elt->getName();
            $eltcaption = trim($elt->getCaption());
            $eltmsg = empty($eltcaption) ? sprintf(_FORM_ENTER, $eltname) : sprintf(_FORM_ENTER, $eltcaption);
            $eltmsg = str_replace('"', '\"', stripslashes($eltmsg));
            if (strtolower(get_class($elt)) == 'xoopsformradio') {
                $js .= "var myOption = -1;";
                $js .= "for (i=myform.{$eltname}.length-1; i > -1; i--) {
					if (myform.{$eltname}[i].checked) {
						myOption = i; i = -1;
					}
				}
				if (myOption == -1) {
					window.alert(\"{$eltmsg}\"); myform.{$eltname}[0].focus(); return false; }\n";
            } elseif (strtolower(get_class($elt)) == 'smartformselect_multielement') {
                $js .= "var hasSelections = false;";
                $js .= "for(var i = 0; i < myform['{$eltname}[]'].length; i++){
					if (myform['{$eltname}[]'].options[i].selected) {
						hasSelections = true;
					}

				}
				if (hasSelections == false) {
					window.alert(\"{$eltmsg}\"); myform['{$eltname}[]'].options[0].focus(); return false; }\n";
            } elseif (strtolower(get_class($elt)) == 'xoopsformcheckbox' || strtolower(get_class($elt)) == 'smartformcheckelement') {
                $js .= "var hasSelections = false;";
                //sometimes, there is an implicit '[]', sometimes not
                if (strpos($eltname, '[') === false) {
                    $js .= "for(var i = 0; i < myform['{$eltname}[]'].length; i++){
						if (myform['{$eltname}[]'][i].checked) {
							hasSelections = true;
						}

					}
					if (hasSelections == false) {
						window.alert(\"{$eltmsg}\"); myform['{$eltname}[]'][0].focus(); return false; }\n";
                } else {
                    $js .= "for(var i = 0; i < myform['{$eltname}'].length; i++){
						if (myform['{$eltname}'][i].checked) {
							hasSelections = true;
						}

					}
					if (hasSelections == false) {
						window.alert(\"{$eltmsg}\"); myform['{$eltname}'][0].focus(); return false; }\n";
                }
            } else {
                $js .= "if ( myform.{$eltname}.value == \"\" ) " . "{ window.alert(\"{$eltmsg}\"); myform.{$eltname}.focus(); return false; }\n";
            }
        }
        // Now, handle custom validation code
        $elements = $this->getElements(true);
        foreach ($elements as $elt) {
            if (method_exists($elt, 'renderValidationJS') && strtolower(get_class($elt)) != 'xoopsformcheckbox') {
                if ($eltjs = $elt->renderValidationJS()) {
                    $js .= $eltjs . "\n";
                }
            }
        }
        $js .= "return true;\n}\n";
        if ($withtags) {
            $js .= "//--></script>\n";
            $js .= "<!-- End Form Vaidation JavaScript //-->\n";
        }
        return $js;
    }
}
