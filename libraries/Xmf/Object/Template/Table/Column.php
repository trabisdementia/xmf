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
class Xmf_Object_Template_Table_Column
{
    /**
     * @var string
     */
    protected $_keyname;
    /**
     * @var string
     */
    protected $_align;
    /**
     * @var string
     */
    protected $_width;
    /**
     * @var bool|string
     */
    protected $_customMethodForValue;
    /**
     * @var array
     */
    protected $_extraParams;
    /**
     * @var
     */
    protected $_sortable;
    /**
     * @var
     */
    protected $_customCaption;

    public function __construct($keyname, $align = 'left', $width = '', $customMethodForValue = false, $extraParams = false, $customCaption = false, $sortable = true)
    {
        $this->_keyname = $keyname;
        $this->_align = $align;
        $this->_width = $width;
        $this->_customMethodForValue = $customMethodForValue;
        $this->_sortable = $sortable;
        $this->_extraParams = $extraParams;
        $this->_customCaption = $customCaption;
    }

    public function getKeyName()
    {
        return $this->_keyname;
    }

    public function getAlign()
    {
        return $this->_align;
    }

    public function isSortable()
    {
        return $this->_sortable;
    }

    public function getWidth()
    {
        if ($this->_width) {
            $ret = $this->_width;
        } else {
            $ret = '';
        }
        return $ret;
    }

    public function getCustomCaption()
    {
        return $this->_customCaption;
    }

    /**
     * @param bool|string $customMethodForValue
     */
    public function setCustomMethodForValue($customMethodForValue)
    {
        $this->_customMethodForValue = $customMethodForValue;
    }

    /**
     * @return bool|string
     */
    public function getCustomMethodForValue()
    {
        return $this->_customMethodForValue;
    }

    /**
     * @param array $extraParams
     */
    public function setExtraParams($extraParams)
    {
        $this->_extraParams = $extraParams;
    }

    /**
     * @return array
     */
    public function getExtraParams()
    {
        return $this->_extraParams;
    }

    /**
     * @param string $keyname
     */
    public function setKeyname($keyname)
    {
        $this->_keyname = $keyname;
    }

    /**
     * @param mixed $sortable
     */
    public function setSortable($sortable)
    {
        $this->_sortable = $sortable;
    }

    /**
     * @return mixed
     */
    public function getSortable()
    {
        return $this->_sortable;
    }

}