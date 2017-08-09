<?php
  /**
   * Static Functions to Make interacting with the user interface simple.
   * Check here first before you write a custom function.
   * If you do something more than once and its generic this is the place to put this code.
   */
class qStdLib
{
  static public $loadtime;
  static public $dumpno = 0;
  static public $init   = false;
  static public $constants = null;

  static public function getShortHostname()
  {
    $tmp = explode('.', gethostname());
    return array_shift($tmp);
  }

  static public function dumpMemStats($mode = 'callgrind')
  {
    $pid = getmypid();
    self::$dumpno++;

    switch ($mode)
    {
      case 'html':
        $file = '/usr/local/www/data/sites/webgrind/dumps/'.$pid.'.'.self::$dumpno.'.html';
        $fh = fopen($file, 'w');
        if ($fh !== false)
        {
          fwrite($fh, '<html><body><pre>');
          fwrite($fh, print_r(memprof_dump_array(), true));
          fwrite($fh, '</pre></body></html>');
          fclose($fh);
          self::lprint('*** dumped mem stats to '.$file);
        }
        else
        {
          self::lprint('*** mem dump failed');
        }
      break;

      case 'callgrind':
        $file = '/usr/local/www/apache22/data/sites/webgrind/profile/callgrind.'.$pid.'.'.self::$dumpno;
        memprof_dump_callgrind(fopen($file, 'w'));
        self::lprint('*** dumped mem stats to '.$file);
      break;

      case 'pprof':
        $file = '/usr/local/www/apache22/data/sites/webgrind/profile/'.$pid.'.'.self::$dumpno.'.heap';
        memprof_dump_pprof(fopen($file, 'w'));
        self::lprint('*** dumped mem stats to '.$file);
      break;
    }
  }

  static public function oprint($msg)
  {
    if (!defined('qQUIET'))
    {
      if (php_sapi_name() === 'cli')
      {
        print($msg);
      }
      else
      {
        error_log($msg);
      }
    }
  }

  static public function lprintMem($msg, $maxlen = 4096)
  {
    static $usedmem = 0;
    $newmem = memory_get_usage(true);
    if ($usedmem != $newmem)
    {
      $diff = abs($usedmem - $newmem);
      if ($usedmem > $newmem)
      {
        self::oprint(self::lprintFmt('--- DECREASE '.number_format($diff).' ('.number_format($newmem).')', false)."\t".$msg."\n");
      }
      else
      {
        self::oprint(self::lprintFmt('+++ INCREASE '.number_format($diff).' ('.number_format($newmem).')', false)."\t".$msg."\n");
      }
      $usedmem = $newmem;
    }
    else
    {
      self::oprint(self::lprintFmt('=== NOCREASE 000,000 ('.number_format($newmem).')', false)."\t".$msg."\n");
    }
  }

  /** Checks if a value is serialized or not
   *
   * decode_json() returns FALSE for both a serialized boolean
   * that is false, and a failure to decode.
   *
   * This function returns true if the value equals the
   * serialized value of false, or if unserialize returns
   * anything other than false.
   */
  static public function isJSON($val)
  {
    $val = json_decode($val);

    return (json_last_error() == JSON_ERROR_NONE);
  }

  /**
   * Checks that all array keys exist in array.
   *
   * array_values($keys) is used to get the list of keys to search
   * for so that not only can normal arrays be used, so can the
   * values in an associative array.
   *
   * @param array $keys
   *   The keys to check for
   * @param array $array
   *   The array to search
   * @return boolean
   *   True if all keys exist
   *
   */
  static public function array_keys_exist($keys, $array)
  {
    $ret = true;

    if (is_array($keys) && (is_array($array)))
    {
      foreach (array_values($keys) as $k)
      {
        if (!array_key_exists($k, $array))
        {
          $ret = false;
          break;
        }
      }
    }
    else
    {
      throw new Exception('array_keys_exist requires two array parameters');
    }

    return $ret;
  }

  /**
   * Flourish debugging callback handle
   */
  static public function debugcb($msg)
  {
    error_log($msg);
  }

  /**
   * Builds a Query String for a URL.
   *
   * ie. /foo1/bar1/foo2/bar2  etc...
   *
   * @param array $query
   *   the key and value to create.
   * @return string
   *   The Query String.
   */
  static public function makeQueryString($query = array())
  {
    $ret = '';
    foreach ($query as $k => $v)
    {
      $ret .= '/' .$k . '/' . str_replace('/', '%2F', $v);
    }
    return $ret;
  }

