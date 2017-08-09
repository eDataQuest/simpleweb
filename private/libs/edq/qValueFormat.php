<?php
  class qValueFormat
  {
    // Default formatting function based on column info.
    public static function formatwithColumnInfo($val, $ci, $use)
    {
      $ret = $val;
      if (is_array($ci))
      {
        if (array_key_exists('type', $ci))
        {
          switch (strtolower($ci['type']))
          {
            case 'float':
              $places = 5;
              if (array_key_exists('decimal_places', $ci))
              {
                if ($ci['decimal_places'])
                {
                  $places = $ci['decimal_places'];
                }
              }

              switch (strtolower($use))
              {
                case 'edit':
                  $ret = number_format($val, $places, '.', '');
                break;

                default:
                case 'disp':
                  $ret = number_format($val, $places, '.', ',');
                break;
              }

            break;

            case 'timestamp':
              $fmt = 'c';
              switch (strtolower($use))
              {
                case 'edit':
                  $fmt = 'c';
                break;

                default:
                case 'disp':
                  $fmt = 'U';
                break;
              }

              $ts  = new fTimestamp($val);
              $ret = $ts->format($fmt);
            break;

            default:
            case 'blob':
            case 'integer':
            case 'text':
            case 'date':
            // do nothing
              $ret = $val;
            break;
          }
        }
      }

      return $ret;
    }

    public static function formatTimestamp($val, $fmt)
    {
      $ts  = new fTimestamp($val);
      $ret = $ts->format($fmt);

      return $ret;
    }

    public static function formatDate($val, $fmt)
    {
      $d  = new fDate($val);
      $ret = $d->format($fmt);

      return $ret;
    }
  }