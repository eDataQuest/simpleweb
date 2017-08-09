<?php

class qDataTable extends qGetterSetter
{

  protected static $unique_table_number = 1;
  static $route                         = '/DataTable';

  const Variables = [
      'Id'      => ['type' => self::DT_STRING],
      'Class'   => ['type' => self::DT_STRING],
      'Fn'      => ['type' => self::DT_STRING],
      'Url'     => ['type' => self::DT_STRING],
      'Table'   => ['type' => self::DT_STRING],
      'Filter'  => ['type' => self::DT_ARRAY],
      'SortBy'  => ['type' => self::DT_INTEGER],
      'Columns' => ['type' => self::DT_ARRAY],
      'BtnAdd'  => ['opts' => self::optsHtml],
      'DtServerSide'  => ['type' => self::DT_BOOL],
      'DtBtnCopy'     => ['type' => self::DT_BOOL],
      'DtBtnPrint'    => ['type' => self::DT_BOOL],
      'DtBtnCsv'      => ['type' => self::DT_BOOL],
      'DtBtnPdf'      => ['type' => self::DT_BOOL],
      'DtBtnExcel'    => ['type' => self::DT_BOOL],
      'DtProcessing'  => ['type' => self::DT_BOOL],
      'DtResponsive'  => ['type' => self::DT_BOOL],
      'DtSaveState'   => ['type' => self::DT_BOOL],
      'DtOrder'       => ['type' => self::DT_ARRAY],
      'DtLengthMenu'  => ['type' => self::DT_ARRAY],
      'DefaultCols'   => ['type' => self::DT_ARRAY],
      'DefaultFilter' => ['type' => self::DT_ARRAY],
      'DrillHref'     => ['type' => self::DT_STRING],
  ];

  protected $column_id = 0;

  public static function define()
  {
    if (!defined('DATATABLES'))
    {
      define('DATATABLES', true);
    }
  }
  protected $ActiveRecord;
  public function __construct($ActiveRecord)
  {
    self::define();
    $this->ActiveRecord = $ActiveRecord;
    // Determine who called us.
    $class = get_class($ActiveRecord);
    $table = fORM::tablize($class);
    $stack = (new Exception())->getTrace();


    $this->initVariables(self::Variables);
    $this->setClass($class);
    $this->setFn($stack[1]['function']);
    $this->setUrl(self::$route);
    $this->setTable($table);
    $this->setId('dt_' . $this->getTable() . self::$unique_table_number++);
    $this->setTitleCaption(fGrammar::humanize($table));
    $this->setTitleIcon('fa-user');
    $this->setBoxTemplate('shared/Box')->setBoxEnable(true);
    $this->setBoxTopLeftTemplate('shared/Caption')->setBoxTopLeftEnable(true);
    $this->setBoxTopRightTemplate('shared/DataTableAdd')->setBoxTopRightEnable(true);
    $this->setBoxMiddleTemplate('shared/DataTable')->setBoxMiddleEnable(true);
    $this->setBtnAddCaption('+ Add ' . fGrammar::humanize(fGrammar::singularize($table)));
    $this->setBtnAddHref('/Form?table=' . $table);
    $this->setDrillHref('/Form?table=' . $table);

    // Data Tables Options....
    $this->setDtServerSide(true);
    $this->setDtProcessing(true);
    $this->setDtResponsive(true);
    $this->setDtSaveState(true);
    $this->setDtOrder([[$this->getSortBy(), 'asc']]);
    $this->setDtLengthMenu([[10, 25, 50, -1], [10, 25, 50, 'All Records']]);
    //$this->setDtBtnPdf(true);
  }


  public function addColumns($columns)
  {
    foreach ($columns as $opts)
    {
      $this->addColumn($opts);
    }
  }

  public function addColumn($opts)
  {
    if (strstr($opts['db'], '.'))
    {

      // This is so ugly there MUST be a flourish way...
      $parts  = explode('.', $opts['db']);
      $parts2 = explode('{', $parts[0]);
      $tab    = $parts2[0];
      $col    = $parts[1];
      // We can use normal ORM
      $tab    = fGrammar::singularize($tab);
      $label  = fGrammar::humanize($tab);
    }
    else
    {
      $label = $this->ActiveRecord->qGetEditLabel($opts['db']);
    }


    $defaults = [
        'dt'    => $this->column_id++,
        'label' => $label
    ];

    $this->setColumns(array_merge($this->getColumns(), [array_merge($defaults, $opts)]));
  }

  public function makeData()
  {
    $request = $_GET;
    $columns = $this->getColumns();


    // Dump whats above when you can.

    $table = fGrammar::singularize($this->getTable());
    $table = fGrammar::camelize($table, TRUE);

    $limit = null;
    $page  = null;
    if (isset($request['start']) && $request['length'] != -1)
    {
      $limit = intval($request['length']);
      $start = intval($request['start']);
      $page  = ($start / $limit) + 1;
    }
    $order  = $this->order($request, $columns);
    $where  = $this->filter($request, $columns);
    //error_log('************'.print_r($where,true));
    $filter = $tot    = fRecordSet::tally($table, $this->getFilter());
    if(count($where)>0)
    {
      $filter = fRecordSet::tally($table, array_merge($where, $this->getFilter()));
    }
    $obj    = fRecordSet::build($table, array_merge($where, $this->getFilter()), $order, $limit, $page);


    return array(
        "draw"            => intval($request['draw']),
        "recordsTotal"    => intval($tot),
        "recordsFiltered" => intval($filter),
        "data"            => $this->data_output($columns, $obj)
    );
  }

