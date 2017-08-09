<?php
  class qURL
  {
    public static function parse($url)
    {
      return parse_url($url);
    }

    public static function fullDecode($val)
    {
      $ret = null;
      if (is_array($val))
      {
        $ret = [];
        foreach ($val as $v)
        {
          $ret[] = self::fullDecode($v);
        }
      }
      else
      {
        while ($val != urldecode($val))
        {
          $val = trim(urldecode($val));
        }

        while ($val != trim(html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8')))
        {
          $val = trim(html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $ret = $val;
      }

      return $ret;
    }

    public static function getBaseUrl($url)
    {
      $ret = '';

      $parts = parse_url($url);
      $ret = $parts['scheme'].'://'.$parts['host'];
      $ret .= array_key_exists('port', $parts) ? ':'.$parts['port'] : '';

      return $ret;
    }

    /*
      Given a URL returns an array with the base URL separated from
      the params, and the params in their own associative array, to
      be serialized when stored.
    */
    public static function getUrlAndArgs($url)
    {
      $ret   = array('url' => null, 'args' => []);
      $parts = parse_url($url);

      // PHP does not properly parse a straight hostname e.g. 'www.foo.com' as a URL.
      if ((count(array_keys($parts)) == 1) && (array_key_exists('path', $parts)))
      {
        $parts = parse_url('http://'.$url);
      }

      $ret['url'] = $parts['scheme'].'://'.$parts['host'];
      $ret['url'] .= array_key_exists('port', $parts) ? ':'.$parts['port'] : '';
      $ret['url'] .= array_key_exists('path', $parts) ? $parts['path'] : '';

      if (array_key_exists('query', $parts))
      {
        foreach (preg_split('/&/', html_entity_decode($parts['query']), -1, PREG_SPLIT_NO_EMPTY) as $kv)
        {
          $lkv = explode('=', $kv, 2);
          $k = self::fullDecode(array_shift($lkv));
          $v = '';
          if (count($lkv))
          {
            $v = self::fullDecode(array_shift($lkv));
          }
          $ret['args'][$k] = $v;
        }
      }
      return $ret;
    }

    public static function merge($oURL, $nURL)
    {
      $rURL = self::buildUrl(array_merge(self::parse($oURL), self::parse($nURL)));

      return $rURL;
    }

    public static function build($base, $args)
    {
      $parts = parse_url($base);
      $parts['query'] = http_build_query($args);
      return self::buildUrl($parts);
    }

    /*
    * This is a helper function that assists in bulding
    * human readable links when arguments are stored in a
    * serialized or array format.
    *
    * $base = string (base part of the URL string)
    * $args = mixed - array, serialized data or null
    */
    public static function buildHumanLink($base, $args)
    {
      if(is_array($args))
      {
        $args_array = $args;
      }
      else
      {
        $args_array = unserialize($args);
        if ($args_array == false)
        {
          $args_array = [];
        }
      }
      return qUrl::build($base, $args_array);
    }

    public static function getArgFromUrl($url, $arg, $default = '')
    {
      $ret = $default;
      if ($parts = qURL::getUrlAndArgs($url))
      {
        if (array_key_exists('args', $parts))
        {
          if (array_key_exists($arg, $parts['args']))
          {
            $ret = $parts['args'][$arg];
          }
        }
      }

      return $ret;
    }

    public static function splitUrlPath($url)
    {
      $ret = [];

      if ($parts = self::parse($url))
      {
        if (array_key_exists('path', $parts))
        {
          $ret = preg_split('/\//', $parts['path'], -1, PREG_SPLIT_NO_EMPTY);
        }
      }

      return $ret;
    }

    public static function replaceUrlPathItem($url, $idx, $val)
    {
      $ret = $url;

      $uparts     = self::parse($url);
      if ($pparts = self::splitUrlPath($url))
      {
        if (count($pparts) >= $idx)
        {
          $pparts[$idx - 1] = $val;
          $uparts['path'] = '/'.join('/', $pparts);
          $ret = self::buildUrl(array_merge(self::parse($url), $uparts));
        }
      }

      return $ret;
    }

    public static function changeUrlArg($url, $arg, $val)
    {
      $ret = $url;

      if ($parts = self::getUrlAndArgs($url))
      {
        $parts['args'][$arg] = $val;
        $ret = self::buildHumanLink($parts['url'], $parts['args']);
      }

      return $ret;
    }

    public static function getUrlPart($part, $url)
    {
      $ret = null;

      try
      {
        $parts = parse_url($url);
        if (array_key_exists($part, $parts))
        {
          $ret = $parts[$part];
        }
      }
      catch (Exception $e)
      {
      }

      return $ret;
    }

    public static function removeArgs($url)
    {
      $ret = $url;

      try
      {
        $parts = self::getUrlAndArgs($url);
        $ret = $parts['url'];
      }
      catch (Exception $e)
      {
      }

      return $ret;
    }

    // Fixes minor issues with URLs
    public static function fixup($url)
    {
      $ret = $url;

      if ($parts = self::parse_url($url))
      {
        $ret = self::buildUrl($parts);
      }

      return $ret;
    }

    // Function can be called direct and is also used by runkit
    public static function parse_url($url)
    {
      $fn = function_exists('qphp_parse_url') ? 'qphp_parse_url' : 'parse_url';

      if ($parts = $fn($url))
      {
        if (!array_key_exists('scheme', $parts) && (array_key_exists('host', $parts)))
        {
          $parts['scheme'] = 'http';
        }
      }

      return $parts;
    }

    public static function isComplete($url)
    {
      $ret   = false;

      if ($parts = parse_url($url))
      {
        $ret = array_key_exists('scheme', $parts) &&
               array_key_exists('host', $parts);
      }

      return $ret;
    }

    // Takes an array of key/val pairs and returns a string suitable for
    // use as a url-encoded post string.
    public static function urlEncodeArray($args = [])
    {
      $ret = '';

      if (is_array($args))
      {
        $tmp = [];

        foreach ($args as $k => $v)
        {
          $tmp[] = rawurlencode($k).'='.rawurlencode($v);
        }

        $ret = join('&', $tmp);
      }

      return $ret;
    }

    // replacement for http_build_url
    public static function buildUrl($parts)
    {
      $url = new http\Url($parts);
      return $url->toString();
    }

  }
