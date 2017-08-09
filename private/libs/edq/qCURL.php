<?php

class qCURL
{
  protected $params    = [];
  protected $opts;
  protected $_chInfo;
  protected $_headers  = [];
  protected $_cookies  = [];
  protected $_content  = null;
  protected $_cerror   = null;
  protected $fetched   = false;
  protected $finalURL  = null;
  protected $lastError = null;

  protected $cache     = null;
  protected $maxage    = 'PT23H'; // See phps DateInterval for format

  public function __construct($params = ['url' => '', 'args' => []])
  {
    // Old syntax has the only possible value of $param as a url,
    // New syntax takes an array with url as a key
    $defParams = [
      'bucket'          => 'reader.cache2',
      'timeout'         => 10,
      'interface'       => null, // auto
      'ignorecache'     => false,
      'ignorecacheage'  => false,
      'args'            => [],
      'postargs'        => [],
      'headers'         => [],
      'cookies'         => [],
      'basefetch'       => false,
      'basedelay'       => 0,
      'allowargsinpost' => false,
      'encoding'        => 'gzip, deflate',
    ];

    if (is_string($params))
    {
      $url    = $params;
      $params = qURL::getUrlAndArgs($url);
    }

    $this->params = array_merge($defParams, $params);

    if (!$this->params['interface'])
    {
      $this->setInterface();
    }

    $this->reset();

    if (defined('CURLCACHE'))
    {
      $this->cache = new qWebpageStore('riak.concord.edqnet.net', 8098);
    }
  }

  protected function setInterface()
  {
    foreach (qStdLib::explodeAndTrim("\n", `/sbin/ifconfig`) as $ifline)
    {
      if (preg_match('/^.*?(192\.168\.21\.[0-9]{1,3}).*/', $ifline, $matches))
      {
        $this->params['interface'] = $matches[1];
        break;
      }
    }
  }

  protected function normalizeURL()
  {
    // Sometimes url in the params is passed in with arguments from external sources.
    // This function combines it with standalone args, and then resets both
    // of them to be correct, so that params['url'] no longer has arguments.

    // Merge the url with a built uril from args.
    $url = qURL::merge($this->params['url'], qURL::build($this->params['url'], $this->params['args']));

    // Split it up
    $parts = qURL::getUrlAndArgs($url);

    // Save it
    $this->params['url']  = $parts['url'];
    $this->params['args'] = $parts['args'];

    // Return it
    $ret = null;
    switch (strtolower($this->getopt(CURLOPT_CUSTOMREQUEST)))
    {
      case 'post':
        if ($this->params['allowargsinpost'])
        {
          $ret = $url;
        }
        else
        {
          $ret = $this->params['url'];
        }
      break;

      default:
        $ret = $url;
      break;
    }

    return $ret;
  }

  protected function mergeCookies()
  {
    $ret     = [];
    $cookies = [];

    foreach ($this->params['cookies'] as $cookie)
    {
      list($k, $v) = explode('=', $cookie, 2);
      $cookies[$k] = $v;
    }

    foreach ($this->_cookies as $cookie)
    {
      list($k, $v) = explode('=', $cookie, 2);
      $cookies[$k] = $v;
    }

    foreach ($cookies as $cn => $cv)
    {
      $ret[] = $cn.'='.$cv;
    }

    $this->_cookies = $ret;
  }

  protected function setRequestHeaderOpts()
  {
    $hdrs = [];
    if (array_key_exists('headers', $this->params))
    {
      foreach ($this->params['headers'] as $k => $v)
      {
        $hdrs[] = $k.': '.$v;
      }

      if (count($hdrs))
      {
        $this->setopt(CURLOPT_HTTPHEADER, $hdrs);
      }
    }
  }

  public function getRandomUA()
  {
    $uas = [
    // IE10
      'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1)',
      'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
      'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)',
      'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
/*
    // IE 11
      'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko',

    // Firefox 33-38
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0',
      'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
      'Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) Gecko/20121223 Ubuntu/9.25 (jaunty) Firefox/3.8',

    // Chrome 41
      'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36',
*/
    ];

    $ua = $uas[rand(0, count($uas) - 1)];

    return $ua;
  }

  public function setRandomUA()
  {
    $ua = $this->getRandomUA();
    $this->setopt(CURLOPT_USERAGENT, $ua);
  }

