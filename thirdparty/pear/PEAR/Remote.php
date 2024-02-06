<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stig Bakken <ssb@php.net>                                    |
// +----------------------------------------------------------------------+
//
// $Id: Remote.php,v 1.39 2003/03/18 12:06:06 ssb Exp $

require_once 'PEAR.php';
require_once 'PEAR/Config.php';

/**
 * This is a class for doing remote operations against the central
 * PEAR database.
 *
 * @nodep XML_RPC_Value
 * @nodep XML_RPC_Message
 * @nodep XML_RPC_Client
 */
class PEAR_Remote extends PEAR
{
    // {{{ properties

    var $config = null;
    var $cache  = null;

    // }}}

    // {{{ PEAR_Remote(config_object)

    function __construct(&$config)
    {
        $this->PEAR();
        $this->config = &$config;
    }

    // }}}

    // {{{ getCache()

    
    function getCache($args)
    {
        $id       = $this->encryptOld(serialize($args));
        $cachedir = $this->config->get('cache_dir');
        if (!file_exists($cachedir)) {
            PearSystem::mkdir('-p '.$cachedir);
        }
        $filename = $cachedir . DIRECTORY_SEPARATOR . 'xmlrpc_cache_' . $id;
        if (!file_exists($filename)) {
            return null;
        };
		
        $fp = fopen($filename, "rb");
        if ($fp === null) {
            return null;
        }
        $content  = fread($fp, filesize($filename));
        fclose($fp);
        $result   = array(
            'age'        => time() - filemtime($filename),
            'lastChange' => filemtime($filename),
            'content'    => unserialize($content),
            );
        return $result;
    }

    // }}}
    
    // {{{ saveCache()

    function saveCache($args, $data)
    {
        $id       = $this->encryptOld(serialize($args));
        $cachedir = $this->config->get('cache_dir');
        if (!file_exists($cachedir)) {
            PearSystem::mkdir('-p '.$cachedir);
        }
        $filename = $cachedir.'/xmlrpc_cache_'.$id;
        
        $fp = @fopen($filename, "wb");
        if ($fp !== null) {
            fwrite($fp, serialize($data));
            fclose($fp);
        };
    }

    // }}}
    
    // {{{ call(method, [args...])

    function call($method)
    {
        $_args = $args = func_get_args();
        
        $this->cache = $this->getCache($args);
        $cachettl = $this->config->get('cache_ttl');
        // If cache is newer than $cachettl seconds, we use the cache!
        if ($this->cache !== null && $this->cache['age'] < $cachettl) {
            return $this->cache['content'];
        };

        if (!@include_once("XML/RPC.php")) {
            return $this->raiseError("For this remote PEAR operation you need to install the XML_RPC package");
        }
        array_shift($args);
        $server_host = $this->config->get('master_server');
        $username = $this->config->get('username');
        $password = $this->config->get('password');
        $eargs = array();
        foreach($args as $arg) $eargs[] = $this->_encode($arg);
        $f = new XML_RPC_Message($method, $eargs);
        if ($this->cache !== null) {
            $maxAge = '?maxAge='.$this->cache['lastChange'];
        } else {
            $maxAge = '';
        };
        $proxy_host = $proxy_port = $proxy_user = $proxy_pass = '';
        if ($proxy = parse_url($this->config->get('http_proxy'))) {
            $proxy_host = @$proxy['host'];
            $proxy_port = @$proxy['port'];
            $proxy_user = @$proxy['user'];
            $proxy_pass = @$proxy['pass'];
        }        
        $c = new XML_RPC_Client('/xmlrpc.php'.$maxAge, $server_host, 80, $proxy_host, $proxy_port, $proxy_user, $proxy_pass);
        if ($username && $password) {
            $c->setCredentials($username, $password);
        }
        if ($this->config->get('verbose') >= 3) {
            $c->setDebug(1);
        }
        $r = $c->send($f);
        if (!$r) {
            return $this->raiseError("XML_RPC send failed");
        }
        $v = $r->value();
        if ($e = $r->faultCode()) {
            if ($e == $GLOBALS['XML_RPC_err']['http_error'] && strstr($r->faultString(), '304 Not Modified') !== false) {
                return $this->cache['content'];
            }
            return $this->raiseError($r->faultString(), $e);
        }

        $result = XML_RPC_decode($v);
        $this->saveCache($_args, $result);
        return $result;
    }

    // }}}

    // {{{ _encode

    // a slightly extended version of XML_RPC_encode
    function _encode($php_val)
    {
        global $XML_RPC_Boolean, $XML_RPC_Int, $XML_RPC_Double;
        global $XML_RPC_String, $XML_RPC_Array, $XML_RPC_Struct;

        $type = gettype($php_val);
        $xmlrpcval = new XML_RPC_Value;

        switch($type) {
            case "array":
                reset($php_val);
                $firstkey = key($php_val);
                end($php_val);
                $lastkey = key($php_val);
                if ($firstkey === 0 && is_int($lastkey) &&
                    ($lastkey + 1) == count($php_val)) {
                    $is_continuous = true;
                    reset($php_val);
                    $size = count($php_val);
                    for ($expect = 0; $expect < $size; $expect++, next($php_val)) {
                        if (key($php_val) !== $expect) {
                            $is_continuous = false;
                            break;
                        }
                    }
                    if ($is_continuous) {
                        reset($php_val);
                        $arr = array();
                        foreach ($php_val as $k => $v) {
                            $arr[$k] = $this->_encode($v);
                        }
                        $xmlrpcval->addArray($arr);
                        break;
                    }
                }
                // fall though if not numerical and continuous
            case "object":
                $arr = array();
                foreach ($php_val as $k => $v) {
                    $arr[$k] = $this->_encode($v);
                }
                $xmlrpcval->addStruct($arr);
                break;
            case "integer":
                $xmlrpcval->addScalar($php_val, $XML_RPC_Int);
                break;
            case "double":
                $xmlrpcval->addScalar($php_val, $XML_RPC_Double);
                break;
            case "string":
            case "NULL":
                $xmlrpcval->addScalar($php_val, $XML_RPC_String);
                break;
            case "boolean":
                $xmlrpcval->addScalar($php_val, $XML_RPC_Boolean);
                break;
            case "unknown type":
            default:
                return null;
        }
        return $xmlrpcval;
    }

    // }}}
    
    public function encryptOld($string)
    {
        if (!class_exists('G')) {
            $realdocuroot = str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] );
            $docuroot = explode( '/', $realdocuroot );
            array_pop( $docuroot );
            $pathhome = implode( '/', $docuroot ) . '/';
            array_pop( $docuroot );
            $pathTrunk = implode( '/', $docuroot ) . '/';
            require_once($pathTrunk.'gulliver/system/class.g.php');
        }
        return G::encryptOld($string);
    }

}

?>
