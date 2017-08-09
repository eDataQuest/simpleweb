<?php

class qMap extends qGetterSetter
{

 use qMapquestMapT;
   const Variables = [
  ];

  public function __construct()
  {
    self::define();
    $this->_mapdata['id'] = self::getNextId();

    $this->initVariables(self::Variables);
    $this->setTitleCaption('Standard Map');
    $this->setTitleIcon('fa-cogs');
    $this->setBoxTemplate('shared/Box')->setBoxEnable(true);
    $this->setBoxTopLeftTemplate('shared/Caption')->setBoxTopLeftEnable(true);
    $this->setBoxMiddleTemplate('shared/Map')->setBoxMiddleEnable(true);
  }
}
