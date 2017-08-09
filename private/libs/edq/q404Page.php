<?php

class q404Page extends qPage
{
  protected $tplDefaults = [
    'default' => 'shared/404.tpl.php'
  ];

  public function run($args)
  {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');

    $this->setTitle('Not Found');
    $this->template = '404.tpl.php';

    parent::run($args);
  }
}