  static public function buildFilter($records, $col, $args = array())
  {
    $getFn = 'get' . $col;
    $ret  = array();
    $idxs = array();

    foreach ($records as $record)
    {
      $idxs = array();
      $data = $record->$getFn($args);

      if (count($args))
      {
        foreach ($args[$col]['ranges'] as $arg)
        {
          $lfn = 'get'.$args[$col]['field'];
          if ($record->$lfn() <= $arg['max'])
          {
            $idxs[] = $arg['max'];
          }
        }
      }
      else
      {
        $idxs[] = $data;
      }

      foreach ($idxs as $idx)
      {
        $qs = self::makeQueryString(array(fGrammar::camelize($col, true) => $idx));
        if (array_key_exists($qs, $ret))
        {
          $ret[$qs]['count']++;
        }
        else
        {
          $ret[$qs]['url_segment'] = $qs;
          $ret[$qs]['count']       = 1;
          $ret[$qs]['data']        = $record->formatColData($col, $idx);
        }
      }
    }

    if ($ret)
    {
      ksort($ret);
    }

    return $ret;
  }

  static public function isArrayDataValid($a, $dtype, $count = false)
  {
    $ret = false;

    if (is_array($a))
    {
      switch ($dtype)
      {
        case 'numeric':
          foreach ($a as $av)
          {
            if (!is_numeric($av))
            {
              break;
            }
          }

          // Earlier break prevents this from running
          if ($count !== false)
          {
            $ret = (count($a) == $count);
          }

        break;
      }
    }

    return $ret;
  }

  static public function getFileList($path)
  {

    $ret  = array();
    if (is_dir($path))
    {
      if ($h = opendir($path))
      {
        while (false !== ($f = readdir($h)))
        {
          if (!is_dir($path.$f))
          {
            $ret[] = $path.$f;
          }
        }
        closedir($h);
      }
    }
    return $ret;
  }

  static public function buildSerImageFiles($path,$data)
  {
    $files = self::getFileList($path);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    foreach ($files as $file)
    {
      list($type, $handler) = explode('/', finfo_file($finfo, $file));
      if ($type == 'image')
      {
        $key = str_replace($path, '', $file);
        file_put_contents($file . '.ser', serialize($data[$key]));
      }
    }
    finfo_close($finfo);
  }

  static public function getSerImageFiles($path)
  {
    $ret = array();
    $files = self::getFileList($path);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    foreach ($files as $file)
    {
      list($type, $handler) = explode('/', finfo_file($finfo, $file));
      if ($type == 'image')
      {
        if (file_exists($file . '.ser'))
        {
          $array = unserialize(file_get_contents($file . '.ser'));
          $ret[] = array_merge($array, array('src'=>$file));
        }
      }
    }
    finfo_close($finfo);
    return $ret;
  }


  static public function lprint($msg, $maxlen = 4096)
  {
    // Truncate message to max length
    $msg = substr($msg, 0, $maxlen);

    if (defined('MEMPRINT'))
    {
      self::lprintMem($msg, $maxlen);
    }
    else
    {
      self::oprint(self::lprintFmt($msg));
    }
  }

  static public function lprintFmt($msg, $nl = true)
  {
    $dt = microtime(true) - self::$loadtime;

    $h   = floor($dt / 3600);
    $dt -= ($h * 3600);
    $m   = floor($dt / 60);
    $dt -= ($m * 60);
    $s   = $dt;

    $msg = '['.getmypid().'] ('.sprintf('%03.0f:%02.0f:%05.2f', $h, $m, $s).'): '.$msg;

    if ($nl)
    {
      $msg .= "\n";
    }

    return $msg;
  }

  static public function debugBacktrace($options = DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit = 0, $lightweight = true)
  {
    $ret = [];
    $bt  = debug_backtrace($options, $limit > 0 ? $limit + 1 : 0);

    // Remove the call to this function
    array_shift($bt);

    if ($lightweight)
    {
      foreach ($bt as &$entry)
      {
        unset($entry['args']);
      }
    }

    $ret = $bt;

    return $ret;
  }

  static public function stristr($haystack, $needle, $before_needle = false)
  {
    $ret = false;

    if (is_array($needle))
    {
      foreach ($needle as $sr)
      {
        $ret = stristr($haystack, $sr, $before_needle);
        if ($ret)
        {
          break;
        }
      }
    }
    else
    {
      $ret = stristr($haystack, $needle, $before_needle);
    }

    return $ret;
  }

  /** Checks if a value is serialized or not
   *
   * unserialize() returns FALSE for both a serialized boolean
   * that is false, and a failure to unserialize.
   *
   * This function returns true if the value equals:
   *   the serialized value of false,
   *   the serialized value of zero,
   *   the serialized value of an empty array
   *
   */
  static public function isSerialized($val)
  {
    $fn = function_exists('qphp_unserialize') ? 'qphp_unserialize' : 'unserialize';

    return ($val === serialize(0) ||
            $val === serialize(false) ||
            $val === serialize([]) ||
            false !== @$fn($val));
  }

