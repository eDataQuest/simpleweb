<?php

class qStdBox extends qGetterSetter
{

  const Variables = [
      'Title'          => ['opts' => self::optsHtml],
      'Box'            => ['opts' => self::optsHtml],
      'BoxTopLeft'     => ['opts' => self::optsHtml],
      'BoxTopRight'    => ['opts' => self::optsHtml],
      'BoxMiddle'      => ['opts' => self::optsHtml],
      'BoxBottom'      => ['opts' => self::optsHtml],
  ];

  public function __construct()
  {
    $this->initVariables(self::Variables);
    $this->setTitleCaption('Standard Box');
    $this->setTitleIcon('fa-cogs');
    $this->setBoxTemplate('shared/Box')->setBoxEnable(true);
    $this->setBoxTopLeftTemplate('shared/Caption')->setBoxTopLeftEnable(true);
  }

}
