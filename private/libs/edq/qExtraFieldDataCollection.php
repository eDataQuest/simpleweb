<?php
  class qExtraFieldDataCollection
  {
    protected $_parent  = null;
    protected $_colinfo = [];
    protected $_items   = [];

    public function __construct($parent, $items = [])
    {
      if ($parent instanceof qExtendedActiveRecord)
      {
        $this->_parent  = $parent;
        $this->_colinfo = $this->_parent->qGetColumnInfo();

        foreach ($items as $k => $v)
        {
          if (in_array($k, array_keys($this->_colinfo)))
          {
            $this->addItem($k, $v);
          }
        }
      }
      else
      {
        throw new Exception(__CLASS__.' requires a qExtendedActiveRecord');
      }
    }

    public function itemExists($name)
    {
      return in_array($name, array_keys($this->_items));
    }

    public function getItem($name, $def = null)
    {
      return $this->itemExists($name) ? $this->_items[$name] : $def;
    }

    public function getItemVal($name, $field, $def = null)
    {
      $ret = $def;

      if ($item = $this->getItem($name, false))
      {
        if (array_key_exists($field, $item))
        {
          $ret = $item[$field];
        }
      }

      return $ret;
    }

    public function addItem($name, $val)
    {
      // ensure name is valid
      if (in_array($name, array_keys($this->_colinfo)))
      {
        if (is_array($val))
        {
          foreach (array_keys($val) as $k)
          {
            switch ($k)
            {
              case 'required':
                if (is_bool($val[$k]))
                {
                  $this->_items[$name][$k] = $val[$k];
                }
                else
                {
                  throw new Exception('Field "required" must be a boolean.');
                }
              break;

              case 'type':
                if (in_array($val[$k], qValidate::getTypes()))
                {
                  $this->_items[$name][$k] = $val[$k];

                  if (($val[$k] == 'gplaces') && (!defined('GPLACES')))
                  {
                    define('GPLACES', true);
                  }

                  $this->_items[$name]['dispFormat'] = qValidate::getConstraintDispFormat($val[$k]);
                  $this->_items[$name]['editFormat'] = qValidate::getConstraintEditFormat($val[$k]);
                }
                else
                {
                  throw new Exception('Field "type" must be a valid qValidate type.  '.$val[$k].' is not.');
                }
              break;

              case 'editFormat':
              case 'dispFormat':
                // only valid in qValidate
              break;

              case 'label':
              case 'placeholder':
              case 'places':
                // any data ok in these.
                $this->_items[$name][$k] = $val[$k];
              break;

              default:
                throw new Exception('Unknown field "'.$k.'" in addItem');
              break;
            }
          }
        }
        else
        {
          throw new Exception('addItem can only be an array');
        }
      }
      else
      {
        throw new Exception('"'.$name.'" is not a valid field of '.get_class($this));
      }
      return $this;
    }
  }