  public function reset($url = null)
  {
    $this->opts = array();

    if ($url)
    {
      $this->params['url']  = null;
      $this->params['args'] = null;

      switch (true)
      {
        case (is_array($url)):
          $this->params['url']  = $url['url'];
          $this->params['args'] = $url['args'];
        break;

        case (is_string($url)):
          $this->params['url']  = $url;
          $this->params['args'] = [];
        break;
      }
    }
    $this->normalizeUrl();

    $this->setopt(CURLOPT_DNS_CACHE_TIMEOUT, 1);
    $this->setopt(CURLOPT_FORBID_REUSE,      true);
    $this->setopt(CURLOPT_FRESH_CONNECT,     true);
    $this->setopt(CURLOPT_HEADER,            false);
    $this->setopt(CURLOPT_RETURNTRANSFER,    true);
    $this->setopt(CURLOPT_AUTOREFERER,       true);
    $this->setopt(CURLOPT_FOLLOWLOCATION,    true);
    $this->setopt(CURLOPT_MAXREDIRS,         5);
    $this->setopt(CURLOPT_COOKIEJAR,         '');
    $this->setopt(CURLOPT_COOKIEFILE,        '');
    $this->setopt(CURLOPT_COOKIESESSION,     true);
    $this->setopt(CURLOPT_FILETIME,          true);
    $this->setopt(CURLOPT_TIMEOUT,           $this->params['timeout']);
    $this->setopt(CURLOPT_CONNECTTIMEOUT,    $this->params['timeout']);
    $this->setopt(CURLOPT_SSL_VERIFYPEER,    false);
    $this->setopt(CURLOPT_SSL_VERIFYHOST,    0);
    $this->setopt(CURLOPT_ENCODING,          $this->params['encoding']);

    if ($this->params['interface'])
    {
      $this->setopt(CURLOPT_INTERFACE,       $this->params['interface']);
    }
  }

  public function getFinalURL()
  {
    return $this->finalURL;
  }

  public function setopt_array($opts = array())
  {
    $this->opts = array_merge($this->opts, $opts);
  }

  public function setopt($opt, $val)
  {
    $this->opts[$opt] = $val;
  }

  public function getopts($txt = false)
  {
    $ret = [];

    switch ($txt)
    {
      case false:
        $ret = $this->opts;
      break;

      case true:
        // Get curl constants
        foreach ($this->opts as $k => $v)
        {
          $ret[qStdLib::getConstantName($k, 'curl')] = $v;
        }
      break;
    }

    return $ret;
  }

  public function getopt($opt)
  {
    return array_key_exists($opt, $this->opts) ? $this->opts[$opt] : null;
  }

  public function getInfo()
  {
    return $this->_chInfo;
  }

  public function getContent()
  {
    return $this->_content;
  }

  public function getCookies()
  {
    return $this->_cookies;
  }

  public function getHeaders()
  {
    return $this->_headers;
  }

  public function exec($opts = ['global' => [], 'curl' => []])
  {
    $this->_headers = [];

    $this->_fetched = false;
    if (!$this->params['ignorecache'])
    {
      $this->cacheFetch();
    }

    // backstop
    if ($this->_fetched)
    {
      if ($this->_chInfo['http_code'] == 200)
      {
        if (strlen(trim($this->_content)) == 0)
        {
          $this->_fetched = false;
        }
      }
    }

    if (!$this->_fetched)
    {
      $this->liveFetch($opts);
    }

    return $this->_content;
  }

  /*
   * Fetches from cache
   */
  protected function cacheFetch()
  {
    $this->_fetched = false;
    if ($this->cache)
    {
      if ($cached = $this->cache->getPage($this->params, $this->params['bucket']))
      {
        // Check cache age
        $dtNow   = new DateTime();
        if (array_key_exists('X-Riak-Index-Creationstamp-Int', $cached['headers']))
        {
          $dtCache = new DateTime();
          $dtCache->setTimestamp($cached['headers']['X-Riak-Index-Creationstamp-Int']);
        }
        elseif (array_key_exists('Last-Modified', $cached['headers']))
        {
          $dtCache = new DateTime($cached['headers']['Last-Modified']);
        }
        else
        {
          // Header is missing, expire the cache;
          $dtCache = new DateTime();
          $dtCache->sub(new DateInterval('PT48H'));
        }

        $dtCache->add(new DateInterval($this->maxage)); // Add maxage to cache age
        if ($this->params['ignorecacheage'] || ($dtCache > $dtNow))
        {
          $data = unserialize($cached['content']);

          $fetched = ((array_key_exists('cookies', $data)) &&
                      (array_key_exists('info', $data)) &&
                      (array_key_exists('headers', $data)) &&
                      (array_key_exists('content', $data)));

          if ($fetched)
          {
            $this->_cookies = $data['cookies'];
            $this->_chInfo  = $data['info'];
            $this->_headers = $data['headers'];
            $this->_content = $data['content'];
            $this->_cerror  = array_key_exists('error', $data) ? $data['error'] : '';
            $this->_fetched = true;
          }
        }
      }
    }
  }

