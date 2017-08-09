<?php
  class qPageArgs implements Iterator
  {
    // iterator interface properties
    protected $_iikeys = array();
    protected $_iipos = null;

    // standard properties
    protected $_args = array();
    protected $_page = '';
    protected $_pageType = 'html';
    protected $knownTypes = array('html', 'xml', 'js', 'json', 'qr', 'rss', 'soap', 'wsdl', 'xsd','txt');

    // Iterator interface methods
    public function current()
    {
      return $this->_args[$this->_iikeys[$this->_iipos]];
    }

    public function key()
    {
      return $this->_iikeys[$this->_iipos];
    }

    public function next()
    {
      $this->_iipos++;
    }

    public function rewind()
    {
      // init iterator vars
      $this->_iikeys = array_keys($this->_args);
      $this->_iipos  = 0;
    }

    public function valid()
    {
      return ($this->_iipos < count($this->_iikeys));
    }

    // Standard methods
    public function __construct($uri)
    {
      $urlParts  = parse_url($uri);
      $path      = $urlParts['path'];
      $fnparts   = explode('.', $path);

      // Determine page type
      if (count($fnparts) > 1)
      {
        $type = mb_strtolower(array_pop($fnparts));
        if (in_array($type, $this->knownTypes))
        {
          $this->_pageType = $type;
          $path = implode('.', $fnparts);
        }
      }

      // parse args
      $tmp = preg_split('/\//', $path, -1, PREG_SPLIT_NO_EMPTY);
      if (count($tmp) > 0)
      {
        $this->_page = '/'.array_shift($tmp);
        while(count($tmp) > 0)
        {
          $k = array_shift($tmp);
          $v = urldecode(array_shift($tmp));
          $this->_args[$k]['_raw']   = $v;
          $this->_args[$k]['_parts'] = preg_split('/,/', $v);
        }
      }

      // init iterator vars
      $this->rewind();
    }

    public function getPage()
    {
      return $this->_page;
    }

    public function setPage($page, $findroute = true)
    {
      if ($findroute)
      {
        $this->_page = qFrontController::SgetRoute($page);
      }
      else
      {
        $this->_page = $page;
      }
    }

    public function set($k, $v = null)
    {
      if ($k)
      {
        if ($v != null)
        {
          $this->_args[$k]['_raw'] = $v;
          $this->_args[$k]['_parts'] = preg_split('/,/', $v);
        }
        else
        {
          unset($this->_args[$k]);
        }
      }
      return $this;
    }

    public function getAll()
    {
      $ret = null;

      foreach ($this->_args as $k => $v)
      {
        $ret[$k] = $this->get($k);
      }

      return $ret;
    }

    protected function _internalGet($k, $part = -1, $def = null, $lower = false)
    {
      $ret = null;

      $args = $lower ? array_change_key_case($this->_args) : $this->_args;

      if (array_key_exists($k, $args))
      {
        if ($part == -1)
        {
          $ret = $args[$k]['_raw'];
        }
        else
        {
          if (count($args[$k]['_parts']) > $part)
          {
            $ret = $args[$k]['_parts'][$part];
          }
        }
      }
      else
      {
        $ret = $def;
      }

      return $ret;
    }

    public function getLower($k, $part = -1, $def = null)
    {
      return $this->_internalGet(strtolower($k), $part, $def, true);
    }

    public function get($k, $part = -1, $def = null)
    {
      return $this->_internalGet($k, $part, $def);
    }

    public function getPagetype()
    {
      return $this->PageType;
    }

    public function setPageType($type)
    {
      $this->pageType = $type;
      return $this;
    }

    public function buildUrl($add = array())
    {
      $uri = $this->_page;
      $tmp = array();

      foreach ($this->_args as $k => $v)
      {
        $tmp[$k] = $v['_raw'];
      }

      $tmp = array_merge($tmp, $add);
      foreach ($tmp as $k => $v)
      {
        $uri .= '/' . $k . '/' . $v;
      }

      $uri = preg_replace('/\/+/', '/', $uri);

      return $uri;
    }

    public function dump()
    {
      error_log($_SERVER['REQUEST_URI']);
      error_log(print_r($this, true));
      error_log('out: ' . $this->Urlbuild());
      return $this;
    }
  }
