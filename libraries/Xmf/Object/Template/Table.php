<?php

/**
 * Contains the classes responsible for displaying a simple table filled with records of SmartObjects
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartobjecttable.php 2067 2008-05-08 16:12:18Z fx2024 $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectTable
 */
class Xmf_Object_Template_Table extends Xmf_Object_Template_Abstract
{
    var $_id;
    /**
     * @var XoopsPersistableObjectHandler
     */
    var $_objectHandler;
    /**
     * @var array
     */
    var $_columns = array();
    /**
     * @var false|CriteriaCompo
     */
    var $_criteria = false;
    var $_actions = array('edit', 'delete');
    /**
     * @var array
     */
    var $_objects = array();
    var $_aObjects;
    var $_custom_actions = array();
    var $_sortsel;
    var $_ordersel;
    var $_limitsel;
    var $_filtersel;
    var $_filterseloptions;
    var $_filtersel2;
    var $_filtersel2options;
    var $_filtersel2optionsDefault;
    var $_tempObject;
    var $_introButtons;
    var $_quickSearch = false;
    var $_actionButtons = false;
    var $_head_css_class = 'bg3';
    var $_hasActions = false;
    var $_userSide = false;
    var $_printerFriendlyPage = false;
    var $_tableHeader = false;
    var $_tableFooter = false;
    var $_showActionsColumnTitle = true;
    var $_isTree = false;
    var $_showFilterAndLimit = true;
    var $_enableColumnsSorting = true;
    var $_customTemplate = false;
    var $_withSelectedActions = array();
    var $_itemName;
    var $_pageName;

    public function init()
    {
        $objectHandler = $this->_decorator->getHandler();
        $this->_id = $objectHandler->className;
        $this->_objectHandler = $objectHandler;
        $this->_itemName = $this->_decorator->getHandlerName();
        $this->_pageName = $this->_decorator->getHandlerName() . '.php';
        $this->_criteria = new CriteriaCompo();
        /*if ($userSide) {
            $this->_head_css_class = 'head';
        }*/
        $this->setTemplate(XOOPS_ROOT_PATH . '/modules/xmf/templates/xmf_object_table.html');
    }

    /**
     * @return Xmf_Request_Http
     */
    public function request()
    {
        return Xmf_Request::getInstance();
    }

    /**
     * @return Xmf_Response_Http
     */
    public function response()
    {
        return Xmf_Response::getInstance();
    }

    /**
     * @param string $op
     * @param string $caption
     * @param string $text
     */
    public function addActionButton($op, $caption = '', $text = '')
    {
        $action = array(
                'op'      => $op,
                'caption' => $caption,
                'text'    => $text
        );
        $this->_actionButtons[] = $action;
    }

    /**
     * @param Xmf_Object_Template_Table_Column $columnObj
     */
    public function addColumn(Xmf_Object_Template_Table_Column $columnObj)
    {
        $this->_columns[] = $columnObj;
    }

    /**
     * @param string $name
     * @param string $location
     * @param string $value
     */
    public function addIntroButton($name, $location, $value)
    {
        $introButton = array();
        $introButton['name'] = $name;
        $introButton['location'] = $location;
        $introButton['value'] = $value;
        $this->_introButtons[] = $introButton;
        unset($introButton);
    }

    public function addPrinterFriendlyLink()
    {
        $this->_printerFriendlyPage = Xmf_Utils::getCurrentPage() . '&print';
    }

    /**
     * @param array  $fields
     * @param string $caption
     */
    public function addQuickSearch($fields, $caption = _OBJ_XMF_QUICK_SEARCH)
    {
        $this->_quickSearch = array('fields' => $fields, 'caption' => $caption);
    }

    /**
     * @param string $content
     */
    public function addHeader($content)
    {
        $this->_tableHeader = $content;
    }

    /**
     * @param string $content
     */
    public function addFooter($content)
    {
        $this->_tableFooter = $content;
    }

    /**
     * @param $caption
     */
    public function addDefaultIntroButton($caption)
    {
        $this->addIntroButton($this->_itemName, $this->_pageName . "?op=mod", $caption);
    }

    /**
     * @param string $method
     */
    public function addCustomAction($method)
    {
        $this->_custom_actions[] = $method;
    }

