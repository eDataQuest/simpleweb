<?php
  class qSession
  {
    static protected $dbh = null;

    /************** START SessionHandlerInterface Methods **************/
    static public function close()
    {
      return true;
    }

    static public function destroy($session_id)
    {
      self::dbConnect();
      $stmt = self::$dbh->prepare('DELETE FROM sessions WHERE session_id = ?');
      $stmt->execute([$session_id]);

      return true;
    }

    static public function gc($maxlifetime)
    {
      self::dbConnect();
      self::$dbh->query('DELETE FROM sessions WHERE updated < now() - interval \''.$maxlifetime.' seconds\'');

      return true;
    }

    static public function open($sp, $session_id)
    {
      return true;
    }

    static public function read($session_id)
    {
      $ret = '';

      if (self::createSession($session_id))
      {
        $stmt = self::$dbh->prepare('UPDATE sessions SET updated=now() WHERE session_id = ?');
        $stmt->execute([$session_id]);

        $stmt = self::$dbh->prepare('SELECT data FROM sessions WHERE session_id = ?');
        $stmt->execute([$session_id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
          $ret = $row['data'];
        }
      }

      return $ret;
    }

    static public function write($session_id, $session_data)
    {
      $ret = false;

      if (self::createSession($session_id))
      {
        $stmt = self::$dbh->prepare('UPDATE sessions SET data = ?, updated=now() WHERE session_id = ?');
        $ret = $stmt->execute([$session_data, $session_id]);
      }

      return $ret;
    }

    /************** END SessionHandlerInterface Methods **************/
    static public function tableExists()
    {
      self::dbConnect();
      $ret = false;

      $stmt = self::$dbh->query('SELECT count(relname) as cnt FROM pg_class WHERE relname=\'sessions\' AND relkind=\'r\'');
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $ret = $row['cnt'] == 1;

      return $ret;
    }

    static protected function dbConnect()
    {
      if (!self::$dbh)
      {
        self::$dbh = new PDO('pgsql:host='.DBSRV.';port='.DBPORT.';dbname='.DBNAME.';user='.DBUSR.';password='.DBPASS);
      }
    }

    static protected function createSession($session_id)
    {
      self::dbConnect();
      $res = false;
      $ret = false;

      if(self::$dbh)
      {
        $stmt = self::$dbh->prepare('SELECT count(*) as cnt FROM sessions WHERE session_id=?');
        $stmt->execute([$session_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $val = $row['cnt'];

        if ($val == 0)
        {
          $ret = 1 == self::$dbh->exec('INSERT into sessions (session_id) VALUES ('.self::$dbh->quote($session_id).')');
        }
        else
        {
          $ret = true;
        }
      }

      return $ret;
    }

   /**
    * Gets a session value if it exists, returning null or the specified default otherwise.
    *
    * @param string $key
    *   The index of the value to return.
    * @param mixed $default
    *   The default value to return if the key is not found, defaults to NULL.
    *
    * @result
    *   The value of the requested key, or default.
    */
    static public function get($key, $default = null)
    {
      $ret = (array_key_exists($key, $_SESSION)) ? $_SESSION[$key] : $default;

      return $ret;
    }

   /**
    * Sets a session value.
    *
    * @param string $key
    *   The index of the value to set.
    * @param mixed $value
    *   The value to store.
    *
    */
    static public function set($key, $value)
    {
      if ($value != null)
      {
        $_SESSION[$key] = $value;
      }
      else
      {
        unset($_SESSION[$key]);
      }
    }
  }

  if ((php_sapi_name() != 'cli') &&
      (defined('DBUSR')) &&
      (defined('DBPASS')))
  {
    if (qSession::tableExists())
    {
      $ssh = session_set_save_handler(
        ['qSession', 'open'],
        ['qSession', 'close'],
        ['qSession', 'read'],
        ['qSession', 'write'],
        ['qSession', 'destroy'],
        ['qSession', 'gc']
      );
    }

    @session_start();
  }
