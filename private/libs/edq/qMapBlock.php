<?php

class qMapBlock extends qBlock
{

  public $ActiveRecord = null;
  protected $opts      = [];
  protected $method    = null;
  public $pk           = null;
  public $tplData      = null;

  public function __construct($name = null, $method = 'qMapDefault')
  {

    $this->opts   = $opts;
    $this->method = $method;
    parent::__construct($name);
  }

  public function run($args)
  {

    $data     = array();
    $cn       = get_called_class();
    $db_class = str_replace('MapBlock', '', $cn);
    if ($db_class[0] == 'q')
    {

      $db_class = substr($db_class, 1);
    }
    if ($this->pk)
    {
      $pk = $this->pk;
    }
    else
    {
      $pk = json_decode(fRequest::get('pk'), true);
    }
    $this->ActiveRecord = new $db_class($pk);
    $fn                 = $this->method;
    $this->tplData      = $cn::$fn($this->ActiveRecord);

    // Set the Title name if it was not previously set via the page.
    $page = qFrontController::get()->getPage();
    if (!$page->getTitle())
    {
      $page->setTitle($this->tplData->getTitleCaption());
    }

    if (class_exists('qBreadCrumb'))
    {
      qBreadCrumb::push($this->tplData->getTitleCaption());
    }
    $data ['self'] = $this;


    return $data;
  }

  static public function qMapDefault($ActiveRecord, $opts = [])
  {
    $map = new qMap();

    return $map;
  }

}
