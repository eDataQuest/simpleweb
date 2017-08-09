<?php if (defined('BSTHEME')): ?>
<link rel="stylesheet" type="text/css" href="/shared/bootstrap/css/bootstrap-<?= BSTHEME ?>.min.css">
<?php else: ?>
<link rel="stylesheet" type="text/css" href="/shared/bootstrap/css/bootstrap.min.css">
<?php endif; ?>
<link rel="stylesheet" type="text/css" href="/shared/bootstrap/css/qBSHelpers.css">
<link rel="stylesheet" type="text/css" href="/shared/fontawesome/css/font-awesome.min.css">

<?php if (defined('DATATABLES')):?>
<link rel="stylesheet" type="text/css" href="/shared/datatables/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/shared/datatables/css/buttons.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/shared/datatables/css/responsive.bootstrap.min.css">
<?php endif; ?>

<?php if (defined('XEDIT')):?>
<link rel="stylesheet" type="text/css" href="/shared/xeditable/css/bootstrap-editable.css" >
<?php endif; ?>

<?php if (defined('DATATABLES') || defined('XEDIT')):?>
<link rel="stylesheet" type="text/css" href="/shared/html-generator/css/generator.css">
<?php endif; ?>


<link rel="stylesheet" type="text/css" href="/shared/misc/css/ie10-viewport-bug-workaround.css">
<?php if (defined('MAPQUEST')): ?>
  <link rel="stylesheet" type="text/css" href="/shared/leaflet/leaflet.css" />
  <link rel="stylesheet" type="text/css" href="/shared/leaflet.extramarkers/css/leaflet.extra-markers.min.css" />
<?php endif; ?>