<?php

class qFormPage extends qSitePage
{
  public function run($args)
  {
    $this->template = '_automatic_Form.tpl.php';
    $table = fRequest::get('table');
    $class = 'q'.fORM::classize($table).'FormBlock';
    $this->addBlock(new $class('FormBlock'));
    parent::run($args);
  }
}
