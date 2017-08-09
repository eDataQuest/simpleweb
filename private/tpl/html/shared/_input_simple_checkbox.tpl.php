<?php
  // $row (and other things) provided by input.tpl.php.
?>
  <input
    type="checkbox"
    <?php if ($inputOpts['data-value']) { print("checked\n"); } ?>
  />
  <?php if ($row['data']['type'] != 'hidden'): ?>
    <h3 style="display:inline;" class="xedit-margin"><small><?= $tplData->getActiveRecord()->qGetEditLabel($row['data']['col']) ?></small></h3>
  <?php endif; ?>
