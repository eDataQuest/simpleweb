<?php

class qValidate
{
  protected static $constraints = [
    'checkbox' => [
      'pattern'     => '/^[01]$/',
      'message'     => 'Only 0 and 1 are valid values',
    ],

    'dateISO' => [
      'pattern'     => '/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/',
      'mask'        => '9999-99-99',
      'message'     => 'Please enter a date in the format "yyyy-mm-dd".',
      'placeholder' => 'Ex. 2016-01-15',
      'dispFormat'  => 'Y-m-d',
      'editFormat'  => 'Y-m-d'
    ],

    'datetime' => [
      'pattern'     => '/^[0-9]{4}-[0-1][0-9]-[0-3][0-9] [01]{0,1}[0-9]:[0-5][0-9] *[ap]m$/',
      'mask'        => '9999-99-99 09:99 AA',
      'message'     => 'Please enter a date and time in the format "yyyy-mm-dd 12:34 pm".',
      'placeholder' => 'Ex. 2016-01-15 12:34 pm',
      'dispFormat'  => 'Y-m-d g:i a',
      'editFormat'  => 'Y-m-d g:i a',
    ],

    'datetimesec' => [
      'pattern'     => '/^[0-9]{4}-[0-1][0-9]-[0-3][0-9] [01]{0,1}[0-9]:[0-5][0-9]:[0-5][0-9] *[aApP][mM]$/',
      'mask'        => '9999-99-99 09:99:99 AA',
      'message'     => 'Please enter a date and time in the format "yyyy-mm-dd 12:34:59 pm".',
      'placeholder' => 'Ex. 2016-12-25 12:34:59 PM'
    ],

    'dateUS' => [
      'pattern'     => '/^[0-1][0-9]\/[0-3][0-9]\/[0-9]{2,4}$/',
      'mask'        => '09/09/9900',
      'message'     => 'Please enter a date in the format "mm/dd/yy".',
      'placeholder' => 'Ex. 12/25/16',
      'dispFormat'  => 'm/d/Y',
      'editFormat'  => 'm/d/Y'
    ],

    'email' => [
      'pattern'     => '/^[-_a-zA-Z0-9]+@[-a-zA-Z0-9]+\.[-.a-zA-Z0-9]+$/',
      'message'     => 'Please enter a valid email address.',
      'placeholder' => 'Ex. john.doe@example.com'
    ],

    'float' => [
      'pattern'     => '/^[-]{0,1}[[:digit:]]+(\.){0,1}[[:digit:]]*$/',
      'mask'        => '-099999999.999999999',
      'mask-extra'    => [
        'translation' => ['-' => ['pattern' => '[-]', 'optional' => 'true']],
      ],
      'message'     => 'Please enter a number in the format 123.4567',
      'placeholder' => 'Ex. 123.4567',
    ],

    'floatpos' => [
      'pattern'     => '/^[[:digit:]]+(\.){0,1}[[:digit:]]*$/',
      'mask'        => '099999999.999999999',
      'message'     => 'Please enter a number in the format 123.4567',
      'placeholder' => 'Ex. 123.4567',
    ],

    'gplaces' => [
      'message'     => 'Please enter a place name',
      'placeholder' => 'Ex. Acme Products',
    ],

    'integer' => [
      'pattern'       => '/^[-]{0,1}[[:digit:]]+$/',
      'mask'          => 'e0#',
      'mask-extra'    => [
        'translation' => ['e' => ['pattern' => '[-0-9]', 'optional' => 'true']],
      ],
      'message'       => 'Please enter a whole number',
      'placeholder'   => 'Ex. 123 or -456'
    ],

    'integerbool' => [
      'pattern'     => '/^[01]$/',
      'mask'        => 'b',
      'mask-extra'    => [
        'translation' => ['b' => ['pattern' => '[01]']],
      ],
      'message'     => 'Enter 1 for true/yes or 0 for false/no',
      'placeholder' => '1 or 0',
    ],

    'integerneg' => [
      'pattern'     => '/^-[[:digit:]]+$/',
      'mask'        => '-0#',
      'message'     => 'Please enter a negative whole number',
      'placeholder' => 'Ex. -123'
    ],

    'integerpos' => [
      'pattern'     => '/^[[:digit:]]+$/',
      'mask'        => '0#',
      'message'     => 'Please enter a positive whole number',
      'placeholder' => 'Ex. 123'
    ],

    'lat' => [
      'pattern'     => '/^[-]{0,1}[[:digit:]]+(\.){0,1}[[:digit:]]*$/',
      'mask'        => '-099999999.999999999',
      'mask-extra'    => [
        'translation' => ['-' => ['pattern' => '[-]', 'optional' => 'true']],
      ],
      'message'     => 'Please enter a latitude from -90 to +90 in the format 23.4567',
      'placeholder' => 'Ex. 23.4567',
    ],

    'lon' => [
      'pattern'     => '/^[-]{0,1}[[:digit:]]+(\.){0,1}[[:digit:]]*$/',
      'mask'        => '-099999999.999999999',
      'mask-extra'    => [
        'translation' => ['-' => ['pattern' => '[-]', 'optional' => 'true']],
      ],
      'message'     => 'Please enter a longitude from -180 to +180 in the format 23.4567',
      'placeholder' => 'Ex. 23.4567',
    ],

    'money' => [
      'pattern'     => '/^[-]{0,1}[[:digit:]]+(\.){0,1}[[:digit:]]{0,2}$/',
      'mask'        => '-099999999.00',
      'mask-extra'    => [
        'translation' => ['-' => ['pattern' => '[-]', 'optional' => 'true']],
      ],
      'message'     => 'Please enter a number in the format 1.23',
      'placeholder' => 'Ex. 1.23',
    ],

    'phone' => [
      'pattern'     => '/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/',
      'mask'        => '000-000-0000',
      'message'     => 'Please enter a valid phone number.',
      'placeholder' => 'Ex. 212-555-1212'
    ],

    'time' => [
      'pattern'     => '/^[01]{0,1}[0-9]{0,1}:[0-5]{1}[0-9]{1} *[aApP]{1}[mM]{0,1}$/',
      'mask'        => '09:00 AA',
      'message'     => 'Please enter a time in the format "12:34 pm".',
      'placeholder' => '12:34 PM'
    ],

    'timezone' => [
      'message'     => 'Please enter a valid timezone in the format Country/City_Name',
      'placeholder' => 'America/New_York',
    ],

    'url' => [
      'pattern'     => '/^http[s]{0,1}:\/\/[-a-zA-Z0-9]+\.[-a-zA-Z0-9]+.*$/',
      'message'     => 'Please enter a valid URL, Ex. http://example.com',
      'placeholder' => 'Ex. http://example.com'
    ],

    'zip' => [
      'pattern'     => '/^[0-9]{5}$/',
      'mask'        => '00000',
      'message'     => 'Please enter a valid zip code.',
      'placeholder' => 'Ex. 33060'
    ],

    // 'test' stays last.
    'test' => [
      'pattern'     => '/^[[:alnum:]]+$/',
      'message'     => 'Please enter a valid test value.',
      'placeholder' => 'Enter your test data here'
    ],

  ];

