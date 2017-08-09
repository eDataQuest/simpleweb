<?php
  class qNavigation
  {
    static public $earthRadius = 3959.9; // radius in miles (== 6372.8 km)

    protected $navURL     = 'http://www.mapquestapi.com/directions/v2/route';
    protected $geoURL     = 'http://www.mapquestapi.com/geocoding/v1/address';
    protected $geoReverseURL     = 'http://www.mapquestapi.com/geocoding/v1/reverse';
    protected $key        = '';
    protected $startPoint = null;
    protected $waypoints  = [];

    static public function calcDist($points)
    {
      $ret = 0;
      if ((is_array($points)) && (count($points) == 2))
      {
        // haversine formula.
        $dlat = deg2rad($points[0]['lat'] - $points[1]['lat']);
        $dlon = deg2rad($points[0]['lon'] - $points[1]['lon']);

        $a = sin($dlat/2) * sin($dlat/2) + cos(deg2rad($points[0]['lat'])) * cos(deg2rad($points[1]['lat'])) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * asin(sqrt($a));
        $ret = self::$earthRadius * $c;
      }
      return $ret;
    }

    static public function calcHeading($lat1, $lon1, $lat2, $lon2)
    {
      $ret = 0;

      // sin, cos, etc need radians, not degrees.
      $lat1 = deg2rad($lat1);
      $lon1 = deg2rad($lon1);
      $lat2 = deg2rad($lat2);
      $lon2 = deg2rad($lon2);

      // Get heading in radians
      $ret = atan2(sin($lon2 - $lon1) * cos($lat2),
                   cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lon2 - $lon1));

      // Convert to degrees.
      $ret = $ret * (180 / M_PI);

      // Result may be out of range 0..360 so convert.
      // Php modulus operator '%' converts to integer so don't use that.
      $ret = fmod($ret + 360, 360);

      return $ret;
    }

    static public function headingToCompass($heading)
    {
      $ret = 'UNKNOWN';

      $points = [
        '0'     => 'N',
        '22.5'  => 'NNE',
        '45'    => 'NE',
        '67.5'  => 'ENE',
        '90'    => 'E',
        '112.5' => 'ESE',
        '135'   => 'SE',
        '157.5' => 'SSE',
        '180'   => 'S',
        '202.5' => 'SSW',
        '225'   => 'SW',
        '247.5' => 'WSW',
        '270'   => 'W',
        '292.5' => 'WNW',
        '315'   => 'NW',
        '337.5' => 'NNW',
        '360'   => 'N'
      ];

      $diff = 360;

      foreach ($points as $k => $v)
      {
        if (abs($k - $heading) < $diff)
        {
          $diff = abs($k - $heading);
          $ret = $v;
        }
      }

      return $ret;
    }

    public function setStart($start)
    {
      if (trim($start))
      {
        $this->startPoint = strtolower(trim($start));
      }
    }

    public function setKey($key)
    {
      $this->key = $key;
    }

    public function addWaypointAddress($point = null)
    {
      $ret = false;

      if (is_array($point))
      {
        $point = implode(','.$point);
      }

      $point = strtolower(trim($point));

      if (!in_array($point, $this->waypoints))
      {
        $this->waypoints[] = $point;
        $ret = true;
      }

      return $ret;
    }

    public function clear()
    {
      $this->startPoint = '';
      $this->waypoints = [];
    }

    public function getReverseGeocoding($lat,$lon)
    {

      $args = [
          'key'      => $this->key,
          'location' => $lat.','.$lon
      ];

      $curl = new qCURL(
              [
          'url'             => $this->geoReverseURL,
          //'ignorecache'     => true,
          'allowargsinpost' => true,
          'method'          => 'GET',
          'args'            => $args
              ]
      );

      $raw = $curl->exec();
      $ret = json_decode($raw, true);
      return $ret;
    }

 public function getGeocoding($location)
    {

      $args = [
          'key'      => $this->key,
          'location' => $location
      ];

      $curl = new qCURL(
              [
          'url'             => $this->geoURL,
          //'ignorecache'     => true,
          'allowargsinpost' => true,
          'method'          => 'GET',
          'args'            => $args
              ]
      );

      $raw = $curl->exec();

      $ret = json_decode($raw, true);
      return $ret;
    }

 public function getTravelTime($from,$to)
    {

      $args = [
          'key'      => $this->key,
          'from' => $from,
          'to' => $to,
          'useTraffic'=>'true',
          'timeType' =>1,
      ];

      $curl = new qCURL(
              [
          'url'             => $this->navURL,
          'ignorecache'     => true,
          'allowargsinpost' => true,
          'method'          => 'GET',
          'args'            => $args
              ]
      );

      $raw = $curl->exec();

      $ret = json_decode($raw, true);
      return $ret;
    }

  public function run()
    {
      $ret = false;

      if ($this->startPoint)
      {
        if (count($this->waypoints) > 0)
        {
          // see http://www.mapquestapi.com/directions/#basicoptions for a full list of options.
          $args = [
            'key'  => $this->key,
          ];

          $postargs = [
            'locations' => [$this->startPoint],
          ];

          foreach($this->waypoints as $point)
          {
            $postargs['locations'][] = $point;
          }

          $curl = new qCURL(
          [
            'url'             => $this->navURL,
            //'ignorecache'     => true,
            'allowargsinpost' => true,
            'method'          => 'POST',
            'args'            => $args,
            'posttype'        => 'url',
            'postargs'        => ['json' => json_encode($postargs)]
           ]
          );

          $raw = $curl->exec();

          $ret = json_decode($raw, true);
        }
      }
      else
      {
        throw new Exception('Navigation has no start point set!');
      }

      return $ret;
    }
  }
