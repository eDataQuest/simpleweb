<?php if ($tplData->getActiveRecord()->exists()): ?>
  <?php if ($tplData->getBtnDoneEnable()): ?>
    <a
      class="btn btn-primary pull-right col-xs-2"
      data-button-type="done"
      data-form="<?= $tplData->getForm() ?>"
      data-table="<?= $tplData->getTable() ?>"
      data-pk="<?= htmlspecialchars($tplData->getPk()) ?>"
      data-redirect="<?= $tplData->getRedirect() ?>"
      ><?= $tplData->getBtnDoneCaption() ?></a>
  <?php endif; ?>
<?php else: ?>
  <?php if ($tplData->getBtnAddEnable()): ?>
    <a
      class="<?= $tplData->getBtnAddClass() ?>"
      data-button-type="add"
      data-form="<?= $tplData->getForm() ?>"
      data-table="<?= $tplData->getTable() ?>"
      data-pk="<?= htmlspecialchars($tplData->getPk()) ?>"
      <?php if ($tplData->getBtnAddHref()): ?>
      data-redirect="<?= $tplData->getBtnAddHref() ?>"
      <?php else: ?>
       data-redirect="<?= $tplData->getRedirect() ?>"
      <?php endif; ?>
      data-route=""
    ><strong><?= $tplData->getBtnAddCaption() ?></strong></a>
  <?php endif; ?>
<?php endif; ?>
