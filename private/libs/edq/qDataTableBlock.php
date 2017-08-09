<?php

class qDataTableBlock extends qBlock
{

  public function run($args)
  {

    $data        = array();
    $class       = fRequest::get('class');
    $block_class = 'q' . $class . 'GridBlock';

    $fn           = fRequest::get('fn');
    $filter       = json_decode(fRequest::get('filter'), true);
    $opts         = ['filter' => $filter];
    $ActiveRecord = new $class();

    $data['ajax'] = $block_class::$fn($ActiveRecord, $opts)->makeData();
    return $data;
  }

}
