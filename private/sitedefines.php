<?php
  define('DBSRV',  'localhost');
  define('DBNAME', 'edqcore');
  define('DBHOST', DBSRV);
  define('DBTYPE', 'sqlite');
  define('VER', 4.0);
  define('DBVER', 2);
  define('ENV', array_key_exists('ENV', $_ENV) ? $_ENV['ENV'] : 'dev');
  define('MAPKEY', 'MIuzgXS3Ci2aABL5HvwCJKlxtA04THJR');
  define('GOOGLE_API_KEY', 'AIzaSyCIx8r7n-amFou0LhLKUp5Jd6gxiJl84I4');
//  define('DISABLE_MASKS', true); // if DEFINED, jquery edit masks are not used.
//  define('NO_EXTENDED_ACTIVE_RECORD', null); // if DEFINED, fDatabase will not use qExtendedActiveRecord