  protected function filter($request, $columns)
  {
    $dtColumns = $this->pluck($columns, 'dt');
    $where     = array();
    $or_key    = array();
    $or_val    = array();
    if (isset($request['search']) && $request['search']['value'] != '')
    {
      $str = $request['search']['value'];


      for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++)
      {
        $requestColumn = $request['columns'][$i];
        $columnIdx     = array_search($requestColumn['data'], $dtColumns);
        $column        = $columns[$columnIdx];

        if ($requestColumn['searchable'] == 'true')
        {
          $or_key[] = $column['db'] . '~';
          $or_val[] = $str;
        }
      }
    }
    if (count($or_key) > 0)
    {
      $where = array_merge($where, array(implode('|', $or_key) => $or_val));
    }

    // Individual column filtering
    for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++)
    {
      $requestColumn = $request['columns'][$i];
      $columnIdx     = array_search($requestColumn['data'], $dtColumns);
      $column        = $columns[$columnIdx];

      $str = $requestColumn['search']['value'];

      if ($requestColumn['searchable'] == 'true' && $str != '')
      {
        $where = array_merge($where, array($column['db'] . '~' => $str));
      }
    }
    return $where;
  }

  protected function order($request, $columns)
  {
    $order = array();

    if (isset($request['order']) && count($request['order']))
    {
      $orderBy   = array();
      $dtColumns = $this->pluck($columns, 'dt');

      for ($i = 0, $ien = count($request['order']); $i < $ien; $i++)
      {
        // Convert the column index into the column data property
        $columnIdx     = intval($request['order'][$i]['column']);
        $requestColumn = $request['columns'][$columnIdx];

        $columnIdx = array_search($requestColumn['data'], $dtColumns);
        $column    = $columns[$columnIdx];

        if ($requestColumn['orderable'] == 'true')
        {
          $dir   = $request['order'][$i]['dir'] === 'asc' ?
                  'asc' :
                  'desc';
          $order = array_merge($order, array($column['db'] => $dir));
        }
      }
    }

    return $order;
  }

  protected function data_output($columns, $obj)
  {

    $out = array();
    foreach ($obj as $rec)
    {
      $row = array();
      for ($j = 0, $jen = count($columns); $j < $jen; $j++)
      {
        $column = $columns[$j];
        $c      = $column['db'];
        if (strstr($c, '.'))
        {

          // This is so ugly there MUST be a flourish way...
          $parts  = explode('.', $c);
          $parts2 = explode('{', $parts[0]);
          $tab    = $parts2[0];
          $col    = $parts[1];
          // We can use normal ORM
          $tab  = fGrammar::singularize($tab);
          $tab  = 'create' . fGrammar::camelize($tab, TRUE);
          $data = $rec->$tab()->qGetValue($col, 'Disp');
        }
        else
        {
          $data = $rec->qGetValue($c, 'Disp');
        }

        $row[$column['dt']] = htmlspecialchars($data);

        if (array_key_exists('cb',$column))
        {
          $row[$column['dt']] = call_user_func($column['cb'],htmlspecialchars($data), $rec,$this);
        }
      }
      $out[] = $row;
    }
    return $out;
  }

  protected function pluck($a, $prop)
  {
    $out = array();

    for ($i = 0, $len = count($a); $i < $len; $i++)
    {
      $out[] = $a[$i][$prop];
    }

    return $out;
  }

  public function getOpts()
  {

    $buttons = [];
    if ($this->getDtBtnPdf())
    {
      $buttons[] = [
          'extend'        => 'pdf',
          'text'          => '<i class="fa fa-file-pdf-o"></i> PDF',
          'titleAttr'     => 'PDF',
          'className'     => 'btn btn-default btn-sm',
          'exportOptions' => ['columns' => ':visible']
      ];
    }

    if ($this->getDtBtnCopy())
    {
      $buttons[] = [
          'extend'    => 'copy',
          'text'      => '<i class="fa fa-files-o"></i> Copy',
          'titleAttr' => 'Copy',
          'className' => 'btn btn-default btn-sm'
      ];
    }

    if ($this->getDtBtnCsv())
    {
      $buttons[] = [
          'extend'        => 'csv',
          'text'          => '<i class="fa fa-files-o"></i> CSV',
          'titleAttr'     => 'CSV',
          'className'     => 'btn btn-default btn-sm',
          'exportOptions' => ['columns' => ':visible']
      ];
    }

    if ($this->getDtBtnExcel())
    {
      $buttons[] = [
          'extend'        => 'excel',
          'text'          => '<i class="fa fa-files-o"></i> Excel',
          'titleAttr'     => 'Excel',
          'className'     => 'btn btn-default btn-sm',
          'exportOptions' => ['columns' => ':visible']
      ];
    }

    if ($this->getDtBtnPrint())
    {
      $buttons[] = [
          'extend'        => 'print',
          'text'          => '<i class="fa fa-print"></i> Print',
          'titleAttr'     => 'Print',
          'className'     => 'btn btn-default btn-sm',
          'exportOptions' => ['columns' => ':visible']
      ];
    }

    $opts = [
        'serverSide' => $this->getDtServerSide(),
        'ajax'       => $this->getUrl() . '.json?class=' . $this->GetClass() . '&fn=' . $this->GetFn().'&filter='.json_encode($this->getFilter()),
        'processing' => $this->getDtProcessing(),
        'responsive' => $this->getDtResponsive(),
        'stateSave'  => $this->getDtSaveState(),
        'order'      => $this->getDtOrder(),
        'lengthMenu' => $this->getDtLengthMenu(),
        'buttons'    => [$buttons]
    ];
    return $opts;
  }

}
