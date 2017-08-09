<?php
  class AutoLoader
  {
    static public function __AutoLoad($class_name)
    {
      if (stream_resolve_include_path($class_name.'.php'))
      {
        require_once($class_name.'.php');
      }
      elseif (stream_resolve_include_path(str_replace('_', '/', $class_name).'.php'))
      {
        require_once(str_replace('_', '/', $class_name).'.php');
      }
      else
      {
        if (class_exists('qSitePage'))
        {
          if (method_exists(qSitePage, 'autoClassCreator'))
          {
            qSitePage::autoClassCreator($class_name);
          }
        }

        if (class_exists('fDatabase') && class_exists('fActiveRecord'))
        {
          global $__db;
          if ($__db)
          {
            fDatabase::createDefaultActiveRecordClass($__db, $class_name);
          }
        }

        // Last ditch effort
        if (!class_exists($class_name))
        {
          qPage::autoClassCreator($class_name);
        }
      }
    }

    static protected function addSubdirsToSearchPath($tdir)
    {
      $restrictedDirs = array('.', '..', '.svn');
      $newdirs = array();

      while(strpos($tdir, '..'))
      {
        $tdir = preg_replace('/\/[^\/]+?\/\.\./', '', $tdir);
      }

      if ((file_exists($tdir)) && is_dir($tdir))
      {
        if ($dh = opendir($tdir))
        {
          while (false !== ($dir = readdir($dh)))
          {
            if ((!in_array($dir, $restrictedDirs)) && (is_dir($tdir.$dir)))
            {
              $newdirs[] = $tdir.$dir.DS;
            }
          }
          closedir($dh);
        }
      }
      sort($newdirs);
      set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $newdirs));
    }

    static public function initialize($additional = array())
    {
      // Include path
      $dir = '';
      if (defined(DOC_ROOT))
      {
        $dir = DOC_ROOT . PATH_SEPARATOR;
      }
      elseif (defined(FILE_ROOT))
      {
        $dir = FILE_ROOT . PATH_SEPARATOR;
      }

      set_include_path(
        '.' . DS . PATH_SEPARATOR
        . $dir
        . PVT_DIR . PATH_SEPARATOR
        . PVT_DIR . 'pages' . DS . PATH_SEPARATOR
        . PVT_DIR . 'blocks' . DS . PATH_SEPARATOR
        . PVT_DIR . '_classes' . DS . PATH_SEPARATOR
        . EDQ_DIR
      );

      // Register autoloader
      spl_autoload_register('AutoLoader::__AutoLoad');
    }
  }