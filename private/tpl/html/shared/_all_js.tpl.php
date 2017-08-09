<?php
  function getScriptTag($src)
  {
    return '<script type="text/javascript" src="'.$src.'?v='.VER.'"></script>'."\n";
  }
?>
<?= getScriptTag('/shared/jquery/js/jquery-1.12.3.js') ?>
<?= getScriptTag('/shared/bootstrap/js/bootstrap.min.js') ?>

<?php if (defined('DATATABLES')):?>
<?= getScriptTag('/shared/datatables/js/jquery.dataTables.min.js') ?>
<?= getScriptTag('/shared/datatables/js/dataTables.responsive.min.js') ?>
<?= getScriptTag('/shared/datatables/js/dataTables.bootstrap.min.js') ?>
<?= getScriptTag('/shared/datatables/js/dataTables.buttons.min.js') ?>
<?= getScriptTag('/shared/datatables/js/buttons.bootstrap.min.js') ?>
<?= getScriptTag('/shared/datatables/js/responsive.bootstrap.min.js') ?>
<?= getScriptTag('/shared/datatables/js/jszip.min.js') ?>
<?= getScriptTag('/shared/datatables/js/pdfmake.min.js') ?>
<?= getScriptTag('/shared/datatables/js/vfs_fonts.js') ?>
<?= getScriptTag('/shared/datatables/js/buttons.html5.min.js') ?>
<?= getScriptTag('/shared/datatables/js/buttons.print.min.js') ?>
<?= getScriptTag('/shared/datatables/js/buttons.colVis.min.js') ?>
<?php endif; ?>

<?php if (defined('XEDIT')) :?>
<?= getScriptTag('/shared/misc/js/jquery.mask.js') ?>
<?= getScriptTag('/shared/xeditable/js/bootstrap-editable.min.js') ?>
<?= getScriptTag('/shared/html-generator/js/generator.js') ?>
<?= getScriptTag('/shared/edq/editable-support.js') ?>
<?php endif; ?>

<?= getScriptTag('/shared/misc/js/ie10-viewport-bug-workaround.js') ?>

<?php if (defined('MAPQUEST')): ?>
  <?= getScriptTag('/shared/leaflet/leaflet.js') ?>
  <script type="text/javascript" src="https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=<?= MAPKEY ?>"></script>
  <script type="text/javascript" src="https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-traffic.js?key=<?= MAPKEY ?>"></script>
  <?= getScriptTag('/shared/edq/mapq.js') ?>
  <?= getScriptTag('/shared/leaflet.extramarkers/js/leaflet.extra-markers.min.js') ?>
<?php endif; ?>

<?php if (defined('GPLACES')): ?>
  <?= getScriptTag('/shared/edq/gplaces.js') ?>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_API_KEY ?>&libraries=places&callback=initAutocomplete" async defer></script>
<?php endif; ?>