    /**
     * @param string $default_sort
     */
    public function setDefaultSort($default_sort)
    {
        $this->_sortsel = $default_sort;
    }

    /**
     * @return string
     */
    public function getDefaultSort()
    {
        if ($this->_sortsel) {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_sortsel', $this->_sortsel);
        } else {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_sortsel', $this->_objectHandler->identifierName);
        }
    }

    /**
     * @param $default_order
     */
    public function setDefaultOrder($default_order)
    {
        $this->_ordersel = $default_order;
    }

    /**
     * @return string
     */
    public function getDefaultOrder()
    {
        if ($this->_ordersel) {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_ordersel', $this->_ordersel);
        } else {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_ordersel', 'ASC');
        }
    }

    /**
     * @param array $actions
     */
    public function addWithSelectedActions($actions = array())
    {
        $this->addColumn(new Xmf_Object_Template_Table_Column('checked', 'center', 20, false, false, '&nbsp;'));
        $this->_withSelectedActions = $actions;
    }

    /**
     * Adding a filter in the table
     *
     * @param string $key    key to the field that will be used for sorting
     * @param string $method method of the handler that will be called to populate the options when this filter is selected
     * @param bool   $default
     */
    public function addFilter($key, $method, $default = false)
    {
        $this->_filterseloptions[$key] = $method;
        $this->_filtersel2optionsDefault = $default;
    }

    /**
     * @param string $default_filter
     */
    public function setDefaultFilter($default_filter)
    {
        $this->_filtersel = $default_filter;
    }

    public function isForUserSide()
    {
        $this->_userSide = true;
    }

    /**
     * @param string $template
     */
    public function setCustomTemplate($template)
    {
        $this->_customTemplate = $template;
    }

    public function setSortOrder()
    {
        $this->_sortsel = $this->request()->asStr($this->_itemName . '_' . 'sortsel', $this->getDefaultSort());
        $this->response()->setCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_sortsel', $this->_sortsel);
        /*if (isset($this->_tempObject->vars[$this->_sortsel]['itemName']) && $this->_tempObject->vars[$this->_sortsel]['itemName']) {
            $this->_criteria->setSort($this->_tempObject->vars[$this->_sortsel]['itemName'] . "." . $this->_sortsel);
        } else {
            $this->_criteria->setSort($this->_itemName . "." . $this->_sortsel);
        }
*/
        $this->_criteria->setSort($this->_sortsel);
        $this->_ordersel = $this->request()->asStr($this->_itemName . '_' . 'ordersel', $this->getDefaultOrder());
        $this->response()->setCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_ordersel', $this->_ordersel);
        $this->_criteria->setOrder($this->_ordersel);
    }

    /**
     * @param $id
     */
    public function setTableId($id)
    {
        $this->_id = $id;
    }

    /**
     * @param $objects
     */
    public function setObjects($objects)
    {
        $this->_objects = $objects;
    }

