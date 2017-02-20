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
 * @version         $Id: Utils.php 10616 2012-12-31 19:02:57Z trabis $
 */
class Xmf_Utils
{

    /**
     * Support for recursive array diff
     * Needed for php 5.4.3 warning issues
     *
     * @param array $aArray1
     * @param array $aArray2
     *
     * @return array
     */
    public static function arrayRecursiveDiff(array $aArray1, array $aArray2)
    {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }

    /**
     * This function can be thought of as a hybrid between PHP's `array_merge` and `array_merge_recursive`.
     * The difference between this method and the built-in ones, is that if an array key contains another array, then
     * Xoops_Utils::arrayRecursiveMerge() will behave in a recursive fashion (unlike `array_merge`).  But it will not act recursively for
     * keys that contain scalar values (unlike `array_merge_recursive`).
     * Note: This function will work with an unlimited amount of arguments and typecasts non-array parameters into arrays.
     *
     * @param array $data  Array to be merged
     * @param mixed $merge Array to merge with. The argument and all trailing arguments will be array cast when merged
     *
     * @return array Merged array
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/hash.html#Hash::merge
     */
    public static function arrayRecursiveMerge(array $data, $merge)
    {
        $args = func_get_args();
        $return = current($args);

        while (($arg = next($args)) !== false) {
            foreach ((array)$arg as $key => $val) {
                if (!empty($return[$key]) && is_array($return[$key]) && is_array($val)) {
                    $return[$key] = self::arrayRecursiveMerge($return[$key], $val);
                } elseif (is_int($key)) {
                    $return[] = $val;
                } else {
                    $return[$key] = $val;
                }
            }
        }
        return $return;
    }

    /**
     * @return array
     */
    public static function getCurrentUrls()
    {
        $http = ((strpos(XOOPS_URL, "https://")) === false) ? ("http://") : ("https://");
        $phpself = $_SERVER['PHP_SELF'];
        $httphost = $_SERVER['HTTP_HOST'];
        $querystring = $_SERVER['QUERY_STRING'];
        if ($querystring != '') {
            $querystring = '?' . $querystring;
        }
        $currenturl = $http . $httphost . $phpself . $querystring;
        $urls = array();
        $urls['http'] = $http;
        $urls['httphost'] = $httphost;
        $urls['phpself'] = $phpself;
        $urls['querystring'] = $querystring;
        $urls['full_phpself'] = $http . $httphost . $phpself;
        $urls['full'] = $currenturl;
        $urls['isHomePage'] = (XOOPS_URL . "/index.php") == ($http . $httphost . $phpself);
        return $urls;
    }

    /**
     * @return string
     */
    public static function getCurrentPage()
    {
        $urls = self::getCurrentUrls();
        return $urls['full'];
    }

    public static function getObjectSize($obj)
    {
        $serialized = serialize($obj);
        if (function_exists('mb_strlen')) {
            $size = mb_strlen($serialized, '8bit');
        } else {
            $size = strlen($serialized);
        }
        return $size;
    }

    /**
     * Check Xoops Version against a provided version
     *
     * @param int $x
     * @param int $y
     * @param int $z
     * @param string $signal
     * @return bool
     */
    public static function checkXoopsVersion($x, $y, $z, $signal = '==')
     {
         $xv = explode('-', str_replace('XOOPS ', '', XOOPS_VERSION));

         list($a, $b, $c) = explode('.', $xv[0]);
         $xv = $a*10000 + $b*100 + $c;
         $mv = $x*10000 + $y*100 + $z;
         if ($signal == '>') return $xv > $mv;
         if ($signal == '>=') return $xv >= $mv;
         if ($signal == '<') return $xv < $mv;
         if ($signal == '<=') return $xv <= $mv;
         if ($signal == '==') return $xv == $mv;

         return false;
     }
     static public function purifyText($text, $keyword = false)
    {
        $myts = MyTextSanitizer::getInstance();
        $text = str_replace('&nbsp;', ' ', $text);
        $text = str_replace('<br />', ' ', $text);
        $text = str_replace('<br/>', ' ', $text);
        $text = str_replace('<br', ' ', $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);
        $text = $myts->undoHtmlSpecialChars($text);
        $text = str_replace(')', ' ', $text);
        $text = str_replace('(', ' ', $text);
        $text = str_replace(':', ' ', $text);
        $text = str_replace('&euro', ' euro ', $text);
        $text = str_replace('&hellip', '...', $text);
        $text = str_replace('&rsquo', ' ', $text);
        $text = str_replace('!', ' ', $text);
        $text = str_replace('?', ' ', $text);
        $text = str_replace('"', ' ', $text);
        $text = str_replace('-', ' ', $text);
        $text = str_replace('\n', ' ', $text);
        $text = str_replace('&#8213;', ' ', $text);

        if ($keyword) {
            $text = str_replace('.', ' ', $text);
            $text = str_replace(',', ' ', $text);
            $text = str_replace('\'', ' ', $text);
        }
        $text = str_replace(';', ' ', $text);

        return $text;
    }

    static public function html2text($document)
    {
        // PHP Manual:: function preg_replace
        // $document should contain an HTML document.
        // This will remove HTML tags, javascript sections
        // and white space. It will also convert some
        // common HTML entities to their text equivalent.
        // Credits : newbb2
        $search = array(
                "'<script[^>]*?>.*?</script>'si", // Strip out javascript
                "'<img.*?/>'si", // Strip out img tags
                "'<[\/\!]*?[^<>]*?>'si", // Strip out HTML tags
                "'([\r\n])[\s]+'", // Strip out white space
                "'&(quot|#34);'i", // Replace HTML entities
                "'&(amp|#38);'i",
                "'&(lt|#60);'i",
                "'&(gt|#62);'i",
                "'&(nbsp|#160);'i",
                "'&(iexcl|#161);'i",
                "'&(cent|#162);'i",
                "'&(pound|#163);'i",
                "'&(copy|#169);'i",
                "'&#(\d+);'e"
        ); // evaluate as php

        $replace = array(
                "",
                "",
                "",
                "\\1",
                "\"",
                "&",
                "<",
                ">",
                " ",
                chr(161),
                chr(162),
                chr(163),
                chr(169),
                "chr(\\1)"
        );

        $text = preg_replace($search, $replace, $document);
        return $text;
    }
}