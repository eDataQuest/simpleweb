<?php

class qMapquestMapBlock extends qBlock
{
  use qMapquestMapT;

  public function __construct($name = null, $args = [])
  {
    self::define();

    // Set some mapquest defaults.  For details, see:
    // http://leafletjs.com/reference-1.0.0.html#map-option
    $this->_mapdata = qStdLib::deep_array_merge($this->_mapdata, $args);
    $this->_mapdata['id']    = self::getNextId();

    parent::__construct($name);
  }

  public function printMap()
  {
    // The shared map templates use this 'mapdata' variable, do not rename it
    $mapdata = $this->_mapdata;
    include('shared/map_mapquest.tpl.php');
  }

  public function run($args)
  {
    $ret = ['self' => $this];

    return $ret;
  }

}