  /*
   * Runs curl exec.
   */
  public function liveFetch($opts, $isBaseFetch = false)
  {
    $ch     = null;
    $res    = null;

    if ($ch = curl_init())
    {
      $this->setRandomUA();

      if (array_key_exists('curl', $opts))
      {
        // We can't use array_merge here because the CURL constants are integers,
        // and array_merge renumbers integer based arrays.
        foreach ($opts['curl'] as $k => $v)
        {
          $this->setopt($k, $v);
        }
      }

      $method = array_key_exists('method', $this->params) ? $this->params['method'] : 'GET';

      $this->setopt(CURLOPT_CUSTOMREQUEST, strtoupper($method));
      switch (strtolower($method))
      {
        case 'post':
          $posttype = array_key_exists('posttype', $this->params) ? $this->params['posttype'] : 'standard';
          switch (strtolower($posttype))
          {
            case 'url':
              $this->setopt(CURLOPT_POSTFIELDS, http_build_str($this->params['postargs']));
            break;

            case 'standard':
            default:
              $this->setopt(CURLOPT_POSTFIELDS, $this->params['postargs']);
            break;
          }
        break;

        default:
        break;
      }

      $this->setRequestHeaderOpts();

      curl_setopt_array($ch, $this->opts);

      $url = $this->normalizeURL();

      if ($this->params['basefetch'])
      {
        if ($isBaseFetch)
        {
          $url = qURL::getBaseUrl($url);
        }
        else
        {
          qStdLib::lprint('Fetching base url');
          $this->liveFetch($opts, true);
        }
      }

      $this->mergeCookies();

      if (count($this->_cookies))
      {
        curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $this->_cookies));
      }

      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'headerFunction']);

      $this->_headers  = [];
      $this->_content  = curl_exec($ch);
      $this->_chInfo   = curl_getinfo($ch);
      $this->finalURL  = $this->_chInfo['url'];
      $this->lastError = curl_error($ch);

      curl_close($ch);
      $ch = null;
      gc_collect_cycles();

      if (($this->_chInfo['http_code'] < 400) && ($this->_chInfo['http_code'] > 0) && $this->cache)
      {
        $this->saveToCache();
      }

      if (($this->params['basefetch']) && ($this->params['basedelay']) && ($isBaseFetch))
      {
        usleep($this->params['basedelay'] * 1000000);
      }
    }
  }

  protected function saveToCache()
  {
    if ($this->params['bucket'])
    {
      $urls = array_unique([$this->normalizeURL(), $this->finalURL]);

      foreach ($urls as $url)
      {
        qStdLib::lprint('Storing "'.$url.'" in cache.');

        $dataParts = [
          'content'  => $this->_content,
          'info'     => $this->_chInfo,
          'cookies'  => $this->_cookies,
          'headers'  => $this->_headers,
          'error'    => $this->lastError
        ];

        $urlParts = qURL::getUrlAndArgs($url);

        $fd = [
          'bucket'       => $this->params['bucket'],
          'content-type' => trim($this->_chInfo['content_type']) ? $this->_chInfo['content_type'] : 'text/plain',
          'content'      => serialize($dataParts),
          'params'       =>
          [
            'url'  => $urlParts['url'],
            'args' => $urlParts['args'],
            'postargs' => $this->params['postargs'],
            'headers'  => $this->params['headers'],
          ],
        ];

        $this->cache->storePage($fd);
      }
    }
  }

  // Public, but for internal use only.  Must be public to be made available
  // for curl to use as a callback
  public function headerFunction($ch, $dat)
  {
    $matchres = preg_match('/^([-a-zA-Z0-9]+): (.*)/', trim($dat), $matches);
    if (($matchres !== false) && ($matchres > 0))
    {
      switch (strtolower($matches[1]))
      {
        case 'set-cookie':
          $parts = explode('; ', $matches[2]);
          $this->_cookies[] = $parts[0];
        break;

        default:
          $this->_headers[$matches[1]] = $matches[2];
        break;
      }
    }

    return strlen($dat);
  }

  public function getError()
  {
    return $this->lastError;
  }
}
