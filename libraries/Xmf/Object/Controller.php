<?php

/**
 * Contains the basis classes for managing any objects derived from SmartObjects
 *
 * @license    GNU
 * @author     marcan <marcan@smartfactory.ca>
 * @version    $Id: smartobjectcontroller.php 2357 2008-05-22 19:01:44Z fx2024 $
 * @link       http://smartfactory.ca The SmartFactory
 * @package    SmartObject
 * @subpackage SmartObjectCore
 */

if (!defined("XOOPS_ROOT_PATH")) {
    die("XOOPS root path not defined");
}

/**
 * Persistable SmartObject Controller class.
 * This class is responsible for providing operations to an object
 * for managing the object's manipulation
 *
 * @package SmartObject
 * @author  marcan <marcan@smartfactory.ca>
 * @credit  Jan Keller Pedersen <mithrandir@xoops.org> - IDG Danmark A/S <www.idg.dk>
 * @link    http://smartfactory.ca The SmartFactory
 */

class Xmf_Object_Controller
{
    /**
     * @var Xmf_Object_Decorator_Form
     */
    protected $_decorator;

    public function __construct(Xmf_Object_Decorator_Form $decorator)
    {
        $this->_decorator = $decorator;
        $this->_itemName = $this->_decorator->getHandlerName();
        $this->_pageName = $this->_decorator->getHandlerName() . '.php';
        $this->_url = $this->_decorator->getHelper()->url();
        $this->_identifierName = $this->_decorator->getHandler()->identifierName;
        $this->_keyName = $this->_decorator->getHandler()->keyName;
        $this->_moduleName = $this->_decorator->getDirname();
    }