    /**
     *
     */
    public function createTableRows()
    {
        $this->_aObjects = array();
        $doWeHaveActions = false;
        $objectclass = 'odd';

        if (count($this->_objects) > 0) {
            /* @var $object XoopsObject */
            foreach ($this->_objects as $object) {
                $decorator = new Xmf_Object_Decorator_Form($object, $this->_decorator->getHelper(), $this->_decorator->getHandlerName());
                $aObject = array();
                $i = 0;
                $aColumns = array();

                /* @var $column Xmf_Object_Template_Table_Column */
                foreach ($this->_columns as $column) {
                    $aColumn = array();
                    if ($i == 0) {
                        $class = "head";
                    } elseif ($i % 2 == 0) {;
                        $class = "even";
                    } else {
                        $class = "odd";
                    }
                    if (method_exists($object, 'initiateCustomFields')) {
                        //$object->initiateCustomFields();
                    }
                    if ($column->getKeyName() == 'checked') {
                        $value = '<input type ="checkbox" name="selected_smartobjects[]" value="' . $decorator->id() . '" />';
                    } elseif ($column->getCustomMethodForValue() && method_exists($object, $column->getCustomMethodForValue())) {
                        $method = $column->getCustomMethodForValue();
                        if ($column->getExtraParams()) {
                            $value = $object->$method($column->getExtraParams());
                        } else {
                            $value = $object->$method();
                        }
                    } else {
                        /**
                         * If the column is the identifier, then put a link on it
                         */
                        if ($column->getKeyName() == $this->_objectHandler->identifierName) {
                            $value = $decorator->getItemLink();
                        } else {
                            $value = $object->getVar($column->getKeyName());
                        }
                    }

                    $aColumn['value'] = $value;
                    $aColumn['class'] = $class;
                    $aColumn['width'] = $column->getWidth();
                    $aColumn['align'] = $column->getAlign();

                    $aColumns[] = $aColumn;
                    $i++;
                }

                $aObject['columns'] = $aColumns;
                $aObject['id'] = $decorator->id();

                $objectclass = ($objectclass == 'even') ? 'odd' : 'even';

                $aObject['class'] = $objectclass;

                $actions = array();

                // Adding the custom actions if any
                foreach ($this->_custom_actions as $action) {
                    if (method_exists($object, $action)) {
                        $actions[] = $object->$action();
                    }
                }

                $controller = new Xmf_Object_Controller($this->_decorator);

                if ((!is_array($this->_actions)) || in_array('edit', $this->_actions)) {
                    $actions[] = $controller->getEditItemLink($object, false, true, $this->_userSide);
                }
                if ((!is_array($this->_actions)) || in_array('delete', $this->_actions)) {
                    $actions[] = $controller->getDeleteItemLink($object, false, true, $this->_userSide);
                }
                $aObject['actions'] = $actions;

                $this->tpl->assign('smartobject_actions_column_width', count($actions) * 30);

                $doWeHaveActions = $doWeHaveActions ? true : count($actions) > 0;

                $this->_aObjects[] = $aObject;
            }
            $this->tpl->assign('smartobject_objects', $this->_aObjects);
        } else {
            $colspan = count($this->_columns) + 1;
            $this->tpl->assign('smartobject_colspan', $colspan);
        }
        $this->_hasActions = $doWeHaveActions;
    }

    public function fetchObjects()
    {
        return $this->_objectHandler->getObjects($this->_criteria, true, true, false);
    }

    /**
     * @return null|string
     */
    public function getDefaultFilter()
    {
        if ($this->_filtersel) {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_filtersel', $this->_filtersel);
        } else {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_filtersel', 'default');
        }
    }