  public static function getTypes()
  {
    return array_keys(self::$constraints);
  }

  public static function isValidType($t)
  {
    return in_array($t, self::getTypes());
  }

  public static function validateToStoreTest($val)
  {
    // do nothing
  }

  public static function formatToStoreTest($val)
  {
    $ret = $val;

    return $ret;
  }

  public static function getConstraints()
  {
    return self::$constraints;
  }

  public static function getConstraint($name)
  {
    return array_key_exists($name, self::$constraints) ? self::$constraints[$name] : [];
  }

  public static function getConstraintFormatValue($name, $key, $def = null)
  {
    $ret = $def;

    if ($c = self::getConstraint($name))
    {
      if (array_key_exists($key, $c))
      {
        $ret = $c[$key];
      }
    }

    return $ret;
  }

  public static function getConstraintPattern($name)
  {
    $ret = self::getConstraintFormatValue($name, 'pattern');
    return $ret;
  }

  public static function getConstraintMessage($name)
  {
    $ret = self::getConstraintFormatValue($name, 'message', 'An unexpected error occurred while storing this value');
    return $ret;
  }

  public static function getConstraintMask($name, $def = false)
  {
    $ret = false;

    if (!defined('DISABLE_MASKS'))
    {
      $ret = [];
      if ($tmp = self::getConstraintFormatValue($name, 'mask', $def))
      {
        $ret['mask'] = $tmp;
      }

      if ($tmp = self::getConstraintFormatValue($name, 'mask-extra', $def))
      {
        $ret['mask-extra'] = $tmp;
      }
    }

    return $ret;
  }

