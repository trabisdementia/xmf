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
 * @author          Joomla!
 * @version         $Id$
 */

defined('XMF_EXEC') or die('Xmf was not detected');

/**
 * Set the available masks for cleaning variables
 */
define('XMF_REQUEST_NOTRIM', 1);
define('XMF_REQUEST_ALLOWRAW', 2);
define('XMF_REQUEST_ALLOWHTML', 4);

/**
 * Xmf_Request Class
 * This class serves to provide a common interface to access
 * request variables.  This includes $_POST, $_GET, and naturally $_REQUEST.  Variables
 * can be passed through an input filter to avoid injection or returned raw.
 */
class Xmf_Request
{
    /**
     * Gets the request method
     *
     * @return string
     */
    static public function getMethod()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        return $method;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasCookie($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * @param string|null $name
     * @param string|null $default
     *
     * @return string|null
     */
    public static function getCookie($name = null, $default = null)
    {
        if ($name === null) {
            return $_COOKIE;
        }
        return self::getVar($name, $default, array(), array(), 'cookie');
    }

    /**
     * @param string|null $name
     * @param string|null $default
     *
     * @return string|null
     */
    public function getSession($name = null, $default = null)
    {
        if ($name === null) {
            return $_SESSION;
        }
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    /**
     * Gets an environment variable from available sources, and provides emulation
     * for unsupported or inconsistent environment variables (i.e. DOCUMENT_ROOT on
     * IIS, or SCRIPT_NAME in CGI mode).  Also exposes some additional custom
     * environment information.
     *
     * @param  string $name Environment variable name.
     * @param  mixed  $default
     *
     * @return string Environment variable setting.
     * @link http: //book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#env
     */
    public static function getEnv($name, $default = null)
    {
        if ($name === 'HTTPS') {
            if (isset($_SERVER['HTTPS'])) {
                return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            }
            return (strpos(self::getEnv('SCRIPT_URI'), 'https://') === 0);
        }

        if ($name === 'SCRIPT_NAME') {
            if (self::getEnv('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
                $name = 'SCRIPT_URL';
            }
        }

        $val = null;
        if (isset($_SERVER[$name])) {
            $val = $_SERVER[$name];
        } elseif (isset($_ENV[$name])) {
            $val = $_ENV[$name];
        } elseif (getenv($name) !== false) {
            $val = getenv($name);
        }

        if ($name === 'REMOTE_ADDR' && $val === self::getEnv('SERVER_ADDR')) {
            $addr = self::getEnv('HTTP_PC_REMOTE_ADDR');
            if ($addr !== null) {
                $val = $addr;
            }
        }

        if ($val !== null) {
            return $val;
        }

        switch ($name) {
            case 'SCRIPT_FILENAME':
                if (defined('SERVER_IIS') && SERVER_IIS === true) {
                    return str_replace('\\\\', '\\', self::getEnv('PATH_TRANSLATED'));
                }
                break;
            case 'DOCUMENT_ROOT':
                $name = self::getEnv('SCRIPT_NAME');
                $filename = self::getEnv('SCRIPT_FILENAME');
                $offset = 0;
                if (!strpos($name, '.php')) {
                    $offset = 4;
                }
                return substr($filename, 0, -(strlen($name) + $offset));
                break;
            case 'PHP_SELF':
                return str_replace(self::getEnv('DOCUMENT_ROOT'), '', self::getEnv('SCRIPT_FILENAME'));
                break;
            case 'CGI_MODE':
                return (PHP_SAPI === 'cgi');
                break;
            case 'HTTP_BASE':
                $host = self::getEnv('HTTP_HOST');
                $parts = explode('.', $host);
                $count = count($parts);

                if ($count === 1) {
                    return '.' . $host;
                } elseif ($count === 2) {
                    return '.' . $host;
                } elseif ($count === 3) {
                    $gTLD = array(
                            'aero', 'asia', 'biz', 'cat', 'com', 'coop', 'edu', 'gov', 'info', 'int', 'jobs', 'mil',
                            'mobi', 'museum', 'name', 'net', 'org', 'pro', 'tel', 'travel', 'xxx'
                    );
                    if (in_array($parts[1], $gTLD)) {
                        return '.' . $host;
                    }
                }
                array_shift($parts);
                return '.' . implode('.', $parts);
                break;
        }
        return $default;
    }

    /**
     * @param null|string $name
     *
     * @return null|string
     */
    public static function getHeader($name = null)
    {
        if ($name === null) {
            return $name;
        }

        // Try to get it from the $_SERVER array first
        if ($res = self::getEnv('HTTP_' . strtoupper(str_replace('-', '_', $name)))) {
            return $res;
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$name])) {
                return $headers[$name];
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public static function getScheme()
    {
        return self::getEnv('HTTPS') ? 'https' : 'http';
    }

    /**
     * @return string
     */
    public static function getHost()
    {
        return self::getEnv('HTTP_HOST') ?: 'localhost';
    }

    /**
     * @return null|string
     */
    public static function getUri()
    {
        if (empty($_SERVER['PHP_SELF']) || empty($_SERVER['REQUEST_URI'])) {
            // IIS
            $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
            }
            return $_SERVER['REQUEST_URI'];
        }
        return isset($_SERVER['ORIG_REQUEST_URI']) ? $_SERVER['ORIG_REQUEST_URI'] : $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public static function getReferer()
    {
        return self::getEnv('HTTP_REFERER') ?: '';
    }

    /**
     * @return string
     */
    public static function getScriptName()
    {
        return self::getEnv('SCRIPT_NAME') ?: (self::getEnv('ORIG_SCRIPT_NAME') ?: '');
    }

    /**
     * Get the domain name and include $tldLength segments of the tld.
     *
     * @param integer $tldLength Number of segments your tld contains. For example: `example.com` contains 1 tld.
     *                           While `example.co.uk` contains 2.
     *
     * @return string Domain name without subdomains.
     */
    public static function getDomain($tldLength = 1)
    {
        $segments = explode('.', self::getHost());
        $domain = array_slice($segments, -1 * ($tldLength + 1));
        return implode('.', $domain);
    }

    /**
     * Get the subdomains for a host.
     *
     * @param integer $tldLength Number of segments your tld contains. For example: `example.com` contains 1 tld.
     *                           While `example.co.uk` contains 2.
     *
     * @return array of subdomains.
     */
    public static function getSubdomains($tldLength = 1)
    {
        $segments = explode('.', self::getHost());
        return array_slice($segments, 0, -1 * ($tldLength + 1));
    }

    /**
     * Get the Client Ip
     *
     * @param string $default
     *
     * @return string
     */
    public static function getClientIp($default = '0.0.0.0')
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (!$res = self::getEnv($key)) {
                continue;
            }
            $ips = explode(',', $res, 1);
            $ip = $ips[0];
            if (false != ip2long($ip) && long2ip(ip2long($ip) === $ip)) {
                return $ips[0];
            }
        }
        return $default;
    }

    /**
     * Return current url
     *
     * @return string
     */
    public static function getUrl()
    {
        $url = self::getScheme() . "://" . self::getHost();
        $port = self::getEnv('SERVER_PORT');
        if (80 != $port) {
            $url .= ":{$port}";
        }
        return $url . self::getUri();
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public static function getFiles($name)
    {
        if (empty($_FILES)) {
            return array();
        }

        if (isset($_FILES[$name])) {
            return $_FILES[$name];
        }

        if (false === $pos = strpos($name, '[')) {
            return array();
        }

        $base = substr($name, 0, $pos);
        $key = str_replace(array(']', '['), array('', '"]["'), substr($name, $pos + 1, -1));
        $code = array(sprintf('if (!isset($_FILES["%s"]["name"]["%s"])) return array();', $base, $key));
        $code[] = '$file = array();';
        foreach (array('name', 'type', 'size', 'tmp_name', 'error') as $property) {
            $code[] = sprintf('$file["%1$s"] = $_FILES["%2$s"]["%1$s"]["%3$s"];', $property, $base, $key);
        }
        $code[] = 'return $file;';

        return eval(implode(PHP_EOL, $code));
    }

    /**
     * Check whether or not a Request is a certain type.
     *
     * @param string $type The type of request you want to check.
     *
     * @return boolean Whether or not the request is the type you are checking.
     */
    public static function is($type)
    {
        $detectors = array(
                'get'      => array('env' => 'REQUEST_METHOD', 'value' => 'GET'),
                'post'     => array('env' => 'REQUEST_METHOD', 'value' => 'POST'),
                'put'      => array('env' => 'REQUEST_METHOD', 'value' => 'PUT'),
                'delete'   => array('env' => 'REQUEST_METHOD', 'value' => 'DELETE'),
                'head'     => array('env' => 'REQUEST_METHOD', 'value' => 'HEAD'),
                'options'  => array('env' => 'REQUEST_METHOD', 'value' => 'OPTIONS'),
                'ssl'      => array('env' => 'HTTPS', 'value' => 1),
                'ajax'     => array('env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest'),
                'flash'    => array('env' => 'HTTP_USER_AGENT', 'pattern' => '/^(Shockwave|Adobe) Flash/'),
                'mobile'   => array(
                        'env' => 'HTTP_USER_AGENT', 'options' => array(
                                'Android', 'AvantGo', 'BlackBerry', 'DoCoMo', 'Fennec', 'iPod', 'iPhone', 'iPad',
                                'J2ME', 'MIDP', 'NetFront', 'Nokia', 'Opera Mini', 'Opera Mobi', 'PalmOS', 'PalmSource',
                                'portalmmm', 'Plucker', 'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP\\.Browser',
                                'webOS', 'Windows CE', 'Windows Phone OS', 'Xiino'
                        )
                ), 'robot' => array(
                        'env' => 'HTTP_USER_AGENT', 'options' => array(
                            /* The most common ones. */
                                'Googlebot', 'msnbot', 'Slurp', 'Yahoo', /* The rest alphabetically. */
                                'Arachnoidea', 'ArchitextSpider', 'Ask Jeeves', 'B-l-i-t-z-Bot', 'Baiduspider',
                                'BecomeBot', 'cfetch', 'ConveraCrawler', 'ExtractorPro', 'FAST-WebCrawler',
                                'FDSE robot', 'fido', 'geckobot', 'Gigabot', 'Girafabot', 'grub-client', 'Gulliver',
                                'HTTrack', 'ia_archiver', 'InfoSeek', 'kinjabot', 'KIT-Fireball', 'larbin', 'LEIA',
                                'lmspider', 'Lycos_Spider', 'Mediapartners-Google', 'MuscatFerret', 'NaverBot',
                                'OmniExplorer_Bot', 'polybot', 'Pompos', 'Scooter', 'Teoma', 'TheSuBot', 'TurnitinBot',
                                'Ultraseek', 'ViolaBot', 'webbandit', 'www\\.almaden\\.ibm\\.com\\/cs\\/crawler',
                                'ZyBorg',
                        )
                ),
        );

        $type = strtolower($type);
        if (!isset($detectors[$type])) {
            return false;
        }
        $detect = $detectors[$type];
        if (isset($detect['env'])) {
            if (isset($detect['value'])) {
                return self::getEnv($detect['env']) == $detect['value'];
            }
            if (isset($detect['pattern'])) {
                return (bool)preg_match($detect['pattern'], self::getEnv($detect['env']));
            }
            if (isset($detect['options'])) {
                $pattern = '/' . implode('|', $detect['options']) . '/i';
                return (bool)preg_match($pattern, self::getEnv($detect['env']));
            }
        }
        return false;
    }

    /**
     * Find out which content types the client accepts or check if they accept a
     * particular type of content.
     * #### Get all types:
     * `$request->accepts();`
     * #### Check for a single type:
     * `$request->accepts('application/json');`
     * This method will order the returned content types by the preference values indicated
     * by the client.
     *
     * @param string $type The content type to check for.  Leave null to get all types a client accepts.
     *
     * @return mixed Either an array of all the types the client accepts or a boolean if they accept the
     *   provided type.
     */
    public function accepts($type = null)
    {
        $raw = $this->_parseAccept();
        $accept = array();
        foreach ($raw as $types) {
            $accept = array_merge($accept, $types);
        }
        if ($type === null) {
            return $accept;
        }
        return in_array($type, $accept);
    }

    /**
     * Parse the HTTP_ACCEPT header and return a sorted array with content types
     * as the keys, and pref values as the values.
     * Generally you want to use CakeRequest::accept() to get a simple list
     * of the accepted content types.
     *
     * @return array An array of prefValue => array(content/types)
     */
    private function _parseAccept()
    {
        $accept = array();
        $header = explode(',', self::getHeader('accept'));
        foreach (array_filter($header) as $value) {
            $prefPos = strpos($value, ';');
            if ($prefPos !== false) {
                $prefValue = substr($value, strpos($value, '=') + 1);
                $value = trim(substr($value, 0, $prefPos));
            } else {
                $prefValue = '1.0';
                $value = trim($value);
            }
            if (!isset($accept[$prefValue])) {
                $accept[$prefValue] = array();
            }
            if ($prefValue) {
                $accept[$prefValue][] = $value;
            }
        }
        krsort($accept);
        return $accept;
    }

    /**
     * Fetches and returns a given variable.
     * The default behaviour is fetching variables depending on the
     * current request method: GET and HEAD will result in returning
     * an entry from $_GET, POST and PUT will result in returning an
     * entry from $_POST.
     * You can force the source by setting the $hash parameter:
     *   post       $_POST
     *   get        $_GET
     *   files      $_FILES
     *   cookie     $_COOKIE
     *   env        $_ENV
     *   server     $_SERVER
     *   method     via current $_SERVER['REQUEST_METHOD']
     *   default    $_REQUEST
     *
     * @static
     *
     * @param string $name    Variable name
     * @param string $default Default value if the variable does not exist
     * @param array  $include Value must be found in this array
     * @param array  $exclude Value must not be found in this array
     * @param string $hash    Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @param string $type    Return type for the variable, for valid values see {@link Xmf_Input_Filter::clean()}
     * @param int    $mask    Filter mask for the variable
     *
     * @return array|null|string
     */
    static public function getVar($name, $default = null, $include = array(), $exclude = array(), $hash = 'default', $type = 'none', $mask = 0)
    {
        // Ensure hash and type are uppercase
        $hash = strtoupper($hash);
        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }
        $type = strtoupper($type);
        //$sig = $hash . $type . $mask;

        // Get the input hash
        switch ($hash) {
            case 'GET' :
                $input = & $_GET;
                break;
            case 'POST' :
                $input = & $_POST;
                break;
            case 'FILES' :
                $input = & $_FILES;
                break;
            case 'COOKIE' :
                $input = & $_COOKIE;
                break;
            case 'ENV'    :
                $input = & $_ENV;
                break;
            case 'SERVER'    :
                $input = & $_SERVER;
                break;
            default:
                $input = & $_REQUEST;
                $hash = 'REQUEST';
                break;
        }

        if (isset($input[$name]) && $input[$name] !== null) {
            // Get the variable from the input hash and clean it
            $var = Xmf_Request::_cleanVar($input[$name], $mask, $type);

            // Handle magic quotes compatibility
            if (get_magic_quotes_gpc() && ($var != $default) && ($hash != 'FILES')) {
                $var = Xmf_Request::_stripSlashesRecursive($var);
            }

            if (!empty($exclude)) {
                if (in_array($var, $exclude)) {
                    $var = $default;
                }
            } elseif (!empty($include)) {
                if (!in_array($var, $include)) {
                    $var = $default;
                }
            }
        } else {
            if ($default !== null) {
                // Clean the default value
                $var = Xmf_Request::_cleanVar($default, $mask, $type);
            } else {
                $var = $default;
            }
        }
        return $var;
    }

    /**
     * Fetches and returns a given filtered variable. The integer
     * filter will allow only digits to be returned. This is currently
     * only a proxy function for getVar().
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     *
     * @param string $name    Variable name
     * @param int    $default Default value if the variable does not exist
     * @param array  $include Value must be found in this array
     * @param array  $exclude Value must not be found in this array
     * @param string $hash    Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     *
     * @return int                 Requested variable
     */
    static public function getInt($name, $default = 0, $include = array(), $exclude = array(), $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $include, $exclude, $hash, 'int');
    }

