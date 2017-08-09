<?php

class qExtendedActiveRecord extends fActiveRecord
{
  protected $qXtraFields = null;

  public function __construct($key = null)
  {
    // Fetch Extra Fields if they exist.
    $cn = 'q'.get_class($this).'XF';
    if(class_exists($cn))
    {
      $this->qXtraFields = $cn::qExtraFields($this);
    }

    parent::__construct($key);
  }

  protected function qGetValidationField($field)
  {
    $ret = false;
    if ($this->qXtraFields)
    {
      $ret = $this->qXtraFields->itemExists($field) ? $this->qXtraFields->getItem($field) : $def;
    }

    return $ret;
  }

  public function qGetColumnInfo()
  {
    $class  = get_class($this);
    $schema = fORMSchema::retrieve($class);
    $table  = fORM::tablize($class);

    return $schema->getColumnInfo($table);
  }

  public function qGetPlaceholder($field, $ret = false)
  {
    if ($vf = $this->qGetValidationField($field))
    {
      if (array_key_exists('type', $vf))
      {
        $ret = qValidate::getConstraintPlaceholder($vf['type'], $ret);
      }
      $ret = array_key_exists('placeholder', $vf) ? $vf['placeholder'] : $ret;
    }

    return $ret;
  }

  public function qGetEditLabel($field, $defaultToFieldname = true)
  {
    $ret = $defaultToFieldname ? fGrammar::humanize($field) : '';

    if ($vf = $this->qGetValidationField($field))
    {
      $ret = array_key_exists('label', $vf) ? $vf['label'] : $ret;
    }

    return $ret;
  }

  public function qGetValidationFieldMask($field)
  {
    $ret = false;
    if ($vf = $this->qGetValidationField($field))
    {
      if (array_key_exists('type', $vf))
      {
        $ret = qValidate::getConstraintMask($vf['type'], $ret);
      }
    }

    return $ret;
  }

  public function qGetValue($field, $which = 'Disp')
  {
    $ret = null;

    $getFn = 'get' . fGrammar::camelize($field, TRUE);
    $ci = $this->qGetColumnInfo();
    if (array_key_exists($field, $ci))
    {
      $fmtFn = 'qGetField'.$which.'Format';
      if ($fmt = $this->$fmtFn($field))
      {
        $fmtFn = 'format'.fGrammar::camelize($ci[$field]['type'], true);
        $ret = qValueFormat::$fmtFn($this->$getFn(), $fmt);
      }
      else
      {
        $ret = qValueFormat::formatwithColumnInfo($this->$getFn(), $ci[$field], $which);
      }
    }

    return $ret;
  }

  public function qGetFieldType($name, $ret = false)
  {
    return $this->qXtraFields ? $this->qXtraFields->getItemVal($name, 'type', $ret) : null;
  }

  public function qGetFieldRequired($name, $ret = false)
  {
    return $this->qXtraFields ? $this->qXtraFields->getItemVal($name, 'required', $ret) : null;
  }

  public function qGetFieldEditFormat($name, $ret = false)
  {
    return $this->qXtraFields ? $this->qXtraFields->getItemVal($name, 'editFormat', $ret) : null;
  }

  public function qGetFieldDispFormat($name, $ret = false)
  {
    return $this->qXtraFields ? $this->qXtraFields->getItemVal($name, 'dispFormat', $ret) : null;
  }

  public function qGetPlaces($name, $ret = false)
  {
    return $this->qXtraFields ? $this->qXtraFields->getItemVal($name, 'places', $ret) : null;
  }


  public function store($force_cascade = false, $ignorePK = true)
  {
    // Only validate something if it changed.
    foreach ($this->old_values as $fieldName => $oldValue)
    {
      $getFn = 'get' . fGrammar::camelize($fieldName, true);
      $setFn = 'set' . fGrammar::camelize($fieldName, true);
      $val   = trim($this->$getFn());

      if ($fmt = $this->qGetFieldEditFormat($fieldName))
      {
        $ts = new fTimestamp($val);
        $val = $ts->format($fmt);
      }

      if (($fieldType = $this->qGetFieldType($fieldName)) && ($val))
      {
        if ($pattern = qValidate::getConstraintPattern($fieldType))
        {
          if (1 !== preg_match($pattern, $val))
          {
            throw new qEditingException(qValidate::getConstraintMessage($fieldType), ['field' => $fieldName]);
          }
        }

        // call validateToStoreFN
        $ffn = 'validateToStore' . fGrammar::camelize($fieldType, true);
        if (method_exists('qValidate', $ffn))
        {
          if (!qValidate::$ffn($val))
          {
            throw new qEditingException(qValidate::getConstraintMessage($fieldType), ['field' => $fieldName]);
          }
        }

        // call formatToStoreFN
        $ffn = 'formatToStore' . fGrammar::camelize($fieldType, true);
        if (method_exists('qValidate', $ffn))
        {
          $this->$setFn(qValidate::$ffn($val));
        }
      }
    }

    // Hackish.  Don't actually store if the PK is not set unless we were told to ignore that.
    if ($this->isPkSet() || $ignorePK)
    {
      parent::store();
    }
  }

  public function qThrowForInvalid($fields = [])
  {
    // If $fields is not empty, only the named fields will cause an exception if validation fails.
    // If $fields is empty, any fields failing validation will cause an exception.
    $vres = $this->validate(true, true);
    if (count(array_keys($vres)))
    {
      $comb = array_intersect(array_keys($vres), $fields);
      if (count($comb) || !count($fields))
      {
        $k     = array_keys($vres)[0];
        $field = explode(',', $k);
        $edata = [
          'field' => $field,
        ];
        throw new qEditingException($vres[$k], $edata);
      }
    }
  }

  protected function isPkSet()
  {
    $ret = true;

    foreach ($this->qGetPk() as $k => $v)
    {
      if ($v === null)
      {
        $ret = false;
        break;
      }
    }

    return $ret;
  }
}
