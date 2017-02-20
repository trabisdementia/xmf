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
 * @package         Xmf
 * @since           0.1
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id$
 */

defined('XMF_EXEC') or die('Xmf was not detected');

class Xmf_Object_Decorator_Form extends Xmf_Object_Decorator_Abstract
{
    protected $_image_path;
    protected $_image_url;
    protected $seoEnabled = false;
    protected $titleField;
    protected $summaryField = false;
    /**
     * References to control objects, managing the form fields of this object
     */
    protected $controls = array();

    public function init()
    {
        Xmf_Locale::load('xmf', $this->_dirname);
        $this->_initVars();
    }

    /**
     * Initialize object default vars
     */
    private function _initVars()
    {
        $vars = $this->_object->getVars();
        foreach (array_keys($vars) as $key) {
            $this->initVar($key);
        }
    }

    /**
     * @param string $key          key of this field. This needs to be the name of the field in the related database table
     * @param string $form_caption caption of this variable in a {@link SmartobjectForm} and title of a column in a  {@link SmartObjectTable}
     * @param string $form_dsc     description of this variable in a {@link SmartobjectForm}
     * @param bool   $sortby       set to TRUE to make this field used to sort objects in SmartObjectTable
     * @param bool   $persistent   set to FALSE if this field is not to be saved in the database
     * @param bool   $displayOnForm
     */
    public function initVar($key, $form_caption = '', $form_dsc = '', $sortby = false, $persistent = true, $displayOnForm = true)
    {
        //url_ is reserved for files.
        if (substr($key, 0, 4) == 'url_') {
            trigger_error("Cannot use variable starting with 'url_'.");
        }
        if (!$form_caption || $form_caption == '') {
            $form_caption = Xmf_Locale::translate('_XMF_' . $this->_dirname . '_' . $this->_handlerName . '_' . $key, Xmf_Utility_Inflector::humanize($key));
        }
        if (!$form_dsc || $form_dsc == '') {
            $form_dsc = Xmf_Locale::translate('_XMF_' . $this->_dirname . '_' . $this->_handlerName . '_' . $key . '_DSC', '');
        }

        $this->_object->vars[$key] = array_merge($this->_object->vars[$key], array(
                'form_caption'        => $form_caption,
                'form_dsc'            => $form_dsc,
            //'form_control'        => ($this->_object->vars[$key]['data_type'] == 2) ? 'txtarea' : 'textbox',
                'sortby'              => $sortby,
                'persistent'          => $persistent,
                'displayOnForm'       => $displayOnForm,
                'displayOnSingleView' => true,
                'readonly'            => false
        ));
        $this->hideFieldFromForm($this->getHandler()->keyName);
    }

    /**
     * Set control information for an instance variable
     * The $options parameter can be a string or an array. Using a string
     * is the quickest way :
     * $this->setControl('date', 'date_time');
     * This will create a date and time selectbox for the 'date' var on the
     * form to edit or create this item.
     * Here are the currently supported controls :
     *        - color
     *        - country
     *        - date_time
     *        - date
     *        - email
     *        - group
     *        - group_multi
     *        - image
     *        - imageupload
     *        - label
     *        - language
     *        - parentcategory
     *        - password
     *        - select_multi
     *        - select
     *        - text
     *        - textarea
     *        - theme
     *        - theme_multi
     *        - timezone
     *        - user
     *        - user_multi
     *        - yesno
     * Now, using an array as $options, you can customize what information to
     * use in the control. For example, if one needs to display a select box for
     * the user to choose the status of an item. We only need to tell SmartObject
     * what method to execute within what handler to retreive the options of the
     * selectbox.
     * $this->setControl('status', array('name' => false,
     *                                   'itemHandler' => 'item',
     *                                   'method' => 'getStatus',
     *                                   'module' => 'smartshop'));
     * In this example, the array elements are the following :
     *        - name : false, as we don't need to set a special control here.
     *                 we will use the default control related to the object type (defined in initVar)
     *        - itemHandler : name of the object for which we will use the handler
     *        - method : name of the method of this handler that we will execute
     *        - module : name of the module from wich the handler is
     * So in this example, SmartObject will create a selectbox for the variable 'status' and it will
     * populate this selectbox with the result from SmartshopItemHandler::getStatus()
     * Another example of the use of $options as an array is for TextArea :
     * $this->setControl('body', array('name' => 'textarea',
     *                                 'form_editor' => 'default'));
     * In this example, SmartObject will create a TextArea for the variable 'body'. And it will use
     * the 'default' editor, providing it is defined in the module
     * preferences : $xoopsModuleConfig['default_editor']
     * Of course, you can force the use of a specific editor :
     * $this->setControl('body', array('name' => 'textarea',
     *                                 'form_editor' => 'koivi'));
     * Here is a list of supported editor :
     *        - tiny : TinyEditor
     *        - dhtmltextarea : XOOPS DHTML Area
     *        - fckeditor    : FCKEditor
     *        - inbetween : InBetween
     *        - koivi : Koivi
     *        - spaw : Spaw WYSIWYG Editor
     *        - htmlarea : HTMLArea
     *        - textarea : basic textarea with no options
     *
     * @param string $key name of the variable for which we want to set a control
     * @param array  $options
     */
    public function setControl($key, $options = array())
    {
        if (is_string($options)) {
            $options = array('name' => $options);
        }
        $this->_object->vars[$key]['form_control'] = $options;
    }

