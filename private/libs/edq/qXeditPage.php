<?php

  class qXeditPage extends qPage
  {
    public function run($args)
    {
      $this->template = 'shared/Xedit.tpl.php';
      $this->addBlock(new qXeditBlock());
      parent::run($args);
    }
  }
