<?php

class qGetterSetter
{
  const DT_STRING  = 0;
  const DT_ARRAY   = 1;
  const DT_INTEGER = 2;
  const DT_BOOL    = 3;

  protected $variables = [];

  const optsHtml = [
      'Enable'   => ['type' => self::DT_BOOL],
      'Template' => ['type' => self::DT_STRING],
      'Caption'  => ['type' => self::DT_STRING],
      'Icon'     => ['type' => self::DT_STRING],
      'Class'    => ['type' => self::DT_STRING],
      'Href'     => ['type' => self::DT_STRING],
  ];

  public function __get($name)
  {
    throw new Exception("__get Property ($name) is not defined");
  }

  public function __set($name, $value)
  {
    throw new Exception("__set Property ($name)->($value) is not defined");
  }

  public function __call($name, $args)
  {
    $retval = null;
    $doRet  = false;

    list($type, $pName) = explode('_', fGrammar::underscorize($name), 2);

    if ($type && $pName)
    {
      $pName = fGrammar::camelize($pName, true);

      switch ($type)
      {
        case 'get':
          $doRet = true;
          if (array_key_exists($pName, $this->variables))
          {
            $retval = $this->variables[$pName];
          }
          else
          {
            throw new Exception(__METHOD__ . ' Property (' . $pName . ') is not defined for getter');
          }
        break;

        case 'set':
          if ((is_array($args)) && (count($args) == 1))
          {
            if (array_key_exists($pName, $this->variables))
            {
              $this->variables[$pName] = $args[0];
              $doRet                   = true;
              $retval                  = $this;
            }
            else
            {
              throw new Exception(__METHOD__ . ' Property (' . $pName . ') is not defined for setter');
            }
          }
          else
          {
            throw new Exception(__METHOD__ . ' Property (' . $pName . ') set with more than one value');
          }
        break;

      }
    }

    if ($doRet)
    {
      return $retval;
    }
  }



  protected static function getDefaultForType($type)
  {
    $ret = null;

    switch ($type)
    {
      case qDataTable::DT_STRING  :
        $ret = null;
      break;

      case qDataTable::DT_ARRAY   :
        $ret = [];
      break;

      case qDataTable::DT_INTEGER :
        $ret = 0;
      break;

      case qDataTable::DT_BOOL    :
        $ret = false;
      break;
    }

    return $ret;
  }

  protected function initVariables($variables)
  {
    // Add in the standard box so it can be defined.
    $sb = qStdBox::Variables;
    $variables = array_merge($variables,$sb);

    foreach ($variables as $key => $val)
    {
      if (array_key_exists('opts', $val))
      {
        if (is_array($val['opts']))
        {
          foreach ($val['opts'] as $k => $v)
          {
            $this->variables[$key.$k] = self::getDefaultforType($v['type']);
          }
        }
      }
      else
      {
        $this->variables[$key] = self::getDefaultforType($val['type']);
      }
    }
  }
}