    /**
     * @return array|bool
     */
    public function getFiltersArray()
    {
        $ret = array();
        $field = array();
        $field['caption'] = _OBJ_XMF_NONE;
        $field['selected'] = '';
        $ret['default'] = $field;
        unset($field);

        if ($this->_filterseloptions) {
            foreach ($this->_filterseloptions as $key => $value) {
                $field = array();
                if (is_array($value)) {
                    $field['caption'] = $key;
                    $field['selected'] = $this->_filtersel == $key ? "selected='selected'" : '';
                } else {
                    $field['caption'] = $this->_tempObject->vars[$key]['form_caption'];
                    $field['selected'] = $this->_filtersel == $key ? "selected='selected'" : '';
                }
                $ret[$key] = $field;
                unset($field);
            }
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * @param $default_filter2
     */
    public function setDefaultFilter2($default_filter2)
    {
        $this->_filtersel2 = $default_filter2;
    }

    /**
     * @return string
     */
    public function getDefaultFilter2()
    {
        if ($this->_filtersel2) {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_filtersel2', $this->_filtersel2);
        } else {
            return $this->request()->getCookie($_SERVER['PHP_SELF'] . '_filtersel2', 'default');
        }
    }

    /**
     * @return array
     */
    public function getFilters2Array()
    {
        $ret = array();

        foreach ($this->_filtersel2options as $key => $value) {
            $field = array();
            $field['caption'] = $value;
            $field['selected'] = $this->_filtersel2 == $key ? "selected='selected'" : '';
            $ret[$key] = $field;
            unset($field);
        }
        return $ret;
    }

    /**
     * @param array $limitsArray
     */
    public function renderOptionSelection($limitsArray)
    {
        // Rendering the form to select options on the table
        $current_url = Xmf_Utils::getCurrentPage();

        /**
         * What was $params_of_the_options_sel doing again ?
         */
        //$this->tpl->assign('smartobject_optionssel_action', $_SERVER['PHP_SELF'] . "?" . implode('&', $params_of_the_options_sel));
        $this->tpl->assign('smartobject_optionssel_action', $current_url);
        $this->tpl->assign('smartobject_optionssel_limitsArray', $limitsArray);
    }

    /**
     * @return array
     */
    public function getLimitsArray()
    {
        $ret = array();
        $ret['all']['caption'] = _OBJ_XMF_LIMIT_ALL;
        $ret['all']['selected'] = ('all' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['5']['caption'] = '5';
        $ret['5']['selected'] = ('5' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['10']['caption'] = '10';
        $ret['10']['selected'] = ('10' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['15']['caption'] = '15';
        $ret['15']['selected'] = ('15' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['20']['caption'] = '20';
        $ret['20']['selected'] = ('20' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['25']['caption'] = '25';
        $ret['25']['selected'] = ('25' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['30']['caption'] = '30';
        $ret['30']['selected'] = ('30' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['35']['caption'] = '35';
        $ret['35']['selected'] = ('35' == $this->_limitsel) ? "selected='selected'" : "";

        $ret['40']['caption'] = '40';
        $ret['40']['selected'] = ('40' == $this->_limitsel) ? "selected='selected'" : "";
        return $ret;
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->_objects;
    }

    public function hideActionColumnTitle()
    {
        $this->_showActionsColumnTitle = false;
    }

    public function hideFilterAndLimit()
    {
        $this->_showFilterAndLimit = false;
    }

    /**
     * @return array
     */
    public function getOrdersArray()
    {
        $ret = array();
        $ret['ASC']['caption'] = _OBJ_XMF_SORT_ASC;
        $ret['ASC']['selected'] = ('ASC' == $this->_ordersel) ? "selected='selected'" : "";

        $ret['DESC']['caption'] = _OBJ_XMF_SORT_DESC;
        $ret['DESC']['selected'] = ('DESC' == $this->_ordersel) ? "selected='selected'" : "";

        return $ret;
    }

    public function render()
    {
        /**
         * We need access to the vars of the SmartObject for a few things in the table creation.
         * Since we may not have a SmartObject to look into now, let's create one for this purpose
         * and we will free it after
         */
        $this->_tempObject =& $this->_objectHandler->create();

        $this->_criteria->setStart($this->request()->asInt('start' . $this->_objectHandler->keyName,0));

        $this->setSortOrder();

        if (!$this->_isTree) {
            $this->_limitsel = $this->request()->asInt('limitsel',$this->request()->getCookie($_SERVER['PHP_SELF'] . '_limitsel', 15));
             } else {
            $this->_limitsel = 'all';
        }
        if ($this->request()->is('post')) {
            $this->_limitsel = $this->request()->asInt('limitsel',$this->_limitsel);
        }
        $this->response()->setCookie($_SERVER['PHP_SELF'] . '_limitsel', $this->_limitsel);
        $limitsArray = $this->getLimitsArray();

        $this->_criteria->setLimit($this->_limitsel);
        $this->_filtersel = $this->request()->asInt('filtersel', $this->getDefaultFilter());
        $this->response()->setCookie($_SERVER['PHP_SELF'] . '_' . $this->_id . '_filtersel', $this->_filtersel);
        $filtersArray = $this->getFiltersArray();

        if ($filtersArray) {
            $this->tpl->assign('smartobject_optionssel_filtersArray', $filtersArray);
        }

        // Check if the selected filter is defined and if so, create the selfilter2
        if (isset($this->_filterseloptions[$this->_filtersel])) {
            // check if method associate with this filter exists in the handler
            if (is_array($this->_filterseloptions[$this->_filtersel])) {
                $filter = $this->_filterseloptions[$this->_filtersel];
                $this->_criteria->add($filter['criteria']);
            } else {
                if (method_exists($this->_objectHandler, $this->_filterseloptions[$this->_filtersel])) {

                    // then we will create the selfilter2 options by calling this method
                    $method = $this->_filterseloptions[$this->_filtersel];
                    $this->_filtersel2options = $this->_objectHandler->$method();

                    $this->_filtersel2 = isset($_GET['filtersel2']) ? $_GET['filtersel2'] : $this->getDefaultFilter2();
                    $this->_filtersel2 = isset($_POST['filtersel2']) ? $_POST['filtersel2'] : $this->_filtersel2;

                    $filters2Array = $this->getFilters2Array();
                    $this->tpl->assign('smartobject_optionssel_filters2Array', $filters2Array);

                    $this->response()->setCookie($_SERVER['PHP_SELF'] . '_filtersel2', $this->_filtersel2);
                    if ($this->_filtersel2 != 'default') {
                        $this->_criteria->add(new Criteria($this->_filtersel, $this->_filtersel2));
                    }
                }
            }
        }
        // Check if we have a quicksearch

        if (isset($_POST['quicksearch_' . $this->_id]) && $_POST['quicksearch_' . $this->_id] != '') {
            $quicksearch_criteria = new CriteriaCompo();
            if (is_array($this->_quickSearch['fields'])) {
                foreach ($this->_quickSearch['fields'] as $v) {
                    $quicksearch_criteria->add(new Criteria($v, '%' . $_POST['quicksearch_' . $this->_id] . '%', 'LIKE'), 'OR');
                }
            } else {
                $quicksearch_criteria->add(new Criteria($this->_quickSearch['fields'], '%' . $_POST['quicksearch_' . $this->_id] . '%', 'LIKE'));
            }
            $this->_criteria->add($quicksearch_criteria);
        }

        $this->_objects = $this->fetchObjects();

        xoops_load('pagenav');
        /**
             * $params_of_the_options_sel is an array with all the parameters of the page
             * but without the pagenave parameters. This array will be used in the
             * OptionsSelection
             */
            $params_of_the_options_sel = array();

        if ($this->_criteria->getLimit() > 0) {

            /**
             * Geeting rid of the old params
             * $new_get_array is an array containing the new GET parameters
             */
            $new_get_array = array();



            $not_needed_params = array('sortsel', 'limitsel', 'ordersel', 'start' . $this->_objectHandler->keyName);
            foreach ($_GET as $k => $v) {
                if (!in_array($k, $not_needed_params)) {
                    $new_get_array[] = "$k=$v";
                    $params_of_the_options_sel[] = "$k=$v";
                }
            }

            /**
             * Adding the new params of the pagenav
             */
            $new_get_array[] = "sortsel=" . $this->_sortsel;
            $new_get_array[] = "ordersel=" . $this->_ordersel;
            $new_get_array[] = "limitsel=" . $this->_limitsel;
            $otherParams = implode('&', $new_get_array);

            $pagenav = new XoopsPageNav($this->_objectHandler->getCount($this->_criteria), $this->_criteria->getLimit(), $this->_criteria->getStart(), 'start' . $this->_objectHandler->keyName, $otherParams);
            $this->tpl->assign('smartobject_pagenav', $pagenav->renderNav());
        }
        $this->renderOptionSelection($limitsArray, $params_of_the_options_sel);

        // retreive the current url and the query string
        $current_urls = Xmf_Utils::getCurrentUrls();
        $current_url = $current_urls['full_phpself'];
        $query_string = $current_urls['querystring'];
        if ($query_string) {
            $query_string = str_replace('?', '', $query_string);
        }
        $query_stringArray = explode('&', $query_string);
        $new_query_stringArray = array();
        foreach ($query_stringArray as $query_string) {
            if (strpos($query_string, 'sortsel') == false && strpos($query_string, 'ordersel') == false) {
                $new_query_stringArray[] = $query_string;
            }
        }
        $new_query_string = implode('&', $new_query_stringArray);

        $orderArray = array();
        $orderArray['ASC']['image'] = 'desc.png';
        $orderArray['ASC']['neworder'] = 'DESC';
        $orderArray['DESC']['image'] = 'asc.png';
        $orderArray['DESC']['neworder'] = 'ASC';

        $aColumns = array();

        /* @var $column Xmf_Object_Template_Table_Column */
        foreach ($this->_columns as $column) {
            $aColumn = array();
            $aColumn['width'] = $column->getWidth();
            $aColumn['align'] = $column->getAlign();
            $aColumn['key'] = $column->getKeyName();
            if ($column->getKeyName() == 'checked') {
                $aColumn['caption'] = '<input type ="checkbox" id="checkall_smartobjects" name="checkall_smartobjects"' . ' value="checkall_smartobjects" onclick="smartobject_checkall(window.document.form_' . $this->_id . ', \'selected_smartobjects\');" />';
            } elseif ($column->getCustomCaption()) {
                $aColumn['caption'] = $column->getCustomCaption();
            } else {
                $aColumn['caption'] = isset($this->_tempObject->vars[$column->getKeyName()]['form_caption']) ? $this->_tempObject->vars[$column->getKeyName()]['form_caption'] : $column->getKeyName();
            }
            // Are we doing a GET sort on this column ?
            $getSort = (isset($_GET[$this->_itemName . '_' . 'sortsel']) && $_GET[$this->_itemName . '_' . 'sortsel'] == $column->getKeyName()) || ($this->_sortsel == $column->getKeyName());
            $order = isset($_GET[$this->_itemName . '_' . 'ordersel']) ? $_GET[$this->_itemName . '_' . 'ordersel'] : 'DESC';

            $qs_param = '';
            if (isset($_REQUEST['quicksearch_' . $this->_id]) && $_REQUEST['quicksearch_' . $this->_id] != '') {
                $qs_param = "&quicksearch_" . $this->_id . "=" . $_REQUEST['quicksearch_' . $this->_id];
            }
            if (!$this->_enableColumnsSorting || $column->getKeyName() == 'checked' || !$column->isSortable()) {
                //$aColumn['caption'] = $aColumn['caption'];
            } elseif ($getSort) {
                $aColumn['caption'] = '<a href="' . $current_url . '?' . $this->_itemName . '_' . 'sortsel=' . $column->getKeyName() . '&' . $this->_itemName . '_' . 'ordersel=' . $orderArray[$order]['neworder'] . $qs_param . '&' . $new_query_string . '">' . $aColumn['caption'] . ' <img src="' . SMARTOBJECT_IMAGES_ACTIONS_URL . $orderArray[$order]['image'] . '" alt="ASC" /></a>';
            } else {
                $aColumn['caption'] = '<a href="' . $current_url . '?' . $this->_itemName . '_' . 'sortsel=' . $column->getKeyName() . '&' . $this->_itemName . '_' . 'ordersel=ASC' . $qs_param . '&' . $new_query_string . '">' . $aColumn['caption'] . '</a>';
            }
            $aColumns[] = $aColumn;
        }
        $this->tpl->assign('smartobject_columns', $aColumns);

        if ($this->_quickSearch) {
            $this->tpl->assign('smartobject_quicksearch', $this->_quickSearch['caption']);
        }

        $this->createTableRows();

        $this->tpl->assign('smartobject_showFilterAndLimit', $this->_showFilterAndLimit);
        $this->tpl->assign('smartobject_isTree', $this->_isTree);
        $this->tpl->assign('smartobject_show_action_column_title', $this->_showActionsColumnTitle);
        $this->tpl->assign('smartobject_table_header', $this->_tableHeader);
        $this->tpl->assign('smartobject_table_footer', $this->_tableFooter);
        $this->tpl->assign('smartobject_printer_friendly_page', $this->_printerFriendlyPage);
        $this->tpl->assign('smartobject_user_side', $this->_userSide);
        $this->tpl->assign('smartobject_has_actions', $this->_hasActions);
        $this->tpl->assign('smartobject_head_css_class', $this->_head_css_class);
        $this->tpl->assign('smartobject_actionButtons', $this->_actionButtons);
        $this->tpl->assign('smartobject_introButtons', $this->_introButtons);
        $this->tpl->assign('smartobject_id', $this->_id);
        if (!empty($this->_withSelectedActions)) {
            $this->tpl->assign('smartobject_withSelectedActions', $this->_withSelectedActions);
        }

        if ($this->_customTemplate) {
            $this->setTemplate($this->_customTemplate);
        }
    }

    function disableColumnsSorting()
    {
        $this->_enableColumnsSorting = false;
    }
}