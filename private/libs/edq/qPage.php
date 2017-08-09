<?php

class qPage
{

  protected $type     = '';
  protected $pageName = '';
  protected $blocks   = array();
  protected $rawData  = array();
  protected $args     = null;
  protected $title    = '';

  protected $tplDefaults = [
    'default' => '_default.php'
  ];

  public $smarty   = null;
  public $tpl      = null;
  public $template = '';

  public function __get($name)
  {
    throw new Exception("__get Property ($name) is not defined");
  }

  public function __set($name, $value)
  {
    throw new Exception("__set Property ($name)->($value) is not defined");
  }

  static public function get($pagename, $type = 'html')
  {
    static $page = null;
    if ($page == null)
    {
      $cn   = get_called_class();
      $page = new $cn($pagename, $type);
    }
    return $page;
  }

  protected function __construct($pagename, $type = 'html')
  {
    $this->pageName = $pagename;
    $this->type     = $type;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function setTitle($title)
  {
    $this->title = $title;
  }

  public function getPageName()
  {
    return $this->pageName;
  }

  public function addBlock($block = null, $name = null)
  {
    if ($block)
    {
      if (is_a($block, 'qBlock'))
      {
        $name                = $name ? $name : $block->getName();
        $this->blocks[$name] = $block;
      }
    }
  }

  protected function getDefaultTemplate()
  {
    $ret = null;

    if (array_key_exists($this->type, $this->tplDefaults))
    {
      $ret = qUtils::smartInclude('tpl'.DS.$this->type.DS.$this->tplDefaults[$this->type]);
    }

    if (!$ret)
    {
      if (array_key_exists('default', $this->tplDefaults))
      {
        $ret = qUtils::smartInclude('tpl'.DS.$this->type.DS.$this->tplDefaults['default']);
      }
    }

    return $ret;
  }

  public function run($args)
  {
    $this->args = $args;
    $pi = null;
    $fn = qUtils::smartInclude('tpl'.DS.$this->type.DS.$this->template);

    if (!$fn)
    {
      $fn = $this->getDefaultTemplate();
    }

    if (!$fn)
    {
      $fn = qUtils::smartInclude('tpl'.DS.'notfound.php');
    }

    if ($fn)
    {
      $pi = pathinfo(realpath($fn));
      $fn  = $pi['dirname'].DS.$pi['basename'];
    }
    set_include_path(get_include_path() . PATH_SEPARATOR . $pi['dirname'].DS);

    if($this->tpl == null)
    {
      $this->tpl = new qTemplating($pi['dirname'], $pi['basename']);
    }

    $this->tpl->set('jquery_style', qFrontController::get()->getStyle());
    $this->tpl->set('_args', $args);

    foreach ($this->blocks as $blockName => $block)
    {
      $this->tpl->set($blockName, $block->run($args));
    }

    foreach ($this->rawData as $k => $v)
    {
      $this->tpl->set($k, $v);
    }

    // Move this here so the BLOCK can change it only if its blank!
    $this->tpl->set('title', $this->getTitle());

    $this->tpl->place();
  }

  static public function autoClassCreator($class_name)
  {
    if (strpos($class_name, 'GridBlock') !== false)
    {
      eval("class $class_name extends qGridBlock{}");
    }

    if (strpos($class_name, 'MapBlock') !== false)
    {
      eval("class $class_name extends qMapBlock{}");
    }

    if (strpos($class_name, 'FormBlock') !== false)
    {
      eval("class $class_name extends qFormBlock{}");
    }
  }

}