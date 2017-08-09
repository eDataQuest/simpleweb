<?php

class qTemplating extends fTemplating
{
  // This includes a Getter Setter Definde include
  public function includeGsTpl($block, $pName)
  {
    $this_block = $this->getThisBlock($block);

    $tplData    = $this_block['self']->tplData;
    $enableFn   = 'get' . $pName . 'Enable';

    if ($tplData->$enableFn())
    {
      $lookupFn = 'get' . $pName . 'Template';
      $file    = fGrammar::underscorize($tplData->$lookupFn());
      if ($file)
      {
        // $tplData is the variable that this template can access...
        include($file . '.tpl.php');
      }
    }
  }

  public function includeFileTpl($block, $file)
  {
    $this_block = $this->getThisBlock($block);
    $tplData    = $this_block['self']->tplData;
    include($file . '.tpl.php');
  }

  // The call signature for the Map template needs to look like this..
  public function includeMapTpl($block)
  {
    $this_block = $this->getThisBlock($block);

    $mapdata = $this_block['self']->_mapdata;
    include('shared/map_mapquest.tpl.php');
  }

  // is there a better way to test if this is a object or string?
  protected function getThisBlock($block)
  {
    $ret = $block;
    if (is_string($block))
    {
      $ret = $this->get($block);
    }

    return $ret;
  }
}
