<?php

class qAdminPage extends qSitePage
{
  public function run($args)
  {
    $this->template = 'Admin.tpl.php';
    $this->setTitle('Admin');
    $this->rawData['tables'] = fORMSchema::retrieve()->getTables();
    parent::run($args);

  }
}