    /**
     * Get control information for an instance variable
     *
     * @param string $key
     *
     * @return bool|array
     */
    public function getControl($key)
    {
        return isset($this->_object->vars[$key]['form_control']) ? $this->_object->vars[$key]['form_control'] : false;
    }

    /**
     * @return string
     */
    public function getDirname()
    {
        return $this->_dirname;
    }

    //todo, implement
    public function getImageDir()
    {
    }

    /**
     * @param string|array $key
     */
    public function makeFieldReadOnly($key)
    {
        if (is_array($key)) {
            foreach ($key as $v) {
                $this->_makeFieldreadOnly($v);
            }
        } else {
            $this->_makeFieldreadOnly($key);
        }
    }

    /**
     * @param string|array $key
     */
    public function showFieldOnForm($key)
    {
        if (is_array($key)) {
            foreach ($key as $v) {
                $this->_showFieldOnForm($v);
            }
        } else {
            $this->_showFieldOnForm($key);
        }
    }

    /**
     * @param string|array $key
     */
    public function hideFieldFromForm($key)
    {
        if (is_array($key)) {
            foreach ($key as $v) {
                $this->_hideFieldFromForm($v);
            }
        } else {
            $this->_hideFieldFromForm($key);
        }
    }

    /**
     * Get the id of the object
     *
     * @return int id of this object
     */
    public function id()
    {
        return $this->_object->getVar($this->getHandler()->keyName, 'e');
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function title($format = 's')
    {
        return $this->_object->getVar($this->getHandler()->identifierName, $format);
    }

    /**
     * Retrieve the object user side link
     *
     * @param bool $onlyUrl wether or not to return a simple URL or a full <a> link
     *
     * @return string user side link to the object
     */
    public function getItemLink($onlyUrl = false)
    {
        $controller = new Xmf_Object_Controller($this);
        return $controller->getItemLink($this->_object, $onlyUrl);
    }

    //todo, implement
    public function getEditItemLink($onlyUrl = false, $withimage = true, $userSide = false)
    {
        $controller = new Xmf_Object_Controller($this);
        return $controller->getEditItemLink($this->_object, $onlyUrl, $withimage, $userSide);
    }

    //todo, implement
    public function getDeleteItemLink($onlyUrl = false, $withimage = false, $userSide = false)
    {
        $controller = new Xmf_Object_Controller($this);
        return $controller->getDeleteItemLink($this->_object, $onlyUrl, $withimage, $userSide);
    }

    /**
     * @param string $key
     */
    protected function _makeFieldReadOnly($key)
    {
        if (isset($this->_object->vars[$key])) {
            $this->_object->vars[$key]['readonly'] = true;
            $this->_object->vars[$key]['displayOnForm'] = true;
        }
    }

    /**
     * @param string $key
     */
    protected function _hideFieldFromForm($key)
    {
        if (isset($this->_object->vars[$key])) {
            $this->_object->vars[$key]['displayOnForm'] = false;
        }
    }

    /**
     * @param string $key
     */
    protected function _showFieldOnForm($key)
    {
        if (isset($this->_object->vars[$key])) {
            $this->_object->vars[$key]['displayOnForm'] = true;
        }
    }
}