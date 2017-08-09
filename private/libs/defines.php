<?php
// Override stupid php timezone thing
  date_default_timezone_set('UTC');

// Indicate pages are OK to proceed
  define('__VALID__', 1);

// Global defines
  define('LIBSVER', '1.0.2');
  define('DS', DIRECTORY_SEPARATOR);
  define('PS', PATH_SEPARATOR);
  define('URI_ROOT', '/');
  define('LIBS_DIR', __DIR__.DS);

  if ($_SERVER['DOCUMENT_ROOT'])
  {
    define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'].DS);
    define('PVT_DIR', realpath(DOC_ROOT.'..'.DS.'private').DS);
  }
  else
  {
    $pi = pathinfo(realpath($_SERVER['PHP_SELF']));
    $dirparts = explode(DS, $pi['dirname']);

    // Find the include dir.
    while ((count($dirparts) > 0) && (!is_dir(implode(DS, $dirparts).DS.'private')))
    {
      array_pop($dirparts);
    }

    define('PVT_DIR', implode(DS, $dirparts).DS.'private'.DS);
    define('FILE_ROOT', realpath($pi['dirname'].DS.'..').DS);
  }

  define('FLOURISH_DIR', LIBS_DIR . 'flourish' . DS);
  define('EDQ_DIR', LIBS_DIR . 'edq' . DS);

  // Some globals all pages will want to know about
  $__lwx  = array ('js', 'css', 'json', 'rss', 'xml', 'php', 'block', 'qr', 'captcha');