  public static function getConstraintPlaceholder($name, $def = false)
  {
    $ret = self::getConstraintFormatValue($name, 'placeholder', $def);
    return $ret;
  }

  public static function getConstraintDispFormat($name, $def = false)
  {
    $ret = self::getConstraintFormatValue($name, 'dispFormat', $def);
    return $ret;
  }

  public static function getConstraintEditFormat($name, $def = false)
  {
    $ret = self::getConstraintFormatValue($name, 'editFormat', $def);
    return $ret;
  }

  // Field type validation and formatting to store functions
  public static function validateToStoreEmail($val)
  {
    $ret = false;

    try
    {
      $parts = explode('@', $val);
      if (count($parts) == 2)
      {
        $res = null;
        if (false !== getmxrr($parts[1], $res))
        {
          $ret = true;
        }
        elseif ($parts[1] != gethostbyname($parts[1]))
        {
          $ret = true;
        }
      }
    }
    catch (Exception $e)
    {
      $ret = false;
    }

    return $ret;
  }

  protected static function validateDate($val, $fmt)
  {
    $ret = false;

    $dt = strptime($val, $fmt);
    $ret = $dt !== false;

    return $ret;
  }

  public static function validateToStoreDateISO($val)
  {
    return self::validateDate($val, '%Y-%m-%d');
  }

  public static function validateToStoreDatetime($val)
  {
    return true;
    return self::validateDate($val, '%Y-%m-%d %I:%M %P') ||
           self::validateDate($val, '%Y-%m-%d %I:%M %p') ;
  }

  public static function validateToStoreDateUS($val)
  {
    return self::validateDate($val, '%m/%d/%y');
  }

  public static function validateToStoreUrl($val)
  {
    $ret = false;

    try
    {
      if ($parsed = parse_url($val))
      {
        if (array_key_exists('scheme', $parsed) && array_key_exists('host', $parsed))
        {
          if ((strtolower($parsed['scheme']) == 'http') || (strtolower($parsed['scheme'] == 'https')))
          {
            if ($parsed['host'] != gethostbyname($parsed['host']))
            {
              $ret = true;
            }
          }
        }
      }
    }
    catch (Exception $e)
    {
      $ret = false;
    }

    return $ret;
  }

  public static function validateToStoreMoney($val)
  {
    $ret = false;

    try
    {
      if (is_numeric($val))
      {
        if ((floor($val * 100))/100 == $val)
        {
          $ret = true;
        }
      }
    }
    catch (Exception $e)
    {
    }

    return $ret;
  }

  protected static function validateRange($val, $min, $max)
  {
    $ret = false;

    try
    {
      if (is_numeric($val))
      {
        $ret = (($val >= $min) && ($val <= $max));
      }
    }
    catch (Exception $e)
    {
    }

    return $ret;
  }

  public static function validateToStoreLat($val)
  {
    return self::validateRange($val, -90, 90);
  }

  public static function validateToStoreLon($val)
  {
    return self::validateRange($val, -180, 180);
  }

  public static function validateToStoreTimezone($val)
  {
    return fTimestamp::isValidTimezone($val);
  }
}
