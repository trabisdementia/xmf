<?php

/**
 * Contains the classe responsible for displaying a ingle SmartObject
 *
 * @license GNU
 * @author  marcan <marcan@smartfactory.ca>
 * @version $Id: smartobjectsingleview.php 839 2008-02-10 02:40:13Z malanciault $
 * @link    http://smartfactory.ca The SmartFactory
 * @package SmartObject
 */
class Xmf_Object_Template_Single extends Xmf_Object_template_Abstract
{
    protected $_userSide = false;
    protected $_rows = array();
    protected $_actions = array();
    protected $_headerAsRow = true;

    /**
     * @return void
     */
    protected function init()
    {
        $this->setTemplate(XOOPS_ROOT_PATH . '/modules/xmf/templates/xmf_object_single.html');
    }

    /**
     * @param array $actions
     */
    public function setActions($actions)
    {
        $this->_actions = (array)$actions;
    }

    /**
     * @param boolean $headerAsRow
     */
    public function setHeaderAsRow($headerAsRow)
    {
        $this->_headerAsRow = (bool)$headerAsRow;
    }

    /**
     * @param bool $userSide
     */
    public function setUserSide($userSide)
    {
        $this->_userSide = (bool)$userSide;
    }

    /**
     * @param mixed $rows
     */
    public function setRows($rows)
    {
        $this->_rows = $rows;
    }

    /**
     * @param Xmf_Object_Template_Single_Row $rowObj
     */
    public function addRow(Xmf_Object_Template_Single_Row $rowObj)
    {
        $this->_rows[] = $rowObj;
    }

    public function render()
    {
        $array = array();

        /* @var $row Xmf_Object_Template_Single_Row */
        foreach ($this->_rows as $row) {
            $key = $row->getKeyName();
            if ($row->getCustomMethodForValue() && method_exists($this->_object, $row->getCustomMethodForValue())) {
                $method = $row->getCustomMethodForValue();
                $value = $this->_object->$method();
            } else {
                $value = $this->_object->getVar($row->getKeyName());
            }
            if ($row->isHeader()) {
                $this->tpl->assign('xmf_object_single_header_caption', $this->_object->vars[$key]['form_caption']);
                $this->tpl->assign('xmf_object_single_header_value', $value);
            } else {
                $array[$key]['value'] = $value;
                $array[$key]['header'] = $row->isHeader();
                $array[$key]['caption'] = $this->_object->vars[$key]['form_caption'];
            }
        }
        $action_row = '';
        if (in_array('edit', $this->_actions)) {
            $action_row .= $this->_decorator->getEditItemLink(false, true, true);
        }
        if (in_array('delete', $this->_actions)) {
            $action_row .= $this->_decorator->getDeleteItemLink(false, true, true);
        }
        if ($action_row) {
            $array['zaction']['value'] = $action_row;
            $array['zaction']['caption'] = _OBJ_XMF_ACTIONS;
        }

        $this->tpl->assign('xmf_object_single_header_as_row', $this->_headerAsRow);
        $this->tpl->assign('xmf_object_single_array', $array);

    }
}