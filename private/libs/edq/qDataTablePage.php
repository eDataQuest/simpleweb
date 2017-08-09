<?php

class qDataTablePage extends qPage
{
  public function run($args)
  {
    $this->template = 'shared/DataTable.tpl.php';
    $this->addBlock(new qDataTableBlock());

    parent::run($args);
  }
}