  // Unserializes but checks that the value is serialized first, so boolean false is properly handled.
  static public function unserialize($val)
  {
    $ret = false;
    if (self::isSerialized($val))
    {
      $fn = function_exists('qphp_unserialize') ? 'qphp_unserialize' : 'unserialize';
      $ret = @$fn($val);
    }

    return $ret;
  }

  static public function ltrim($str, $charlist = " \t\r\n\0\x0B")
  {
    return self::trim($str, $charlist, 'left');
  }

  static public function rtrim($str, $charlist = " \t\r\n\0\x0B")
  {
    return self::trim($str, $charlist, 'right');
  }

  // A trim that handles unicode properly
  static public function trim($str, $charlist = " \t\r\n\0\x0B", $ends = 'both')
  {
    // Ensure str is utf-8
    $ret = mb_convert_encoding($str, 'UTF-8');

    // Explode characters into array of unicode chars
    $hasSpace = mb_strpos($charlist, ' ', 0, 'UTF-8') !== false;

    $re = '';
    switch ($ends)
    {
      case 'left':
        $re = '/^[\pZ\pC]+/u';
      break;

      case 'right':
        $re = '/[\pZ\pC]+$/u';

      case 'both':
        $re = '/^[\pZ\pC]+|[\pZ\pC]+$/u';
      break;
    }

    // Handle space with unicode support
    if ($hasSpace)
    {
      $ret = preg_replace($re, '', $ret);
    }

    // Handle all remaining chars
    // Regex isn't working right, but don't need it just yet anyway.
    switch ($ends)
    {
      case 'left':
        $ret = qphp_ltrim($ret, $charlist);
      break;

      case 'right':
        $ret = qphp_rtrim($ret, $charlist);
      break;

      case 'both':
        $ret = qphp_trim($ret, $charlist);
      break;
    }

    return (string)$ret;
  }

  // The meta-function for strtolower/strtoupper.  Requires mbstring
  static public function strcase($str, $upper)
  {
    $ret = $str;

    switch ($upper)
    {
      case true:
        $ret = mb_strtoupper($str);
      break;

      case false:
        $ret = mb_strtolower($str);
      break;
    }

    return $ret;
  }

  // A multibyte strtolower
  static public function strtolower($s)
  {
    $cn = get_called_class();
    return $cn::strcase($s, false);
  }

  // A multibyte strtoupper
  static public function strtoupper($s)
  {
    $cn = get_called_class();
    return $cn::strcase($s, true);
  }

  // Explodes a string into an array like explode(), returning an array.
  // The returned array only contains non-empty strings.  Additional characters
  // representing blanks in UTF-8 (0xc2 and 0xa0) are also trimmed.
  static public function explodeAndTrim($delim, $string, $limit = -1)
  {
    $ret = [];

    $tmp = preg_split('/'.preg_quote($delim, '/').'/', $string, $limit, PREG_SPLIT_NO_EMPTY);

    foreach ($tmp as $item)
    {
      $trimmed = (string)trim($item);
      if ($trimmed !== false)
      {
        $ret[] = $trimmed;
      }
    }
    return $ret;
  }

  static public function posix_kill($pid, $sig)
  {
    $fn = function_exists('qphp_posix_kill') ? 'qphp_posix_kill' : 'posix_kill';
    return $fn($pid, $sig);
  }

  public static function getConstantName($val, $section = 'Core')
  {
    $ret = array_search($val, self::$constants[$section], true);
    return $ret;
  }

  public static function init()
  {
    self::$loadtime = microtime(true);
    self::$constants = get_defined_constants(true);
    // make some custom groups
    if(isset(self::$constants['pcntl']))
    {
      foreach (self::$constants['pcntl'] as $k => $v)
      {
        if ((strpos($k, 'SIG') === 0) && (strpos($k, 'SIG_') === false))
        {
          self::$constants['qSignals'][$k] = $v;
        }
      }
    }
    foreach (self::$constants['curl'] as $k => $v)
    {
      if (strpos($k, 'CURLOPT_') === 0)
      {
        self::$constants['qCurl'][$k] = $v;
      }
    }

    foreach (self::$constants['Core'] as $k => $v)
    {
      if (strpos($k, 'E_') === 0)
      {
        self::$constants['qErrors'][$k] = $v;
      }
    }
  }

  public static function deep_array_merge($ret, $arb)
  {
    $fn  = __METHOD__;
    if (!is_array($ret) || !is_array($arb))
    {
      throw new Exception('Both arguments to '.__METHOD__.' must be arrays.');
    }

    foreach ($arb as $k => $v)
    {
      if (is_array($v))
      {
        if (!$ret[$k])
        {
          $ret[$k] = [];
        }
        $ret[$k] = $fn($ret[$k], $v);
      }
      else
      {
        $ret[$k] = $v;
      }
    }

    return $ret;
  }
}

qStdLib::init();