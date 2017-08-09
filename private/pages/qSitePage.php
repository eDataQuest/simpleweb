<?php

class qSitePage extends qPage
{

  protected $company = 'Test Company';

  public function setTitle($title)
  {
    parent::setTitle($title.' | '.$this->company);
  }
  public function run($args)
  {
    parent::run($args);
  }

}
