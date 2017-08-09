<?php

class qGridPage extends qSitePage
{
  public function run($args)
  {
    $this->template = '_automatic_Grid.tpl.php';
    $table = fRequest::get('table');
    $class = 'q'.fORM::classize($table).'GridBlock';
    $this->addBlock(new $class('GridBlock'));
    parent::run($args);
  }
}
