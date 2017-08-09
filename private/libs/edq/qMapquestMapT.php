<?php

trait qMapquestMapT
{
  protected static $_id = 0;

  /*
    'opts' is an array of generic/edq options or other flags, interpreted by mapq.js
    'Lopts' is an array passed directly to the Leaflet L.map() constructor.
  */
  public $_mapdata = [
    'id'           => 0,
    'mapCenter'    => ['lat'=>40.731701,'lon'=>-73.993411],
    'points'       => [],
    'circles'      => [],
    'polylines'    => [],
    'callbacks'    => [],

    'baseLayers'  => [ // layers must be defined here instead of in Lopts since the RHS is an expression.
      'Map'       => 'MQ.mapLayer()',
      'Satellite' => 'MQ.satelliteLayer()',
      'Hybrid'    => 'MQ.hybridLayer()',
      'Dark'      => 'MQ.darkLayer()',
      'Light'     => 'MQ.lightLayer()',
    ],
    'overlays' => [ // overlays must be defined here instead of in Lopts since the RHS is an expression.
      'Traffic Flow'      => 'MQ.trafficLayer({layers: [\'flow\']})',
      'Traffic Incidents' => 'MQ.trafficLayer({layers: [\'incidents\']})',
    ],
    'opts'      => [
      'minHeight'   => '100px',
      'canZoom'     => true,
      'scale'       => true,
    ],
    'Lopts'     => [
      'zoomControl'        => true,  // show +/- zoom control
      'zoom'               => '12',  // default zoom level
    ],
  ];

  public static function define()
  {
    if (!defined('MAPQUEST'))
    {
      define('MAPQUEST', true);
    }
  }


  public static function getNextId()
  {
    return ++self::$_id;
  }

  public function setCenter($lat, $lon)
  {
    $this->_mapdata['mapCenter']['lat'] = $lat;
    $this->_mapdata['mapCenter']['lon'] = $lon;

    return $this;
  }

  public function addPoint($name, $lat, $lon, $opts = [])
  {
    $this->_mapdata['points'][] =
      array_merge(
        [
          'name' => $name,
          'lat'  => $lat,
          'lon'  => $lon
        ],
        $opts
      );

    return $this;
  }


  public function addCircle($name, $lat, $lon, $color, $radius)
  {
    $this->_mapdata['circles'][] =

        [
          'name' => $name,
          'lat'  => $lat,
          'lon'  => $lon,
          'opts' => ['color'=>$color,'radius'=>$radius],
        ];

    return $this;
  }

  public function addPolyLine($name, $color, $points)
  {
    $this->_mapdata['polylines'][] =

        [
          'name' => $name,
          'color'  => $color,
          'points' => $points,
        ];

    return $this;
  }

  public function setMinHeight($v)
  {
    $this->_mapdata['opts']['minHeight'] = $v;

    return $this;
  }

   public function addCallBack($url)
  {
    $this->_mapdata['callbacks'][] = $url;


  }

}
