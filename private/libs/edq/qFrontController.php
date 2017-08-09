<?php
  class qFrontController
  {
    /***
     ***
     *** Do not make these public.
     *** Create set/get methods if access is needed outside the FC
     ***
     ***/
    protected $cameFrom  = null;
    protected $args      = null;
    protected $urlParts  = [];
    protected $path      = '';
    protected $page      = null;
    protected $routes    = [];
    protected $aliases   = [];
    protected $parts     = [];
    protected $style     = null;
    protected $type      = 'html';
    protected $browser   = null;

    public static function get()
    {
      static $_fc = null;
      if ($_fc == null)
      {
        $_fc = new qFrontController();
      }
      return $_fc;
    }

    private function __construct()
    {
      setlocale(LC_MONETARY, 'en_US.UTF-8');
      $this->browser = @get_browser(null, true);
      $this->args = new qPageArgs($_SERVER['REQUEST_URI']);

      if ((session_status() == PHP_SESSION_ACTIVE) && (array_key_exists('redirect_from', $_SESSION)))
      {
        $this->cameFrom = $_SESSION['redirect_from'];
        $_SESSION['redirect_from'] = null;
      }
    }

    public function __get($name)
    {
      throw new Exception("__get Property ($name) is not defined");
    }

    public function __set($name, $value)
    {
      throw new Exception("__set Property ($name) is not defined");
    }

    public function getCameFrom()
    {
      return $this->cameFrom;
    }

    public function getArgs()
    {
      return $this->args;
    }

    public function getPage()
    {
      return $this->page;
    }

    public function getStyle()
    {
      return $this->style;
    }

    public function getRoutes()
    {
      return $this->routes;
    }

    public function addRoute($route, $page, $default = false, $sitemapped = true)
    {
      $this->routes[] = array('route' => $route,
        'page' => $page,
        'default' => $default,
        'aliases' => array(),
        'sitemapped' => $sitemapped);
    }

    public function addRouteAlias($alias, $route)
    {
      $found = false;
      $key   = null;

      for ($i=0; $i <= count($this->routes); $i++)
      {
        // Search routes and aliases.
        $regex = '/^'.str_replace('/', '\/',  $this->routes[$i]['route']).'\/*(.*)/i';

        if (preg_match($regex, $route))
        {
          $this->routes[$i]['aliases'][] = array(
            'alias' => mb_strtolower($alias),
            'route' => $route
          );
          $found = true;
          break;
        }
      }

      if (!$found)
      {
        throw new Exception('Route "'.$route.'" for alias "'.$alias.'" not found!');
      }
    }

    public function findRoute($page)
    {
      $ret = null;

      foreach ($this->routes as $route)
      {
        if ($route['page'] == $page)
        {
          $ret = $route;
          break;
        }
      }

      return $ret;
    }

    public static function SgetRoute($page, $preserve = false)
    {
      $fc = self::get();
      return $fc->getRoute($page, $preserve);
    }

    public function getRoute($page, $preserve = false)
    {
      $ret = null;

      if ($route = $this->findRoute($page))
      {
        $cn = 'q'.$page.'Page';
        $ret = $route['route'];

        if ($preserve)
        {
          if ($this->parts)
          {
            foreach ($this->parts as $k => $v)
            {
              $ret .= '/'.$k;
              if (is_array($v))
              {
                $ret .= '/'.$v['raw'];
              }
              else
              {
                $ret .= '/'.$v;
              }
            }
          }
        }

        $ret = preg_replace('/\/+/', '/', $ret);
      }
      else
      {
        $ret = '/';
      }
      return $ret;
    }

    protected function setTypeAndPath($path)
    {
      $knownTypes = array('html', 'txt', 'xml', 'js', 'json', 'qr', 'rss', 'soap', 'wsdl', 'xsd', 'captcha');

      $parts = explode('.', $path);
      if (count($parts) > 1)
      {
        $type = mb_strtolower(array_pop($parts));
        if (in_array($type, $knownTypes))
        {
          $this->type = $type;
          $this->path = implode('.', $parts);
        }
      }
    }

    public function redirect($uri)
    {
      if (session_status() == PHP_SESSION_ACTIVE)
      {
        $_SESSION['redirect_from'] = $_SERVER['REQUEST_URI'];
      }
      fURL::redirect($uri);
      exit(0);
    }

    public function dispatch()
    {
      $fnparts = array();
      $regex   = '';
      $found   = false;

      // Do initial setup
      $this->urlParts  = parse_url($_SERVER['REQUEST_URI']);
      $this->path      = urldecode($this->urlParts['path']);
      $this->setTypeAndPath($this->path);

      // Determine the route
      foreach ($this->routes as $route)
      {
        $matches     = array();
        $this->parts = array();

        if ($route['route'] == '/')
        {
          $regex = '/^\/$/';
        }
        else
        {
          $regex = '/^'.str_replace('/', '\/', $route['route']).'\/*(.*)/i';
        }

        // Search routes and aliases.
        $found = preg_match($regex, $this->path, $matches);

        if (!$found)
        {
          foreach ($route['aliases'] as $alias)
          {
            if ($alias['alias'] == mb_strtolower($this->path))
            {
              $this->redirect($alias['route']);
            }
          }
        }

        if ($found)
        {
          $pc = 'q'.$route['page'].'Page';
          $this->page = $pc::get($route['page'], $this->type);

          if ($matches)
          {
            preg_match_all('/([^\/]+)\/([^\/]+)/i', $matches[1], $this->parts);
            $this->parts = array_combine($this->parts[1], $this->parts[2]);
          }

          // Create sub-arrays for parts that are CSVs
          foreach (array_keys($this->parts) as $k)
          {
            $raw = $this->parts[$k];
            $this->parts[$k] = array();
            $this->parts[$k]['raw']   = $raw;
            $this->parts[$k]['parts'] = explode(',', $raw);
          }

          $this->page->run($this->args);
          break;
        }
      }

      if (!$found)
      {
        $pc = 'q404Page';
        $this->page = $pc::get('404', $this->type);
        $this->page->run($this->args);
      }
    }

    public function enableXedit($route = '/Xedit', $page = 'Xedit')
    {
      $this->addRoute($route, $page);
    }

    public function enableDataTable($route = null, $page = 'DataTable')
    {
      if (!$route)
      {
        $route = qDataTable::$route;
      }
      $this->addRoute($route, $page);
    }
  }
