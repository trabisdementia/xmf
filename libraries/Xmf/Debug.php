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

class Xmf_Debug
{
    /**
     * Output a dump of a variable
     *
     * @static
     * @param $var mixed which will be dumped
     * @param bool $echo
     * @param bool $html
     * @param bool $exit
     * @return mixed|string
     */
    static public function dump($var, $echo = true, $html = true, $exit = false)
    {
        if (!$html) {
            $msg = var_export($var, true);
        } else {
            $ts = MyTextSanitizer::getInstance();
            $msg = $ts->displayTarea(var_export($var, true));
            $msg = "<div style='padding: 5px; font-weight: bold'>{$msg}</div>";
        }
        if (!$echo) {
            return $msg;
        }
        echo $msg;
        if ($exit) {
            die();
        }
        return false;
    }

    /**
     * Output a dump of a file
     *
     * @param mixed $file file which will be dumped
     * @param bool  $echo
     * @param bool  $exit
     *
     * @return string
     */
    public static function dumpFile($file, $echo = true, $exit = false)
    {
        $msg = highlight_file($file, true);
        $msg = "<div style='padding: 5px; font-weight: bold'>{$msg}</div>";
        if (!$echo) {
            return $msg;
        }
        echo $msg;
        if ($exit) {
            die();
        }
        return $msg;
    }
}