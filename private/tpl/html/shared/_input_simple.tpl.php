<?php // $row (and other things) provided by input.tpl.php. ?>
<div class="<?= isset($row['data']['size']) ? $row['data']['size'] : 'col-xs-12' ?>">
<?php require('_input_simple_'.$row['data']['type'].'.tpl.php'); ?>
</div>
