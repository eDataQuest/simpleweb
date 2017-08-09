<?php

class qBlock
{
  protected $_name = null;

  public function __construct($name = null)
  {
    $this->_name = $name ? $name : get_class($this);
  }

  public function __get($name)
  {
    throw new Exception("__get Property ($name) is not defined");
  }

  public function __set($name, $value)
  {
    throw new Exception("__set Property ($name)->($value) is not defined");
  }

  public function getName()
  {
    return $this->_name;
  }

  public function run($args)
  {
  }
}