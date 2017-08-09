// Global vars
  mqmapdata   = {};
  mqmaps      = {};
  mqmarkers   = {};
  mqcircles   = {};
  mqpolylines = {};

// Custom object definitions
  var EDQMarker = function(params)
  {
    this.name   = null;
    this.lat    = 0;
    this.lon    = 0;
    this.mnum   = 0;

    this.icon   = 'fa-trash';
    this.color  = 'blue';
    this.shape  = 'circle';
    this.prefix = 'fa';

    this.btext  = false;
    this.bopen  = false;
    this.draggable = false;

    if (typeof(params) === 'object')
    {
      for (var param in params)
      {
        if (params.hasOwnProperty(param))
        {
          this[param] = params[param];
        }
      }
    }
  };

// Creates a mapquest dynamic map using the provided data.  The div corresponding to the embedded
// id must already exist and be named qmap_[data.id], e.g. qmap_1 if data.id == 1
function mqAddMap(data)
{
  var i;
  var opts  = data.opts;
  var lopts = data.Lopts;
  var lays  = [];

  mqmapdata[data.id] = data;

  lopts.layers = MQ.mapLayer();
  lopts.center = L.latLng(mqmapdata[data.id].mapCenter.lat, mqmapdata[data.id].mapCenter.lon);

  // create the map
  mqmaps[data.id] = L.map('qmap_' + data.id, lopts);

  var layerDefs = {};
  for (var layer in data.baseLayers)
  {
    if (data.baseLayers.hasOwnProperty(layer))
    {
      layerDefs[layer] = eval(data.baseLayers[layer]);
    }
  }

  var overlayDefs = {};
  for (var overlay in data.overlays)
  {
    if (data.overlays.hasOwnProperty(overlay))
    {
      overlayDefs[overlay] = eval(data.overlays[overlay]);
    }
  }

  L.control.layers(layerDefs, overlayDefs).addTo(mqmaps[data.id]);

  if (opts.scale)
  {
    L.control.scale().addTo(mqmaps[data.id]);
  }

  // initialize marker tracking for this map
  mqmarkers[data.id] = {};
  mqcircles[data.id] = {};
  mqpolylines[data.id] = {};

  mqDrawData(data);


}
function bindListenerToDragDropEnd(mapid, mkrname, fn, e)
{
  window[fn](mapid, mkrname, e);
}

// Adds a marker to the specified map.
function mqAddMarker(mapid, data)
{
  if ((typeof(data) === 'object') && data !== null )
  {
    var icopts = {
      icon: data.icon,
      shape: data.shape,
      prefix: data.prefix,
      markerColor: data.color
    };

    if (data.icon == 'fa-number')
    {
      icopts.number = data.mnum;
    }

    if (data.icon == 'fa-spinner')
    {
      icopts.extraClasses = 'fa-spin';
    }

    var ico = L.ExtraMarkers.icon(icopts);

    mqmarkers[mapid][data.name] =
      L.marker(
        [data.lat, data.lon],
        {
          draggable: data.draggable,
          icon: ico
        }
      );

    mqmarkers[mapid][data.name].on('dragend', function(e)
    {
      bindListenerToDragDropEnd(mapid, data.name, mqmapdata[mapid].opts.onMarkerDragDrop, e);
    });

    mqmarkers[mapid][data.name].addTo(mqmaps[mapid]);

    if (data.btext)
    {
      mqmarkers[mapid][data.name].bindPopup(data.btext);
      if (data.bopen)
      {
        mqmarkers[mapid][data.name].openPopup();
      }
    }

    if (data.circled)
    {
      var cData = {
        lat: data.lat,
        lon: data.lon,
        opts: {
          color: data.circled.color,
          radius: data.circled.radius
        }
      };

      mqAddCircle(mapid, cData);
    }
  }
}

// Adds a circle to the specified map.
function mqAddCircle(mapid, data)
{
  if ((typeof (data) === 'object') && data !== null)
  {
    mqcircles[mapid][data.name] =
            L.circle(
                    L.latLng(data.lat, data.lon),
                    data.opts
                    );
    mqcircles[mapid][data.name].addTo(mqmaps[mapid]);
  }
}

// Adds a polyline to the specified map.
function mqAddPolyLine(mapid, data)
{

  if ((typeof (data) === 'object') && data !== null)
  {
    mqpolylines[mapid][data.name] = L.polyline(
            data.points,
            {color: data.color}
    );
    mqpolylines[mapid][data.name].addTo(mqmaps[mapid]);
  }
}

function moveMarker(mkrName, mapid, newLL)
{
  var tmpLL = L.latLng(newLL.lat, newLL.lng);

  if (typeof(mqmarkers[mapid][mkrName]) !== 'undefined')
  {
    mqmarkers[mapid][mkrName].setLatLng(tmpLL);

    if (typeof(mqmarkers[mapid][mkrName].qTrack) !== 'undefined')
    {
      mqmarkers[mapid][mkrName].qTrack.addLatLng(tmpLL);
    }
  }
}



// Process the repetive things.
function mqDrawData(data)
{
  for (var property in data)
  {
    if (data.hasOwnProperty(property))
    {

      switch (property)
      {
        case 'id':
        case 'mapCenter':
        case 'baseLayers':
        case 'overlays':
        case 'opts':
        case 'Lopts':
          // Do nothing by design...
        break;

        case 'points':
          for (i = 0; i < data.points.length; i++)
          {
            var mkr = new EDQMarker(data.points[i]);
            mqAddMarker(data.id, mkr);
          }

          break;

        case 'circles':
          for (i = 0; i < data.circles.length; i++)
          {
            mqAddCircle(data.id, data.circles[i]);
          }

          break;

        case 'polylines':
          for (i = 0; i < data.polylines.length; i++)
          {
            mqAddPolyLine(data.id, data.polylines[i]);
          }

          break;

        case 'callbacks':
          for (i = 0; i < data.callbacks.length; i++)
          {
            $.getJSON(data.callbacks[i], function (json) {
              mqDrawData(json);
            });
          }
          break;

        default:
          console.log(property + ' Not defined in mqDraw');
          break;
      }


    }
  }


}

// Do setup, if any
$(document).ready(function() {
});