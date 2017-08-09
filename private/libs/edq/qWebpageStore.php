<?php
  // Extends qFileStore to be more useful for storing webpages
  class qWebpageStore extends qFileStore
  {
    public function getPageName($params)
    {
      $pname = [];
      $name = '';
      if (is_array($params))
      {
        $kl = array_keys($params);
        sort($kl);
        foreach($kl as $k)
        {
          if ($params[$k])
          {
            switch (strtolower($k))
            {
              case 'url':
              case 'args':
              case 'postargs':
              case 'headers':
                $pname[] = $params[$k];
              break;

              default:
              break;
            }
          }
        }
        $name = serialize($pname);
      }
      else
      {
        throw new Exception(__METHOD__.' takes an array as its only parameter');
      }
      return $name;
    }

    public function getPageLink($bucket, $params)
    {
      return $this->getCacheLink($bucket, self::internalName($this->getPageName($params)));
    }

    public function getPage($params, $bucket = null)
    {
      $ret = false;

      if (is_array($params))
      {
        if (!$bucket)
        {
          if (array_key_exists('bucket', $params))
          {
            $bucket = $params['bucket'];
          }
          else
          {
            $bucket = 'test';
          }
        }
        $ret = parent::getFile($this->getPageName($params), $bucket);
      }
      else
      {
        throw new Exception(__METHOD__.' takes an array as its first parameter');
      }
      return $ret;
    }

    public function storePage($fd)
    {
      $ret = false;

      if (is_array($fd))
      {
        if (array_key_exists('params', $fd))
        {
          if (is_array($fd['params']))
          {
            $fd['name']      = $this->getPageName($fd['params']);
            $fd['secondary'] = ['creationstamp_int' => time()];
            $ret = parent::storeFile($fd);
          }
          else
          {
            throw new Exception(__METHOD__.' No params array in fd');
          }
        }
        else
        {
          throw new Exception(__METHOD__.' params key missing from fd');
        }
      }
      else
      {
        throw new Exception(__METHOD__.' takes an array as its only parameter');
      }

      return $ret;
    }
  }