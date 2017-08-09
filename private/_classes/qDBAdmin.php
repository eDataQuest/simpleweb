<?php

class qDBAdmin
{

  private $db       = null;
  private $files    = array();
  private $messages = array();

  public function run()
  {

    $data = array();

    $action = strtolower(fRequest::get('action', 'string', 'status'));
    switch ($action)
    {
      case 'status':
      case 'install':
      case 'upgrade':
        $data['messages'] = $this->adminDB($action);
        break;

      default:
        $data['messages'] = array('error' => 'Unknown Action: ' . $action);
        break;
    }
    if (self::isDBReady())
    {
      $data['db_status'] = 'System is on-line';
    }
    else
    {
      $data['db_status'] = 'System is in maintenance mode';
    }
    return $data;
  }

  private function adminDB($mode = 'status', $maxver = -1)
  {
    $data = array();
    try
    {
      // Get our own connection...
      $this->getDBH();

      switch ($mode)
      {
        case 'install':
          $this->resetDB();
          $this->runUpgrade($maxver);
          break;
        case 'upgrade':
          $this->runUpgrade($maxver);
          break;
        case 'status':
          break;
        default:
          throw new Exception('Unknown DB Method: ' . $mode);
          break;
      }
    }
    catch (Exception $e)
    {
      $this->messages['error'] = $e->getMessage();
    }
    $this->messages['version'] = $this->getDBVersion();
    return $this->messages;
  }

  // Helper Functions
  // Empty out the database and start fresh
  private function resetDB()
  {
    $this->messages['resetDB']     = 'Resetting Database';
    $this->messages['resetTables'] = 'Removing Tables';
    $this->getDBH();
    $this->db->query('BEGIN');

    $sql     = "select 'drop table if exists ' || name  as stmt from  sqlite_master where type = 'table' and name not like 'sqlite_%'";
    $results = $this->fetchAll($sql);
    foreach ($results as $result)
    {
      $this->exec($result['stmt']);
    }



    $this->db->query('COMMIT');
  }

  // Run the upgrade
  private function runUpgrade($maxver)
  {
    $data                  = array();
    $this->processed_files = array();
    $cver                  = $this->getDBVersion();
    $updates               = 0;

    $this->messages['current version'] = $cver;
    $this->getSchemaFileList('schema', '../private/dbs/sql/schema/');
    $this->getSchemaFileList('data', '../private/dbs/sql/data/');
    $this->getSchemaFileList('code', '../private/dbs/sql/code/');

    ksort($this->files);
    foreach ($this->files as $ver => $file)
    {
      if (($ver > $cver) && (($maxver == -1) || ($ver <= $maxver)))
      {

        $this->getDBH();
        $this->db->query('BEGIN');
        $type                 = key($file);
        $this->messages[$ver] = $file[$type];
        switch ($type)
        {
          case 'schema':
          case 'data':
            $script = file_get_contents($file[$type]);
            $this->exec($script);
            break;
          case 'code':
            $this->code_exec($ver, $file[$type]);
            break;
          default:
            throw new Exception('Unknown file update type: ' . $type);
            break;
        }
        $this->exec('DELETE FROM dbversions');
        $this->exec('INSERT INTO dbversions (current) VALUES (' . $ver . ')');
        $this->db->query('COMMIT');
        $this->messages[$type . '-' . $ver] = 'Update Sucessful';
        $updates++;
      }
    }

    if ($updates == 0)
    {
      $this->messages['no-update'] = 'No Updates Found';
    }
  }

  // What version are we?
  public function getDBVersion()
  {
    if ($this->db == null)
    {
      $this->getDBH();
    }
    // is dbversions there.. if not returning 0 is fine.
    $row = $this->fetch("SELECT count(*) as ver FROM sqlite_master where name = 'dbversions'");

    if ($row['ver'] == 1)
    {
      $row = $this->fetch('SELECT max(current) as ver FROM dbversions');
    }
    return $row['ver'];
  }

  // Determine if we are ok to be running....
  public static function isDBReady()
  {
    global $__db;
    $ret = false;
    $ver = -1;
    // is dbversions there.. if not returning 0 is fine.
    try
    {
      $row = $__db->query("SELECT count(*) as ver FROM sqlite_master where name = 'dbversions'");
      $ver = $row->fetchScalar();
      if ($ver == 1)
      {
        $row = $__db->query('SELECT max(current) as ver FROM dbversions');
        $ver = $row->fetchScalar();
      }
    }
    catch (fException $e)
    {
      // A waring message is thrown here when the DB is altered.
      //throw new Exception('getIsReady: ' . $e->getMessage());
    }
    if (DBVER == $ver)
    {
      $ret = true;
    }
    return $ret;
  }

  // Find upgrade files
  private function getSchemaFileList($type, $path)
  {
    if (is_dir($path))
    {
      if ($h = opendir($path))
      {
        while (false !== ($e = readdir($h)))
        {
          if (!is_dir($path . $e))
          {
            $parts                         = explode('.', $e);
            $parts[0]                      = str_replace('q', '', $parts[0]); // remove the q
            $this->files[$parts[0]][$type] = $path . $e;
          }
        }
      }
    }
  }

  // Wrap all the flourish calls into functions to ensure error trapping
  // Connect/Re-Connect to the database
  private function getDBH()
  {
    try
    {
      if ($this->db)
      {
        $this->db->close();
        $this->db = null;
      }
      $this->db = new fDatabase('sqlite', '../private/dbs/test-website.db');
      $this->db->connect();
      fORMDatabase::attach($this->db);
      fORMSchema::reset();
      $schema   = new fSchema($this->db);
      $schema->clearCache();
    }
    catch (fException $e)
    {
      throw new Exception('getDBH: ' . $e->getMessage());
    }
  }

  // Execute a SQL command
  private function exec($sql)
  {
    try
    {
      $this->db->execute($sql);
    }
    catch (fException $e)
    {
      $this->db->query('ROLLBACK');
      throw new Exception('exec: ' . $e->getMessage());
    }
  }

  // Fetch 1 or more rows
  private function fetchAll($sql)
  {
    $ret = null;
    try
    {
      $ret = $this->db->query($sql);
    }
    catch (fException $e)
    {
      $this->db->query('ROLLBACK');
      throw new Exception('fetchAll: ' . $e->getMessage());
    }
    return $ret;
  }

  // Fetch only 1 row
  private function fetch($sql)
  {
    $ret = null;
    try
    {
      $result = $this->db->query($sql);
      $ret    = $result->fetchRow();
    }
    catch (fException $e)
    {
      $this->db->query('ROLLBACK');
      throw new Exception('fetch: ' . $e->getMessage());
    }
    return $ret;
  }

  // This function executes a code class
  private function code_exec($ver, $file)
  {
    try
    {
      require_once($file);
      $class = 'q' . $ver;
      $rc    = $class::run($this->db);
      if ($rc == false)
      {
        $this->db->query('ROLLBACK');
        throw new Exception('code_exec: ' . $class . ' failed');
      }
    }
    catch (Exception $e)
    {
      $this->db->query('ROLLBACK');
      throw new Exception('code_exec: ' . $e->getMessage());
    }
  }

}
