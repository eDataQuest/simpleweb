<?php

class qXeditBlock extends qBlock
{
  public function run($args)
  {
    $data             = ['rc' => 0, 'msg' => 'Unknown Error'];
    $table            = fRequest::get('table');
    $pk               = fRequest::get('pk');
    $name             = fRequest::get('name');
    $value            = fRequest::get('value');
    $action           = fRequest::get('action');
    $data['action']   = $action;
    $data['redirect'] = fRequest::get('redirect');
    $route            = fRequest::get('route');

    try
    {
      $table_class = fORM::classize($table);

      // A sort of ugly way to determine if this is attempting to reference an existing record
      // or a new one.
      $isNew = true;
      if ($pk && is_array($pk))
      {
        $isNew = false;
        foreach ($pk as $k => $v)
        {
          if ($v === null)
          {
            $isNew = true;
            break;
          }
        }
      }

      // Use what we learned above to make a new record or look up one that exists.
      if ($isNew)
      {
        $obj = new $table_class();
      }
      else
      {
        $obj = new $table_class($pk);
      }

      switch ($action)
      {
        case 'delete':
          $obj->delete();
        break;

        case 'create':
        case 'fullupdate':
          $obj->populate();
          if (method_exists($obj, 'qThrowForInvalid'))
          {
            $obj->qThrowForInvalid();
          }

          $obj->store();
        break;

        case 'update':
          $fn = 'set' . fGrammar::camelize($name, TRUE);
          $obj->$fn($value);
          if (method_exists($obj, 'qThrowForInvalid'))
          {
            $obj->qThrowForInvalid([$name]);
          }

          // The store will not actually happen if the PK is not set, e.g. if the record is new.
          $obj->store(false, false);

          if (method_exists($obj, 'qGetValue'))
          {
            $data['dispValue'] = $obj->qGetValue($name, 'Disp');
            $data['editValue'] = $obj->qGetValue($name, 'Edit');
          }
        break;
      }

      // Hack for add... Figure this out... jim...
      $data['redirect'] = str_replace('{pkeys_hack}', json_encode($obj->qGetPk()), $data['redirect']);

      //
      // Overide the natural redirect to somewhere else
      if ($route)
      {
        $pkeys = '';
        foreach ($obj->qGetPk() as $k => $v)
        {
          $pkeys.='/' . $k . '/' . $v;
        }
        $data['redirect'] = str_replace('{pkeys}', $pkeys, $route);
      }
      $obj->free();
      $obj = null;

      $data['rc']  = 1;
      $data['msg'] = 'OK';
    }
    catch (qEditingException $e)
    {
      $data['msg']   = $e->getMessage();
      $data['field'] = $e->getField();
    }
    catch (fValidationException $e)
    {
      $data['msg']   = 'fValidation error: ' . $e->getMessage();
    }
    catch (fSQLException $e)
    {
      switch (true)
      {
        default:
          $data['msg'] = 'Database Error: ' . $e->getMessage();
        break;
      }
    }
    catch (Exception $e)
    {
      $data['msg'] = 'Unknown Error: ' . $e->getMessage();
    }

    return $data;
  }
}