    /**
     * Fetches and returns a given filtered variable.  The float
     * filter only allows digits and periods.  This is currently
     * only a proxy function for getVar().
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     *
     * @param string $name    Variable name
     * @param float  $default Default value if the variable does not exist
     * @param array  $include Value must be found in this array
     * @param array  $exclude Value must not be found in this array
     * @param string $hash    Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     *
     * @return   float                  Requested variable
     */
    static public function getFloat($name, $default = 0.0, $include = array(), $exclude = array(), $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $include, $exclude, $hash, 'float');
    }

    /**
     * Fetches and returns a given filtered variable. The bool
     * filter will only return true/false bool values. This is
     * currently only a proxy function for getVar().
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     *
     * @param    string $name    Variable name
     * @param    bool   $default Default value if the variable does not exist
     * @param    string $hash    Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     *
     * @return   bool                   Requested variable
     */
    static function getBool($name, $default = false, $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, array(), array(),$hash, 'bool');
    }

    /**
     * Fetches and returns a given filtered variable. The word
     * filter only allows the characters [A-Za-z_]. This is currently
     * only a proxy function for getVar().
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     *
     * @param string $name    Variable name
     * @param string $default Default value if the variable does not exist
     * @param array  $include Value must be found in this array
     * @param array  $exclude Value must not be found in this array
     * @param string $hash    Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     *
     * @return   string                 Requested variable
     */
    static public function getWord($name, $default = '', $include = array(), $exclude = array(), $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $include, $exclude, $hash, 'word');
    }

    /**
     * Fetches and returns a given filtered variable. The cmd
     * filter only allows the characters [A-Za-z0-9.-_]. This is
     * currently only a proxy function for getVar().
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     *
     * @param string $name    Variable name
     * @param string $default Default value if the variable does not exist
     * @param array  $include Value must be found in this array
     * @param array  $exclude Value must not be found in this array
     * @param string $hash    Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     *
     * @return   string                 Requested variable
     */
    static public function getCmd($name, $default = '', $include = array(), $exclude = array(), $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $include, $exclude, $hash, 'cmd');
    }

    /**
     * Fetches and returns a given filtered variable. The string
     * filter deletes 'bad' HTML code, if not overridden by the mask.
     * This is currently only a proxy function for getVar().
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     *
     * @param string $name    Variable name
     * @param string $default Default value if the variable does not exist
     * @param array  $include Value must be found in this array
     * @param array  $exclude Value must not be found in this array
     * @param string $hash    Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @param int    $mask    Filter mask for the variable
     *
     * @return    string    Requested variable
     */
    static function getString($name, $default = '', $include = array(), $exclude = array(), $hash = 'default', $mask = 0)
    {
        // Cast to string, in case XMF_REQUEST_ALLOWRAW was specified for mask
        return (string)Xmf_Request::getVar($name, $default, $include, $exclude, $hash, 'string', $mask);
    }

    /**
     * @static
     *
     * @param string $name
     * @param array  $default
     * @param string $hash
     *
     * @return array
     */
    static public function getArray($name, $default = array(),$hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, array(), array(), $hash, 'array');
    }

    /**
     * @static
     *
     * @param string $name
     * @param string $default
     * @param array  $include Value must be found in this array
     * @param array  $exclude Value must not be found in this array
     * @param string $hash
     *
     * @return string
     */
    static function getText($name, $default = '', $include = array(), $exclude = array(), $hash = 'default')
    {
        return (string)Xmf_Request::getVar($name, $default, $include, $exclude, $hash, 'string', XMF_REQUEST_ALLOWRAW);
    }

    /**
     * Set a variable in on of the request variables
     *
     * @access    public
     *
     * @param     string  $name      Name
     * @param     string  $value     Value
     * @param     string  $hash      Hash
     * @param     boolean $overwrite Boolean
     *
     * @return    string                  Previous value
     */
    static public function setVar($name, $value = null, $hash = 'method', $overwrite = true)
    {
        //If overwrite is true, makes sure the variable hasn't been set yet
        if (!$overwrite && array_key_exists($name, $_REQUEST)) {
            return $_REQUEST[$name];
        }

        // Get the request hash value
        $hash = strtoupper($hash);
        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }

        $previous = array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : null;

        switch ($hash) {
            case 'GET' :
                $_GET[$name] = $value;
                $_REQUEST[$name] = $value;
                break;
            case 'POST' :
                $_POST[$name] = $value;
                $_REQUEST[$name] = $value;
                break;
            case 'COOKIE' :
                $_COOKIE[$name] = $value;
                $_REQUEST[$name] = $value;
                break;
            case 'FILES' :
                $_FILES[$name] = $value;
                break;
            case 'ENV'    :
                $_ENV['name'] = $value;
                break;
            case 'SERVER'    :
                $_SERVER['name'] = $value;
                break;
        }

        return $previous;
    }

    /**
     * Fetches and returns a request array.
     * The default behaviour is fetching variables depending on the
     * current request method: GET and HEAD will result in returning
     * $_GET, POST and PUT will result in returning $_POST.
     * You can force the source by setting the $hash parameter:
     *   post        $_POST
     *   get         $_GET
     *   files       $_FILES
     *   cookie      $_COOKIE
     *   env         $_ENV
     *   server      $_SERVER
     *   method      via current $_SERVER['REQUEST_METHOD']
     *   default     $_REQUEST
     *
     * @static
     *
     * @param    string $hash to get (POST, GET, FILES, METHOD)
     * @param    int    $mask Filter mask for the variable
     *
     * @return   mixed              Request hash
     */
    static public function get($hash = 'default', $mask = 0)
    {
        $hash = strtoupper($hash);

        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }

        switch ($hash) {
            case 'GET' :
                $input = $_GET;
                break;

            case 'POST' :
                $input = $_POST;
                break;

            case 'FILES' :
                $input = $_FILES;
                break;

            case 'COOKIE' :
                $input = $_COOKIE;
                break;

            case 'ENV'    :
                $input = & $_ENV;
                break;

            case 'SERVER'    :
                $input = & $_SERVER;
                break;

            default:
                $input = $_REQUEST;
                break;
        }

        $result = Xmf_Request::_cleanVar($input, $mask);

        // Handle magic quotes compatibility
        if (get_magic_quotes_gpc() && ($hash != 'FILES')) {
            $result = Xmf_Request::_stripSlashesRecursive($result);
        }

        return $result;
    }

    /**
     * Sets a request variable
     *
     * @param    array   $array     An associative array of key-value pairs
     * @param    string  $hash      The request variable to set (POST, GET, FILES, METHOD)
     * @param    boolean $overwrite If true and an existing key is found, the value is overwritten, otherwise it is ingored
     */
    static public function set($array, $hash = 'default', $overwrite = true)
    {
        foreach ($array as $key => $value) {
            Xmf_Request::setVar($key, $value, $hash, $overwrite);
        }
    }

    /**
     * Cleans the request from script injection.
     *
     * @static
     * @return    void
     */
    static public function clean()
    {
        Xmf_Request::_cleanArray($_FILES);
        Xmf_Request::_cleanArray($_ENV);
        Xmf_Request::_cleanArray($_GET);
        Xmf_Request::_cleanArray($_POST);
        Xmf_Request::_cleanArray($_COOKIE);
        Xmf_Request::_cleanArray($_SERVER);

        if (isset($_SESSION)) {
            Xmf_Request::_cleanArray($_SESSION);
        }

        $REQUEST = $_REQUEST;
        $GET = $_GET;
        $POST = $_POST;
        $COOKIE = $_COOKIE;
        $FILES = $_FILES;
        $ENV = $_ENV;
        $SERVER = $_SERVER;

        if (isset ($_SESSION)) {
            $SESSION = $_SESSION;
        }

        foreach ($GLOBALS as $key => $value) {
            if ($key != 'GLOBALS') {
                unset($GLOBALS[$key]);
            }
        }
        $_REQUEST = $REQUEST;
        $_GET = $GET;
        $_POST = $POST;
        $_COOKIE = $COOKIE;
        $_FILES = $FILES;
        $_ENV = $ENV;
        $_SERVER = $SERVER;

        if (isset($SESSION)) {
            $_SESSION = $SESSION;
        }
    }

    /**
     * Adds an array to the GLOBALS array and checks that the GLOBALS variable is not being attacked
     *
     * @access    protected
     *
     * @param    array   $array     Array to clean
     * @param    boolean $globalise True if the array is to be added to the GLOBALS
     */
    static protected function _cleanArray(&$array, $globalise = false)
    {
        static $banned = array('_files', '_env', '_get', '_post', '_cookie', '_server', '_session', 'globals');

        foreach ($array as $key => $value) {
            // PHP GLOBALS injection bug
            $failed = in_array(strtolower($key), $banned);

            // PHP Zend_Hash_Del_Key_Or_Index bug
            $failed |= is_numeric($key);
            if ($failed) {
                exit('Illegal variable <b>' . implode("</b> or <b>", $banned) . '</b> passed to script.');
            }
            if ($globalise) {
                $GLOBALS[$key] = $value;
            }
        }
    }

    /**
     * Clean up an input variable.
     *
     * @param mixed  $var  The input variable.
     * @param int    $mask Filter bit mask. 1=no trim: If this flag is cleared and the
     *                     input is a string, the string will have leading and trailing whitespace
     *                     trimmed. 2=allow_raw: If set, no more filtering is performed, higher bits
     *                     are ignored. 4=allow_html: HTML is allowed, but passed through a safe
     *                     HTML filter first. If set, no more filtering is performed. If no bits
     *                     other than the 1 bit is set, a strict filter is applied.
     * @param string $type The variable type {@see Xmf_Input_Filter::clean()}.
     *
     * @return string
     */
    static protected function _cleanVar($var, $mask = 0, $type = null)
    {
        // Static input filters for specific settings
        static $noHtmlFilter = null;
        static $safeHtmlFilter = null;

        // If the no trim flag is not set, trim the variable
        if (!($mask & 1) && is_string($var)) {
            $var = trim($var);
        }

        // Now we handle input filtering
        if ($mask & 2) {
            // If the allow raw flag is set, do not modify the variable
        } else {
            if ($mask & 4) {
                // If the allow html flag is set, apply a safe html filter to the variable
                if (is_null($safeHtmlFilter)) {
                    $safeHtmlFilter = Xmf_Filter_Input::getInstance(null, null, 1, 1);
                }
                $var = $safeHtmlFilter->clean($var, $type);
            } else {
                // Since no allow flags were set, we will apply the most strict filter to the variable
                if (is_null($noHtmlFilter)) {
                    $noHtmlFilter = Xmf_Filter_Input::getInstance( /* $tags, $attr, $tag_method, $attr_method, $xss_auto */);
                }
                $var = $noHtmlFilter->clean($var, $type);
            }
        }
        return $var;
    }

    /**
     * Strips slashes recursively on an array
     *
     * @access    protected
     *
     * @param     array $value Array of (nested arrays of) strings
     *
     * @return    array                  The input array with stripshlashes applied to it
     */
    static protected function _stripSlashesRecursive($value)
    {
        $value = is_array($value) ? array_map(array(
                'XMF_REQUEST', '_stripSlashesRecursive'
        ), $value) : stripslashes($value);
        return $value;
    }
}
