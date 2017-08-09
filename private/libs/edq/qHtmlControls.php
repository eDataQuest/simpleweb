<?php
class qHtmlControls extends qGetterSetter
{
  protected static $unique_form_number = 1;

  const Variables = [
    'Inputs'        => ['type' => self::DT_ARRAY],
    'Form'          => ['type' => self::DT_STRING],
    'Table'         => ['type' => self::DT_STRING],
    'ActiveRecord'  => ['type' => self::DT_STRING],
    'Pk'            => ['type' => self::DT_STRING],
    'Redirect'      => ['type' => self::DT_STRING],
    'BtnAdd'        => ['opts' => self::optsHtml],
    'BtnDelete'     => ['opts' => self::optsHtml],
    'BtnCancel'     => ['opts' => self::optsHtml],
    'BtnDone'       => ['opts' => self::optsHtml],
    'DefaultCols'   => ['type' => self::DT_ARRAY],
  ];

  const hrefXedit = __CLASS__.'::hrefXedit';

  public static function hrefXedit($column_value, $ActiveRecord,$me)
  {
    return '<a href="' . $me->getDrillHref() . '&pk=' . htmlspecialchars(json_encode($ActiveRecord->qGetPk())) . '">' . $column_value . '</a>';
  }

  public static function define()
  {
    if (!defined('XEDIT'))
    {
      define('XEDIT', true);
    }
  }

  public function __construct($ActiveRecord)
  {
    self::define();
    $this->initVariables(self::Variables);

    // Set Default Values
    $this->setForm(self::$unique_form_number++);
    $this->setTable(fORM::tablize(get_class($ActiveRecord)));
    $this->setActiveRecord($ActiveRecord);
    $this->setPk(json_encode($ActiveRecord->qGetPk()));

    if(class_exists('qBreadCrumbs'))
    {
      $this->setRedirect(qBreadCrumb::getReferer());
    }
    else
    {
      $this->setRedirect($_SERVER["HTTP_REFERER"]);
    }

    $this->setBoxTemplate('shared/Box')->setBoxEnable(true);
    $this->setBoxTopLeftTemplate('shared/Caption')->setBoxTopLeftEnable(true);
    $this->setBoxTopRightTemplate('shared/InputDelete')->setBoxTopRightEnable(true);
    $this->setBoxMiddleTemplate('shared/Input')->setBoxMiddleEnable(true);
    $this->setBoxBottomTemplate('shared/InputAdd')->setBoxBottomEnable(true);

    $this->setTitleCaption(fGrammar::humanize(fGrammar::singularize($this->getTable())))->setTitleEnable(true)
      ->setTitleIcon('fa-user')
      ->setBtnAddCaption('+ Add')->setBtnAddEnable(true)
      ->setBtnAddClass('btn btn-primary pull-right col-xs-2')
      ->setBtnCancelClass('btn btn-border-danger col-xs-2 col-sm-10 q-box-button')
      ->setBtnDeleteCaption('Delete')->setBtnDeleteEnable(true)
      ->setBtnCancelCaption('Cancel')->setBtnCancelEnable(true)
      ->setBtnDoneCaption('Done')->setBtnDoneEnable(true);
  }

  public static function makeField($type, $opts = [])
  {
    // This needs to ensure that $type is valid, along with all the key names in $opts
    // and that all keys for a given $opts['type'] are present and valid.
    $ret = ['type' => $type, 'data' => []];
    $data = [];

    foreach ($opts as $k => $v)
    {
      if ($v !== null)
      {
        switch (strtolower($k))
        {
          case 'name':
            $data['col'] = $v;
          break;

          default:
            $data[$k] = $v;
          break;
        }
      }
    }
    $ret['data'] = $data;

    return $ret;
  }

  public static function makeXeditHiddenField($name = null)
  {
    return self::makeField('xedit', ['type' => 'hidden', 'col' => $name]);
  }

