<?php

class qMapPage extends qPage
{
  public function run($args)
  {
    $this->template = '_automatic_Map.tpl.php';
    $class = fRequest::get('class');
    $fn = fRequest::get('fn');
    $this->addBlock(new $class('MapBlock',$fn));
    parent::run($args);
  }
}