    function postDataToObject(&$smartObj)
    {
        foreach (array_keys($smartObj->vars) as $key) {
            switch ($smartObj->vars[$key]['data_type']) {
                case XOBJ_DTYPE_IMAGE:
                    if (isset($_POST['url_' . $key]) && $_POST['url_' . $key] != '') {
                        $oldFile = $smartObj->getUploadDir(true) . $smartObj->getVar($key, 'e');
                        $smartObj->setVar($key, $_POST['url_' . $key]);
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    if (isset($_POST['delete_' . $key]) && $_POST['delete_' . $key] == '1') {
                        $oldFile = $smartObj->getUploadDir(true) . $smartObj->getVar($key, 'e');
                        $smartObj->setVar($key, '');
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    break;

                case XOBJ_DTYPE_URLLINK:
                    $linkObj = $smartObj->getUrlLinkObj($key);
                    $linkObj->setVar('caption', $_POST['caption_' . $key]);
                    $linkObj->setVar('description', $_POST['desc_' . $key]);
                    $linkObj->setVar('target', $_POST['target_' . $key]);
                    $linkObj->setVar('url', $_POST['url_' . $key]);
                    if ($linkObj->getVar('url') != '') {
                        $smartObj->storeUrlLinkObj($linkObj);
                    }
                    //todo: catch errors
                    $smartObj->setVar($key, $linkObj->getVar('urllinkid'));
                    break;

                case XOBJ_DTYPE_FILE:
                    if (!isset($_FILES['upload_' . $key]['name']) || $_FILES['upload_' . $key]['name'] == '') {
                        $fileObj = $smartObj->getFileObj($key);
                        $fileObj->setVar('caption', $_POST['caption_' . $key]);
                        $fileObj->setVar('description', $_POST['desc_' . $key]);
                        $fileObj->setVar('url', $_POST['url_' . $key]);
                        if (!($fileObj->getVar('url') == '' && $fileObj->getVar('url') == '' && $fileObj->getVar('url') == '')) {
                            $res = $smartObj->storeFileObj($fileObj);
                            if ($res) {
                                $smartObj->setVar($key, $fileObj->getVar('fileid'));
                            } else {
                                //error setted, but no error message (to be improved)
                                $smartObj->setErrors($fileObj->getErrors());
                            }
                        }
                    }
                    break;

                case XOBJ_DTYPE_STIME:
                case XOBJ_DTYPE_MTIME:
                case XOBJ_DTYPE_LTIME:
                    // check if this field's value is available in the POST array
                    if (is_array($_POST[$key]) && isset($_POST[$key]['date'])) {
                        $value = strtotime($_POST[$key]['date']) + $_POST[$key]['time'];
                    } else {
                        $value = strtotime($_POST[$key]);
                        //if strtotime returns false, the value is already a time stamp
                        if (!$value) {
                            $value = intval($_POST[$key]);
                        }
                    }
                    $smartObj->setVar($key, $value);

                    break;

                default:
                    $smartObj->setVar($key, $_POST[$key]);
                    break;
            }
        }
    }

    function &doStoreFromDefaultForm(&$smartObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page = false, $debug = false)
    {
        global $smart_previous_page;

        $this->postDataToObject($smartObj);

        if ($smartObj->isNew()) {
            $redirect_msg = $created_success_msg;
        } else {
            $redirect_msg = $modified_success_msg;
        }

        // Check if there were uploaded files
        if (isset($_POST['smart_upload_image']) || isset($_POST['smart_upload_file'])) {
            include_once XOOPS_ROOT_PATH . "/modules/smartobject/class/smartuploader.php";
            $uploaderObj = new SmartUploader($smartObj->getImageDir(true), $this->handler->_allowedMimeTypes, $this->handler->_maxFileSize, $this->handler->_maxWidth, $this->handler->_maxHeight);
            foreach ($_FILES as $name => $file_array) {
                if (isset ($file_array['name']) && $file_array['name'] != "" && in_array(str_replace('upload_', '', $name), array_keys($smartObj->vars))) {
                    if ($uploaderObj->fetchMedia($name)) {
                        $uploaderObj->setTargetFileName(time() . "_" . $uploaderObj->getMediaName());
                        if ($uploaderObj->upload()) {
                            // Find the related field in the SmartObject
                            $related_field = str_replace('upload_', '', $name);
                            $uploadedArray[] = $related_field;
                            //si c'est un fichier Rich
                            if ($smartObj->vars[$related_field]['data_type'] == XOBJ_DTYPE_FILE) {
                                $object_fileurl = $smartObj->getUploadDir();
                                $fileObj = $smartObj->getFileObj($related_field);
                                $fileObj->setVar('url', $object_fileurl . $uploaderObj->getSavedFileName());
                                $fileObj->setVar('caption', $_POST['caption_' . $related_field]);
                                $fileObj->setVar('description', $_POST['desc_' . $related_field]);
                                $smartObj->storeFileObj($fileObj);
                                //todo : catch errors
                                $smartObj->setVar($related_field, $fileObj->getVar('fileid'));
                            } else {
                                $old_file = $smartObj->getUploadDir(true) . $smartObj->getVar($related_field);
                                unlink($old_file);
                                $smartObj->setVar($related_field, $uploaderObj->getSavedFileName());
                            }
                        } else {
                            $smartObj->setErrors($uploaderObj->getErrors(false));
                        }
                    } else {
                        $smartObj->setErrors($uploaderObj->getErrors(false));
                    }
                }
            }
        }

        if ($debug) {
            $storeResult = $this->handler->insertD($smartObj);
        } else {
            $storeResult = $this->handler->insert($smartObj);
        }

        if ($storeResult) {
            if ($this->handler->getPermissions()) {
                $smartpermissions_handler = new SmartobjectPermissionHandler($this->handler);
                $smartpermissions_handler->storeAllPermissionsForId($smartObj->id());
            }
        }

        if ($redirect_page === null) {
            return $smartObj;
        } else {
            if (!$storeResult) {
                redirect_header($smart_previous_page, 3, _OBJ_XMF_SAVE_ERROR . $smartObj->getHtmlErrors());
            }

            $redirect_page = $redirect_page ? $redirect_page : smart_get_page_before_form();

            redirect_header($redirect_page, 2, $redirect_msg);
        }
    }

    /**
     * Store the object in the database autmatically from a form sending POST data
     *
     * @param string $created_success_msg  message to display if new object was created
     * @param string $modified_success_msg message to display if object was successfully edited
     * @param string $created_redir_page   redirect page after creating the object
     * @param string $modified_redir_page  redirect page after editing the object
     * @param string $redirect_page        redirect page, if not set, then we backup once
     * @param bool   $exit                 if set to TRUE then the script ends
     *
     * @return bool
     */
    function &storeFromDefaultForm($created_success_msg, $modified_success_msg, $redirect_page = false, $debug = false, $x_param = false)
    {
        $objectid = (isset($_POST[$this->_keyName])) ? intval($_POST[$this->_keyName]) : 0;
        if ($debug) {
            if ($x_param) {
                $smartObj = $this->handler->getD($objectid, true, $x_param);
            } else {
                $smartObj = $this->handler->getD($objectid);
            }
        } else {
            if ($x_param) {
                $smartObj = $this->handler->get($objectid, true, false, false, $x_param);
            } else {
                $smartObj = $this->handler->get($objectid);
            }
        }

        // if handler is the Multilanguage handler, we will need to treat this for multilanguage
        if (is_subclass_of($this->handler, 'smartpersistablemlobjecthandler')) {

            if ($smartObj->isNew()) {
                // This is a new object. We need to store the meta data and then the language data
                // First, we will get rid of the multilanguage data to only store the meta data
                $smartObj->stripMultilanguageFields();
                $newObject =& $this->doStoreFromDefaultForm($smartObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page, $debug);
                /**
                 * @todo we need to trap potential errors here
                 */

                // ok, the meta daa is stored. Let's recreate the object and then
                // get rid of anything not multilanguage
                unset($smartObj);
                $smartObj = $this->handler->get($objectid);
                $smartObj->stripNonMultilanguageFields();

                $smartObj->setVar($this->_keyName, $newObject->getVar($this->_keyName));
                $this->handler->changeTableNameForML();
                $ret =& $this->doStoreFromDefaultForm($smartObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page, $debug);

                return $ret;
            }
        } else {
            return $this->doStoreFromDefaultForm($smartObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page, $debug);
        }
    }

    function &storeSmartObjectD()
    {
        return $this->storeSmartObject(true);
    }

    function &storeSmartObject($debug = false, $xparam = false)
    {
        $ret =& $this->storeFromDefaultForm('', '', null, $debug, $xparam);

        return $ret;
    }

    /**
     * Handles deletion of an object which keyid is passed as a GET param
     *
     * @param string $redir_page redirect page after deleting the object
     *
     * @return bool
     */
    function handleObjectDeletion($confirm_msg = false, $op = 'del', $userSide = false)
    {
        global $smart_previous_page;

        $objectid = (isset($_REQUEST[$this->_keyName])) ? intval($_REQUEST[$this->_keyName]) : 0;
        $smartObj = $this->handler->get($objectid);

        if ($smartObj->isNew()) {
            redirect_header("javascript:history.go(-1)", 3, _OBJ_XMF_NOT_SELECTED);
            exit();
        }

        $confirm = (isset($_POST['confirm'])) ? $_POST['confirm'] : 0;
        if ($confirm) {
            if (!$this->handler->delete($smartObj)) {
                redirect_header($_POST['redirect_page'], 3, _OBJ_XMF_DELETE_ERROR . $smartObj->getHtmlErrors());
                exit;
            }

            redirect_header($_POST['redirect_page'], 3, _OBJ_XMF_DELETE_SUCCESS);
            exit();
        } else {
            // no confirm: show deletion condition

            xoops_cp_header();

            if (!$confirm_msg) {
                $confirm_msg = _OBJ_XMF_DELETE_CONFIRM;
            }

            xoops_confirm(array('op'                    => $op,
                                $this->_keyName => $smartObj->getVar($this->_keyName),
                                'confirm'               => 1,
                                'redirect_page'         => $smart_previous_page
            ), xoops_getenv('PHP_SELF'), sprintf($confirm_msg, $smartObj->getVar($this->_identifierName)), _OBJ_XMF_DELETE);

            xoops_cp_footer();
        }
        exit();
    }

    function handleObjectDeletionFromUserSide($confirm_msg = false, $op = 'del')
    {
        global $smart_previous_page, $xoopsTpl;

        $objectid = (isset($_REQUEST[$this->_keyName])) ? intval($_REQUEST[$this->_keyName]) : 0;
        $smartObj = $this->handler->get($objectid);

        if ($smartObj->isNew()) {
            redirect_header("javascript:history.go(-1)", 3, _OBJ_XMF_NOT_SELECTED);
            exit();
        }

        $confirm = (isset($_POST['confirm'])) ? $_POST['confirm'] : 0;
        if ($confirm) {
            if (!$this->handler->delete($smartObj)) {
                redirect_header($_POST['redirect_page'], 3, _OBJ_XMF_DELETE_ERROR . $smartObj->getHtmlErrors());
                exit;
            }

            redirect_header($_POST['redirect_page'], 3, _OBJ_XMF_DELETE_SUCCESS);
            exit();
        } else {
            // no confirm: show deletion condition
            if (!$confirm_msg) {
                $confirm_msg = _OBJ_XMF_DELETE_CONFIRM;
            }

            ob_start();
            xoops_confirm(array('op'                    => $op,
                                $this->_keyName => $smartObj->getVar($this->_keyName),
                                'confirm'               => 1,
                                'redirect_page'         => $smart_previous_page
            ), xoops_getenv('PHP_SELF'), sprintf($confirm_msg, $smartObj->getVar($this->_identifierName)), _OBJ_XMF_DELETE);
            $smartobject_delete_confirm = ob_get_clean();
            $xoopsTpl->assign('smartobject_delete_confirm', $smartobject_delete_confirm);
        }
    }

    /**
     * Retreive the object admin side link for a {@link SmartObjectSingleView} page
     *
     * @param object $smartObj reference to the object from which we want the user side link
     * @param bool   $onlyUrl  wether or not to return a simple URL or a full <a> link
     *
     * @return string admin side link to the object
     */
    function getAdminViewItemLink($smartObj, $onlyUrl = false, $withimage = false)
    {
        $ret = $this->_url . "admin/" . $this->_pageName . "?op=view&" . $this->_keyName . "=" . $smartObj->getVar($this->_keyName);
        if ($onlyUrl) {
            return $ret;
        } elseif ($withimage) {
            return "<a href='" . $ret . "'><img src='" . SMARTOBJECT_IMAGES_ACTIONS_URL . "viewmag.png' style='vertical-align: middle;' alt='" . _OBJ_XMF_ADMIN_VIEW . "'  title='" . _OBJ_XMF_ADMIN_VIEW . "'/></a>";
        }

        return "<a href='" . $ret . "'>" . $smartObj->getVar($this->_identifierName) . "</a>";
    }

    /**
     * Retrieve the object user side link
     *
     * @param XoopsObject $smartObj reference to the object from which we want the user side link
     * @param bool   $onlyUrl  wether or not to return a simple URL or a full <a> link
     *
     * @return string user side link to the object
     */
    function getItemLink(XoopsObject $smartObj, $onlyUrl = false)
    {
        //$seoMode = smart_getModuleModeSEO($this->_moduleName);
        //$seoModuleName = smart_getModuleNameForSEO($this->_moduleName);

        $seoMode = '';
        $seoModuleName = $this->_moduleName;

        /**
         * $seoIncludeId feature is not finished yet, so let's put it always to true
         */
        //$seoIncludeId = smart_getModuleIncludeIdSEO($this->_moduleName);
        $seoIncludeId = true;

        if ($seoMode == 'rewrite') {
            $ret = XOOPS_URL . '/' . $seoModuleName . '.' . $this->_itemName . ($seoIncludeId ? '.' . $smartObj->getVar($this->_keyName) : '') . '/' . $smartObj->getVar('short_url') . '.html';
        } else {
            if ($seoMode == 'pathinfo') {
                $ret = SMARTOBJECT_URL . 'seo.php/' . $seoModuleName . '.' . $this->_itemName . ($seoIncludeId ? '.' . $smartObj->getVar($this->_keyName) : '') . '/' . $smartObj->getVar('short_url') . '.html';
            } else {
                $ret = $this->_url . $this->_pageName . "?" . $this->_keyName . "=" . $smartObj->getVar($this->_keyName);
            }
        }

        if (!$onlyUrl) {
            $ret = "<a href='" . $ret . "'>" . $smartObj->getVar($this->_identifierName) . "</a>";
        }
        return $ret;
    }

    function getEditLanguageLink(XoopsObject $smartObj, $onlyUrl = false, $withimage = true)
    {
        $ret = $this->_url . "admin/" . $this->_pageName . "?op=mod&" . $this->_keyName . "=" . $smartObj->getVar($this->_keyName) . "&language=" . $smartObj->getVar('language');
        if ($onlyUrl) {
            return $ret;
        } elseif ($withimage) {
            return "<a href='" . $ret . "'><img src='" . SMARTOBJECT_IMAGES_ACTIONS_URL . "wizard.png' style='vertical-align: middle;' alt='" . _OBJ_XMF_LANGUAGE_MODIFY . "'  title='" . _OBJ_XMF_LANGUAGE_MODIFY . "'/></a>";
        }

        return "<a href='" . $ret . "'>" . $smartObj->getVar($this->_identifierName) . "</a>";
    }


    function getEditItemLink(XoopsObject $smartObj, $onlyUrl = false, $withimage = true, $userSide = false)
    {
        $admin_side = $userSide ? '' : 'admin/';
        $ret = $this->_url . $admin_side . $this->_pageName . "?op=mod&" . $this->_keyName . "=" . $smartObj->getVar($this->_keyName);
        if ($onlyUrl) {
            return $ret;
        } elseif ($withimage) {
            return "<a href='" . $ret . "'><img src='" . SMARTOBJECT_IMAGES_ACTIONS_URL . "edit.png' style='vertical-align: middle;' alt='" . _OBJ_XMF_MODIFY . "'  title='" . _OBJ_XMF_MODIFY . "'/></a>";
        }

        return "<a href='" . $ret . "'>" . $smartObj->getVar($this->_identifierName) . "</a>";
    }

    /**
     * @param XoopsObject $smartObj
     * @param bool $onlyUrl
     * @param bool $withimage
     * @param bool $userSide
     *
     * @return string
     */
    function getDeleteItemLink(XoopsObject $smartObj, $onlyUrl = false, $withimage = true, $userSide = false)
    {
        $admin_side = $userSide ? '' : 'admin/';
        $ret = $this->_url . $admin_side . $this->_pageName . "?op=del&" . $this->_keyName . "=" . $smartObj->getVar($this->_keyName);
        if ($onlyUrl) {
            return $ret;
        } elseif ($withimage) {
            return "<a href='" . $ret . "'><img src='" . SMARTOBJECT_IMAGES_ACTIONS_URL . "editdelete.png' style='vertical-align: middle;' alt='" . _OBJ_XMF_DELETE . "'  title='" . _OBJ_XMF_DELETE . "'/></a>";
        }

        return "<a href='" . $ret . "'>" . $smartObj->getVar($this->_identifierName) . "</a>";
    }

    function getPrintAndMailLink($smartObj)
    {
        global $xoopsConfig;

        $printlink = $this->_url . "print.php?" . $this->_keyName . "=" . $smartObj->getVar($this->_keyName);
        $js = "javascript:openWithSelfMain('" . $printlink . "', 'smartpopup', 700, 519);";
        $printlink = '<a href="' . $js . '"><img  src="' . SMARTOBJECT_IMAGES_ACTIONS_URL . 'fileprint.png" alt="" style="vertical-align: middle;"/></a>';

        $smartModule = smart_getModuleInfo($smartObj->_moduleName);
        $link = smart_getCurrentPage();
        $mid = $smartModule->getVar('mid');
        $friendlink = "<a href=\"javascript:openWithSelfMain('" . SMARTOBJECT_URL . "sendlink.php?link=" . $link . "&amp;mid=" . $mid . "', ',',',',',','sendmessage', 674, 500);\"><img src=\"" . SMARTOBJECT_IMAGES_ACTIONS_URL . "mail_send.png\"  alt=\"" . _OBJ_XMF_EMAIL . "\" title=\"" . _OBJ_XMF_EMAIL . "\" style=\"vertical-align: middle;\"/></a>";

        $ret = '<span id="smartobject_print_button">' . $printlink . "&nbsp;</span>" . '<span id="smartobject_mail_button">' . $friendlink . '</span>';
        return $ret;
    }

    function getModuleItemString()
    {
        $ret = $this->_moduleName . '_' . $this->handler->_itemname;
        return $ret;
    }
}