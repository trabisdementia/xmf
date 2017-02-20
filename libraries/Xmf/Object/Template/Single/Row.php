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
class Xmf_Object_Template_Single_Row
{
    /**
     * @var string
     */
    protected $_keyname;
    /**
     * @var
     */
    protected $_align;
    /**
     * @var string
     */
    protected $_customMethodForValue;
    /**
     * @var bool
     */
    protected $_header;
    /**
     * @var string
     */
    protected $_class;

    public function __construct($keyname, $customMethodForValue = false, $header = false, $class = false)
    {
        $this->_keyname = $keyname;
        $this->_customMethodForValue = $customMethodForValue;
        $this->_header = $header;
        $this->_class = $class;
    }

    /**
     * @return bool
     */
    public function isHeader()
    {
        return $this->_header;
    }
       /**
     * @param mixed $align
     */
    public function setAlign($align)
    {
        $this->_align = $align;
    }

    /**
     * @return mixed
     */
    public function getAlign()
    {
        return $this->_align;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * @param string $customMethodForValue
     */
    public function setCustomMethodForValue($customMethodForValue)
    {
        $this->_customMethodForValue = $customMethodForValue;
    }

    /**
     * @return string
     */
    public function getCustomMethodForValue()
    {
        return $this->_customMethodForValue;
    }

    /**
     * @param bool $header
     */
    public function setHeader($header)
    {
        $this->_header = (bool)$header;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * @param string $keyname
     */
    public function setKeyname($keyname)
    {
        $this->_keyname = $keyname;
    }

    /**
     * @return string
     */
    public function getKeyname()
    {
        return $this->_keyname;
    }
}