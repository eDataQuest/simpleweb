<?php


$mapdata = $tplData->_mapdata;
$id = $mapdata['id'];
?>
<div id="qmap_container_<?= $id ?>">
  <div id="qmap_<?= $id ?>" class="shadow" style="min-height: <?= $mapdata['opts']['minHeight'] ?>;">
  </div>

  <script type="text/javascript">

    $(document).ready(function() {
      var data  = <?= json_encode($mapdata, JSON_PRETTY_PRINT) ?>;
      mqAddMap(data);
    });
  </script>
</div>