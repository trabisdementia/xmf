<?php

/**
 * Contains the SmartObjectControl class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformparentcategoryelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */
class Xmf_Object_Form_Element_Parentcategory extends XoopsFormSelect
{
    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        $control = $decorator->getControl($key);
        $addNoParent = isset($control['addNoParent']) ? $control['addNoParent'] : true;
        $criteria = new CriteriaCompo();
        $criteria->setSort("weight, name");
        $category_handler = xoops_getmodulehandler('category', $decorator->getDirname());
        $categories = $category_handler->getObjects($criteria);

        XoopsLoad::load('xoopsobjecttree');
        $mytree = new XoopsObjectTree($categories, "categoryid", "parentid");
        parent::__construct($object->vars[$key]['form_caption'], $key, $object->getVar($key, 'e'));

        $ret = array();
        $options = $this->getOptionArray($mytree, "name", 0, "", $ret);
        if ($addNoParent) {
            $newOptions = array('0' => '----');
            foreach ($options as $k => $v) {
                $newOptions[$k] = $v;
            }
            $options = $newOptions;
        }
        $this->addOptionArray($options);
    }

    /**
     * Get options for a category select with hierarchy (recursive)
     *
     * @param XoopsObjectTree $tree
     * @param string          $fieldName
     * @param int             $key
     * @param string          $prefix_curr
     * @param array           $ret
     *
     * @return array
     */
    public function getOptionArray($tree, $fieldName, $key, $prefix_curr = "", &$ret)
    {

        if ($key > 0) {
            $value = $tree->_tree[$key]['obj']->getVar($tree->_myId);
            $ret[$key] = $prefix_curr . $tree->_tree[$key]['obj']->getVar($fieldName);
            $prefix_curr .= "-";
        }
        if (isset($tree->_tree[$key]['child']) && !empty($tree->_tree[$key]['child'])) {
            foreach ($tree->_tree[$key]['child'] as $childkey) {
                $this->getOptionArray($tree, $fieldName, $childkey, $prefix_curr, $ret);
            }
        }
        return $ret;
    }
}