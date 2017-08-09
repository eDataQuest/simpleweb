<?php
  class qAutomaticActiveRecordBlockPage extends qPage
  {
    public function run($args)
    {
      $ccn = get_called_class();
      throw new Exception($ccn);

      $this->template = '_automatic_Grid.tpl.php';
      $table = fRequest::get('table');
      $class = 'q'.fORM::classize($table).'GridBlock';
      $this->addBlock(new $class('GridBlock'));
      parent::run($args);
    }
  }
