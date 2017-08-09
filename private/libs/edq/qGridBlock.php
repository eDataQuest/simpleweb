<?php

class qGridBlock extends qBlock
{

  protected $ActiveRecord = null;
  protected $opts         = [];
  protected $method       = null;
  public $tplData         = null;

  public function __construct($name = null, $method = 'qGridDefault', $opts = [])
  {
    $this->opts   = $opts;
    $this->method = $method;
    parent::__construct($name);
  }

  public function run($args)
  {
    $data     = array();
    $cn       = get_called_class();
    $db_class = str_replace('GridBlock', '', $cn);
    if ($db_class[0] == 'q')
    {

      $db_class = substr($db_class, 1);
    }
    $this->ActiveRecord = new $db_class();
    $fn                 = $this->method;
    $this->tplData      = $cn::$fn($this->ActiveRecord, $this->opts);

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

  static public function qGridDefault($ActiveRecord, $opts = [])
  {
    $dt = new qDataTable($ActiveRecord);
    if (array_key_exists('filter', $opts))
    {
      $dt->setFilter($opts['filter']);
    }

    foreach ($ActiveRecord->qGetColumnInfo() as $k => $v)
    {
      $dt->addColumn(['db' => $k, 'cb' => qHtmlControls::hrefXedit]);
    }
    return $dt;
  }

}
