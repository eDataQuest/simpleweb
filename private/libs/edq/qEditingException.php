<?php
  class qEditingException extends Exception
  {
    protected $data = [];

    public function __construct($msg = '', $data = [], $prev = null)
    {
      // default is to requeue the work unit, send nagios a warning.
      $data = array_merge([
        'code'  => 0,
        ],
        $data);

      $this->data = $data;

      parent::__construct($msg, $data['code'], $prev);
    }

    public function getField()
    {
      return array_key_exists('field', $this->data) ? $this->data['field'] : null;
    }

    public function getData()
    {
      return $this->data;
    }
  }


