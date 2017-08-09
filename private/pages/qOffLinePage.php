<?php

class qOffLinePage extends qSitePage
{

  public function run($args)
  {
    $this->template = 'OffLine.tpl.php';
    $this->setTitle('Off Line');
    parent::run($args);
  }

}
