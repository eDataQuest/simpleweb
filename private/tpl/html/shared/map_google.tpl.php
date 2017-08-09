<?php $id = $mapdata['id']; ?>
<div id="gmap_container_<?= $id ?>">
  <div id="gmap_<?= $id ?>" style="min-height: <?= $mapdata['minHeight'] ?>;">
  </div>
  <?php if (false) :?>
  <div id="gmap_debug_<?= $id ?>" style="visibility: hidden;">
    <pre><?php print_r($mapdata); ?></pre>
  </div>
  <?php endif; ?>

  <script type="text/javascript">
    <?php if (!defined('GMAPDATA')) :?>
    <?php define('GMAPDATA', true); ?>
    gmapdata = {};
    gmaps = {};
    <?php endif; ?>
    gmapdata[<?= $id ?>] = <?= json_encode($mapdata, JSON_PRETTY_PRINT) ?>;
  </script>
</div>