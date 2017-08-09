<?php
  require_once('defines.php');

  // Setup Timing
  require_once(EDQ_DIR.'qExecutionTime.php');
  $__et = new qExecutionTime();
  $__et->Start();

  mb_internal_encoding('UTF-8');


  // Load Flourish
  require_once(FLOURISH_DIR.'fLoader.php');
  fLoader::best();

  require_once('AutoLoader.php');
  AutoLoader::initialize();

  require_once('sitedefines.php');

  // By default, disable all caching by browser
  if (!defined('BROWSER_CAN_CACHE'))
  {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
  }

  // Database definitions
  require_once(PVT_DIR.'dbs'.DS.DBTYPE.'.php');

  if (php_sapi_name() != 'cli')
  {
    require_once('qSession.php');
  }

  // Connect the database and attach to ORM.
  if (!defined('DBCONNECT_DELAY') && (isset($__db)))
  {
    try
    {
      $__db->connect();
      fORMDatabase::attach($__db);

      if (defined('FLOURISH_CACHE'))
      {
        $flourish_cache    = new fCache('file', '/tmp/flourish.cache');
        fORM::enableSchemaCaching($flourish_cache);
        error_log('Flourish cache enabled');

      }
    }
    catch (Exception $e)
    {
      error_log('Database error: '.$e->getMessage());
    }
  }
