<?php
  class qDateInterval extends DateInterval
  {
    public $frac = 0;

    static function createFromDateInterval($di, $frac = 0)
    {
      // DateInterval will not allow construction of an empty interval,
      // nor an interval of just 1 second.  So we create one of one day
      // and then zero out the day.
      $ret = new qDateInterval('P1D');
      $ret->d = 0;
      // Copy all fields
      $fields = ['y', 'm', 'd', 'h', 'i', 's', 'invert', 'days'];
      foreach ($fields as $field)
      {
        $ret->$field = $di->$field;
      }

      $ret->frac = $frac;

      return $ret;
    }

    public function normalize()
    {
      $this->i += floor($this->s / 60);
      $this->s = $this->s % 60;

      $this->h += floor($this->i / 60);
      $this->i = $this->i % 60;

      $this->d += floor($this->h / 24);
      $this->h = $this->h % 24;
    }

    public function format($fmt, $tense = false)
    {
      $this->normalize();
      $ret = parent::format($fmt);
      $ret = str_replace('%u', $this->frac, $ret);
      if ($tense)
      {
        if ($this->invert)
        {
          $ret .= ' ago';
        }
        else
        {
          $ret .= ' from now';
        }
      }
      return $ret;
    }
  }
