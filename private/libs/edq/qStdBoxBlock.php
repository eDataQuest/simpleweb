<?php

class qStdBoxBlock extends qBlock
{

  public $tplData = null;

  public function run($args)
  {
    $data = array();


    $this->tplData = new qStdBox();
    $data ['self'] = $this;

    return $data;
  }

}
