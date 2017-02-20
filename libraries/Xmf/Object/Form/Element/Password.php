<?php
/**
 * Contains the set password tray class
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartformset_passwordelement.php 159 2007-12-17 16:44:05Z malanciault $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectForm
 */

class Xmf_Object_Form_Element_Password extends XoopsFormElementTray
{
    /**
     * Size of the field.
     *
     * @var    int
     */
    protected $_size;

    /**
     * Maximum length of the text
     *
     * @var    int
     */
    protected $_maxlength;

    /**
     * Initial content of the field.
     *
     * @var    string
     */
    protected $_value;

    /**
     * @param Xmf_Object_Decorator_Form $decorator
     * @param string                    $key
     */
    public function __construct(Xmf_Object_Decorator_Form $decorator, $key)
    {
        $object = $decorator->getObject();
        $var = $object->vars[$key];

        parent::__construct($var['form_caption'] . '<br />' . _US_TYPEPASSTWICE, ' ', $key . '_password_tray');
        $password_box1 = new XoopsFormPassword('', $key . '1', 10, 32);
        $this->addElement($password_box1);

        $password_box2 = new XoopsFormPassword('', $key . '2', 10, 32);
        $this->addElement($password_box2);
    }
}