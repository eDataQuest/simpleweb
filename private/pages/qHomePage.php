<?php

class qHomePage extends qSitePage
{

  public function run($args)
  {
    $this->template = 'Home.tpl.php';
    $this->setTitle('Home');
    parent::run($args);
  }

}
