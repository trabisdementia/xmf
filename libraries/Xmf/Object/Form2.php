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

defined('XOOPS_ROOT_PATH') or die('Restricted access');

include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

class Xmf_Object_Form2 extends XoopsSimpleForm
{
    protected $title = 'TITLE';
    protected $action = 'index.php';
    protected $method = 'post';
    protected $token = false;
    protected $categories = array('default' => array('name' => 'Default', 'description' => ''));
    protected $items = array();
    protected $ui_Theme = 'base';

    /**
     * @param XoopsObject $obj
     */
    public function __construct(XoopsObject $obj)
    {
        $this->items = $this->_forgeItems($obj);
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param boolean $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return boolean
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param XoopsObject $obj
     *
     * @return array
     */
    public function _forgeItems(XoopsObject $obj)
    {
        $form_element = array(
                1  => 'textbox',
                2  => 'txtarea',
                3  => 'txtbox',
                4  => 'txtbox',
                5  => 'txtbox',
                6  => 'txtbox',
                7  => 'txtbox',
                8  => 'txtbox',
                9  => 'txtbox',
                10 => 'txtbox',
                11 => 'txtbox'
        );

        $vars = $obj->getVars();
        $items = array();
        foreach (array_keys($vars) as $key) {
            $items[] = array(
                    'name'        => $key,
                    'title'       => $key,
                    'value'       => $obj->getVar($key, 'e'),
                    'description' => '',
                    'formtype'    => $form_element[$vars[$key]['data_type']],
                    'valuetype'   => ($vars[$key]['data_type'] == 6) ? 'array' : 'text',
                    'default'     => '',
                    'options'     => '',
            );
        }
        return $items;
    }

    /**
     * array('form_title' => 'My Form',
     *       'form_name' => 'my_form',
     *       'form_action' => 'index.php',
     *       'form_method' => 'post',
     *       'form_token' => false,
     *       'categories' => array('name' => 'cat-general','title' => 'General Cat')
     *       'items' => array(
     *          'category' => 'cat-general',
     *          'name' => 'story_title',
     *          'title' => 'Title',
     *          'description' => 'Enter story title',
     *          'formtype' => 'textbox',
     *          'valuetype' => 'text',
     *          'default' => '',
     *          'options' => array()
     *      )
     * );
     */
    public function getForm()
    {
        parent::__construct($this->getTitle(), $this->getName(), $this->getAction(), $this->getMethod(), $this->getToken());

        $configs = $this->getItems();
        $configNames = array();
        foreach (array_keys($configs) as $i) {
            $configNames[$configs[$i]['name']] =& $configs[$i];
        }
        $configCats = $this->getCategories();

        foreach (array_keys($configNames) as $name) {
            if (!isset($configNames[$name]['category'])) {
                $configNames[$name]['category'] = 'default';
            }
        }

        $tabtray = new Xmf_Form_Element_Tabtray('', 'xmf_form_element_tabtray', $this->getUiTheme());
        $tabs = array();
        foreach ($configCats as $name => $info) {
            $tabs[$name] = new Xmf_Form_Element_Tab($info['name'], 'xmf_form_element_tab_' . $name);
            if (isset($info['description']) && $info['description'] != '') {
                $tabs[$name]->addElement(new XoopsFormLabel('', $info['description']));
            }
        }
        $count = count($configs);
        for ($i = 0; $i < $count; $i++) {
            $title = Xmf_Locale::translate($configs[$i]['title']);
            $desc = Xmf_Locale::translate($configs[$i]['description']);
            switch ($configs[$i]['formtype']) {

                case 'textarea':
                    $myts = MyTextSanitizer::getInstance();
                    if ($configs[$i]['valuetype'] == 'array') {
                        // this is exceptional.. only when value type is arrayneed a smarter way for this
                        $ele = ($configs[$i]['value'] != '') ? new XoopsFormTextArea($title, $configs[$i]['name'], $myts->htmlspecialchars(implode('|', $configs[$i]['value'])), 5, 5) : new XoopsFormTextArea($title, $configs[$i]['name'], '', 5, 5);
                    } else {
                        $ele = new XoopsFormTextArea($title, $configs[$i]['name'], $myts->htmlspecialchars($configs[$i]['value']), 5, 5);
                    }
                    break;

                case 'select':
                    $ele = new XoopsFormSelect($title, $configs[$i]['name'], $configs[$i]['value']);
                    $options = $configs[$i]['options'];
                    $opcount = count($options);
                    for ($j = 0; $j < $opcount; $j++) {
                        $optval = Xmf_Locale::translate($options[$j]['value']);
                        $optkey = Xmf_Locale::translate($options[$j]['name']);
                        $ele->addOption($optval, $optkey);
                    }
                    break;

                case 'select_multi':
                    $ele = new XoopsFormSelect($title, $configs[$i]['name'], $configs[$i]['value'], 5, true);
                    $options = $configs[$i]['options'];
                    $opcount = count($options);
                    for ($j = 0; $j < $opcount; $j++) {
                        $optval = Xmf_Locale::translate($options[$j]['value']);
                        $optkey = Xmf_Locale::translate($options[$j]['name']);
                        $ele->addOption($optval, $optkey);
                    }
                    break;

                case 'yesno':
                    $ele = new XoopsFormRadioYN($title, $configs[$i]['name'], $configs[$i]['value']);
                    break;

                case 'theme':
                case 'theme_multi':
                    xoops_load('xoopslists');
                    $ele = ($configs[$i]['formtype'] != 'theme_multi') ? new XoopsFormSelect($title, $configs[$i]['name'], $configs[$i]['value']) : new XoopsFormSelect($title, $configs[$i]['name'], $configs[$i]['value'], 5, true);
                    $dirlist = XoopsLists::getThemesList();
                    if (!empty($dirlist)) {
                        asort($dirlist);
                        $ele->addOptionArray($dirlist);
                    }
                    break;

                case 'timezone':
                    $ele = new XoopsFormSelectTimezone($title, $configs[$i]['name'], $configs[$i]['value']);
                    break;

                case 'language':
                    $ele = new XoopsFormSelectLang($title, $configs[$i]['name'], $configs[$i]['value']);
                    break;

                case 'locale':
                    $ele = new XoopsFormSelectLang($title, $configs[$i]['name'], $configs[$i]['value']);
                    break;

                case 'group':
                    $ele = new XoopsFormSelectGroup($title, $configs[$i]['name'], false, $configs[$i]['value'], 1, false);
                    break;

                case 'group_multi':
                    $ele = new XoopsFormSelectGroup($title, $configs[$i]['name'], false, $configs[$i]['value'], 5, true);
                    break;

                // RMV-NOTIFY: added 'user' and 'user_multi'
                case 'user':
                    $ele = new XoopsFormSelectUser($title, $configs[$i]['name'], false, $configs[$i]['value'], 1, false);
                    break;

                case 'user_multi':
                    $ele = new XoopsFormSelectUser($title, $configs[$i]['name'], false, $configs[$i]['value'], 5, true);
                    break;

                case 'password':
                    $myts = MyTextSanitizer::getInstance();
                    $ele = new XoopsFormPassword($title, $configs[$i]['name'], 5, 255, $myts->htmlspecialchars($configs[$i]['value']));
                    break;

                case 'color':
                    $myts = MyTextSanitizer::getInstance();
                    $ele = new XoopsFormColorPicker($title, $configs[$i]['name'], $myts->htmlspecialchars($configs[$i]['value']));
                    break;

                case 'hidden':
                    $myts = MyTextSanitizer::getInstance();
                    $ele = new XoopsFormHidden($configs[$i]['name'], $myts->htmlspecialchars($configs[$i]['value']));
                    break;

                case 'textbox':
                default:
                    $myts = MyTextSanitizer::getInstance();
                    $ele = new XoopsFormText($title, $configs[$i]['name'], 5, 255, $myts->htmlspecialchars($configs[$i]['value']));
                    break;
            }
            //$hidden = new XoopsFormHidden('conf_ids[]', $obj[$i]->getVar('conf_id'));
            if (isset($ele)) {
                $ele->setDescription($desc);
                if ($configs[$i]['formtype'] != 'hidden') {
                    $name = 'default';
                    if (isset($configNames[$configs[$i]['name']]['category'])) {
                        $name = $configNames[$configs[$i]['name']]['category'];
                    }
                    $tabs[$name]->addElement($ele);
                } else {
                    $this->addElement($ele);
                }
                //$this->addElement($hidden);
                unset($ele);
                unset($hidden);
            }
        }
        foreach (array_keys($tabs) as $name) {
            if ($tabs[$name]->getElements()) {
                $tabtray->addElement($tabs[$name]);
            }
        }
        $this->addElement($tabtray);
        $this->addElement(new XoopsFormHidden('op', 'save'));
        $this->addElement(new XoopsFormButton('', 'button', _SUBMIT, 'submit'));
    }

    /**
     * @param string $ui_Theme
     */
    public function setUiTheme($ui_Theme)
    {
        $this->ui_Theme = $ui_Theme;
    }

    /**
     * @return string
     */
    public function getUiTheme()
    {
        return $this->ui_Theme;
    }
}
