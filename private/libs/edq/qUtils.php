<?php
  class qUtils
  {

    public static function hashToTag($tag, $attrs, $contents = null)
    {
      $ret = '';
      $hasSubs = false;

      if (is_array($attrs))
      {
        foreach ($attrs as $k => $v)
        {
          if (is_array($v))
          {
            $hasSubs = true;
            $ret .= self::hashToTag($tag, $v, $contents);
          }
        }

        if (!$hasSubs)
        {
          $ret .= '<'.$tag.' ';
          foreach ($attrs as $k => $v)
          {
            $ret .= $k.'="'.$v.'" ';
          }

          if ($contents)
          {
            $ret .= '>'.$contents.'</'.$tag.">\n";
          }
          else
          {
            $ret .= "/>\n";
          }
        }
      }

      return $ret;
    }

    public static function exfile_exists($path)
    {
      $ret = false;

      $url = parse_url($path);
      if (isset($url['scheme']))
      {
        if ($ch = curl_init())
        {
          curl_setopt($ch, CURLOPT_URL, $path);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_FAILONERROR, true);
          curl_setopt($ch, CURLOPT_NOBODY, true);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
          try
          {
            $ret = curl_exec($ch);
            curl_close($ch);
          }
          catch (Exception $e)
          {
          }
        }
      }
      else
      {
        $ret = file_exists($path);
      }

      return $ret;
    }

    /**
     * Implodes up to $max elements from $pieces, concatenating them with $glue.
     *
     * If $max is -1, all pieces are concatenated, making it identical to
     * imploade.
     *
     * $glue = String used in concatenation.
     * $pieces = Array to implode.
     * $max = Maximum number of pieces to concatenate.
     * $rtl = If nonzero, pieces are concatenated right to left up to $max instead of left to right.
     */
    public static function smartImplode($glue, $pieces, $max = -1, $rtl = 0)
    {
      $ret = NULL;
      $remain = 0;

      if (is_array($pieces))
      {
        if (($max == -1) || ($max >= count($pieces)))
        {
          $ret = implode($glue, $pieces);
        }
        else
        {
          $remain = count($pieces) - $max;
          while ($remain > 0)
          {
            if ($rtl)
            {
              array_shift($pieces);
            }
            else
            {
              array_pop($pieces);
            }
            $remain--;
          }

          $ret = implode($glue, $pieces);
        }
      }
      else
      {
        throw new Exception('PIECES passed to smartImplode is not an array.');
      }

      return $ret;
    }

    /**
    * smart_include actively searches for the file in the include path, since PHPs include path is only
    * searched if the path is path-component free.
    *
    * "include('bar.php')" will search the include path.
    * "include('/bar.php')" will not, as expected.
    * "include('foo/bar.php')" will not, which is bad.
    */
    public static function smartInclude($file)
    {
      $ret   = '';
      $fn    = $file;
      $paths = explode(PS, get_include_path());

      foreach ($paths as $path)
      {
        $fn = rtrim($path, DS).DS.$file;

        if (file_exists($fn))
        {
          $ret = $fn;
          break;
        }
      }

      return $ret;
    }

    /**
    * Takes a password in plaintext and returns a hash according to MCF type.
    *
    * $password = plaintext password
    * $func = The MCF hashtype to use.  The following are supported
    *   '1'  : md5
    *   '2y' : blowfish-fixed
    *   '5'  : sha-256 (default)
    *   '6'  : sha-512
    */
    public static function computeMCFHash($func, $str)
    {
      $ret = '';
      $h   = MHASH_SHA512;
      switch ($func)
      {
        case '1':
          // md5
          $h = MHASH_MD5;
        break;

        case '5':
          // sha-256
          $h = MHASH_SHA256;
        break;

        default:
        case '6':
          // sha-512
          $h = MHASH_SHA512;
        break;
      }
      $ret = mhash($h, $str);

      return bin2hex($ret);
    }

    public static function PasswordToMCF($password, $func = '5')
    {
      $ret = '';
      $salt = fCryptography::randomString(16);
      $hash = self::computeMCFHash($func, $salt.$password);

      return '$'.$func.'$'.$salt.'$'.$hash;
    }

    public static function computeChallengeResponse($mcf, $chal)
    {
      $tmp = self::MCFToArray($mcf);
      $ret = self::computeMCFHash($tmp['func'], $tmp['hash'].$chal);
      return $ret;
    }

    public static function validatePassword($mcf, $password)
    {
      $tmp = self::MCFToArray($mcf);
      $hash = self::computeMCFHash($tmp['func'], $tmp['salt'].$password);
      return ($hash == $tmp['hash']);
    }

    public static function MCFToArray($mcf)
    {
      $ret = array();

      $tmp = preg_split('/\$/', $mcf, 0, PREG_SPLIT_NO_EMPTY);
      if (count($tmp) == 3)
      {
        $ret['func'] = $tmp[0];
        $ret['salt'] = $tmp[1];
        $ret['hash'] = $tmp[2];
      }

      return $ret;
    }

    /**
    * Compares a hashed password to its MCF variant
    *
    */
    public static function PasswordCompare($password, $mcf, $chal)
    {
      $resp = self::computeChallengeResponse($mcf, $chal);
      return ($resp == $password);
    }

    /**
     * Recursively delete a local directory.  Returns list of
     * deleted items
     */
    public static function recurseRmDir($path)
    {
      $ret = array();
      if (strstr($path, count($path)) !== '/')
      {
        $path .= '/';
      }

      if ($h = opendir($path))
      {
        while (false !== ($entry = readdir($h)))
        {
          if (($entry != '.') && ($entry != '..'))
          {
            if (is_dir($path.$entry))
            {
              $ret = array_merge ($ret, self::recurseRmDir($path.$entry));
            }
            else
            {
              $ret[] = 'F: '.$path.$entry;
              unlink($path.$entry);
            }
          }
        }
        $ret[] = 'D: '.$path;
        rmdir($path);
      }
      return $ret;
    }
  }