  public static function makeXeditTextField($name = null, $size = null)
  {
    return self::makeField('xedit', ['type' => 'text', 'col' => $name, 'size' => $size]);
  }

  public static function makeXeditTextAreaField($name = null, $rows = null, $size = null)
  {
    return self::makeField('xedit', ['type' => 'textarea', 'col' => $name, 'size' => $size, 'rows' => $rows]);
  }

  public static function makeXeditSelectField($name = null, $list = null, $size = null)
  {
    return self::makeField('xedit', ['type' => 'select', 'col' => $name, 'size' => $size, 'list' => $list]);
  }

  public static function makeXeditCheckboxField($name = null, $size = null)
  {
    return self::makeField('xedit', ['type' => 'checkbox', 'col' => $name, 'size' => $size]);
  }

  public static function makeSimpleCheckboxField($name = null, $size = null)
  {
    return self::makeField('simple', ['type' => 'checkbox', 'col' => $name, 'size' => $size]);
  }

  public static function makeSimpleLabel($value, $size = null)
  {
    return self::makeField('simple', ['type' => 'label', 'size' => $size, 'value' => $value]);
  }

  public static function makeRaw($raw, $size = null)
  {
    return self::makeField('simple', ['type' => 'raw', 'size' => $size, 'value' => $raw]);
  }

  public function addRow()
  {
    $this->setInputs(array_merge($this->getInputs(), [func_get_args()]));
  }

  public function getInputOptions($input)
  {
    $options               = [];

    if (!in_array($input['type'], ['label', 'raw']))
    {
      $options['class']       = "editable";
      $options['data-name']   = $input['col'];
      $options['data-form']   = $this->getForm();
      $options['data-value']  = $this->getActiveRecord()->qGetValue($input['col'], 'Edit');
      $options['data-pk']     = $this->getPk();
      $options['data-params'] = json_encode(['action' => 'update', 'table' => $this->getTable()]);
    }
    else
    {
      $options = $input;
    }

    // Control Options For Specific Control Types
    switch ($input['type'])
    {
      case 'select':
        $options['data-type']   = $input['type'];
        $options['data-source'] = $input['list'];
      break;

      case 'hidden':
        $options['data-type']  = 'text';
        $options['style']      = "display:none;";
      break;

      case 'textarea':
        $ar   = $this->getActiveRecord();

        $options['data-type']        = 'textarea';
        $options['data-rows']        = $input['rows'];
        $options['data-placeholder'] = $ar->qGetPlaceholder($input['col']);
      break;

      // do nothing with these, they are not actualy 'fields'
      case 'raw':
      case 'label':
      break;

      default:
        $ar       = $this->getActiveRecord();
        if ($maskArgs = $ar->qGetValidationFieldMask($input['col']))
        {
          if (array_key_exists('mask', $maskArgs))
          {
            $options['data-mask'] = $maskArgs['mask'];
          }

          if (array_key_exists('mask-extra', $maskArgs))
          {
            $options['data-mask-extra'] = $maskArgs['mask-extra'];
          }
        }

        $options['data-places']      = $ar->qGetPlaces($input['col']);
        $options['data-type']        = 'text';
        $options['data-placeholder'] = $ar->qGetPlaceholder($input['col']);
      break;
    }


    return $options;
  }

  public static function getSelectDataSource($tbl, $key, $val, $where = null)
  {
    $recs   = fRecordSet::build(fORM::classize($tbl), $where, [$val => 'asc']);
    $kv     = [];
    $getkey = 'get' . fGrammar::camelize($key, TRUE);
    $getval = 'get' . fGrammar::camelize($val, TRUE);
    foreach ($recs as $rec)
    {
      $kv[] = ['value' => (int) $rec->$getkey(), 'text' => $rec->$getval()];
    }
    $str = json_encode($kv);

    return $str;
  }

  public function recordExists()
  {
    return $this->getActiveRecord()->exists();
  }
}