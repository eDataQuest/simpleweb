<?php

class qFormBlock extends qBlock
{
  protected $ActiveRecord = null;
  protected $opts         = [];
  protected $method       = null;
  public $tplData         = null;

  public function __construct($name = null, $method = 'qFormDefault', $opts = [])
  {
    $this->opts   = $opts;
    $this->method = $method;
    parent::__construct($name);
  }

  public function run($args)
  {
    $data = array();
    $cn   = get_called_class();

    $db_class = str_replace('FormBlock', '', $cn);
    if ($db_class[0] == 'q')
    {
      $db_class = substr($db_class, 1);
    }
    $pk = fRequest::get('pk');

    $this->ActiveRecord = new $db_class(json_decode($pk, true));
    $fn                 = $this->method;
    $this->tplData      = $cn::$fn($this->ActiveRecord);

    if (class_exists('qBreadCrumb'))
    {
      qBreadCrumb::push($this->tplData->getTitleCaption());
    }
    $data ['self'] = $this;

    return $data;
  }

  static public function qFormDefault($ActiveRecord)
  {
    $hc = new qHtmlControls($ActiveRecord);
    foreach ($ActiveRecord->qGetColumnInfo() as $k => $v)
    {
      $hc->addRow(qHtmlControls::makeXeditTextField($k));
    }

    return $hc;
  }

}
