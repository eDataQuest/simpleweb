<?php

  class q2
  {
    static protected function getLLAdjust()
    {
      $ret = ((rand(0, 100) - 50) * 0.0002);
      return $ret;
    }

    static public function run()
    {
      $ret = false;

      try
      {
        for($x=0; $x<10; $x++)
        {
          $c = new Customer();
          $c->setName('Customer-'.$x);
          $c->store();
          $c->load();
          for ($y=0; $y<3; $y++)
          {
             $cc = new CustomerContact();
             $cc->setCustomerId($c->getCustomerId());
             $cc->setFirstName('Last-'.$x);
             $cc->setLastName('First-'.$y);
             $cc->store();
             $cc->free();
             $cc=null;
          }
          $c->free();
          $c=null;
        }

        $pois = [
          ['Flatiron Building',     40.7411, -73.9897],
          ['Empire State Building', 40.7484, -73.9857],
          ['Chrysler Building',     40.7516, -73.9755],
          ['Central Park',          40.7829, -73.9654],
        ];

        foreach ($pois as $poi)
        {
          $rec = new Poi();
          $rec->setName($poi[0]);
          $rec->setLat($poi[1]);
          $rec->setLon($poi[2]);
          $rec->store();
        }

        $park = new Poi(['name' => 'Central Park']);

        for ($i = 0; $i < 10; $i++)
        {
          $dev = new Device();
          $dev->setName('Device #'.($i + 1));
          $dev->setNotes('Auto-generated device');
          $dev->store();

          for ($j = 0; $j < 20; $j++)
          {
            $devloc = new DeviceLocation();
            $devloc->setDeviceId($dev->getDeviceId());
            $devloc->setLoggedAt(time() - (100 - $j));
            $devloc->setLat($park->getLat() + self::getLLAdjust());
            $devloc->setLon($park->getLon() + self::getLLAdjust());
            $devloc->store();
          }
        }

        $rec = new TestValidation();
        $rec->setNotes('Validation Tests');
        $rec->setNothingRequire('nothing here');
        $rec->setPhoneRequire('800-555-1212');
        $rec->setEmailRequire('require@example.com');
        $rec->setUrlRequire('http://www.example.com');
        $rec->setNumberRequire(5);
        $rec->setMoneyRequire(100.00);
        $rec->setDateRequire('01/31/99');
        $rec->setTimeRequire('11:02 am');
        $rec->store();

        $rec = new TestCheck();
        $rec->setSunday(0);
        $rec->setMonday(1);
        $rec->setTuesday(1);
        $rec->setWednesday(1);
        $rec->setThursday(1);
        $rec->setFriday(1);
        $rec->setSaturday(0);
        $rec->store();

        $ret = true;
      }
      catch (fException $e)
      {
        // show the error here?
      }

      return $ret;
    }
  }
