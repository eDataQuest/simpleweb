<?php if ($tplData->getActiveRecord()->exists()): ?>
  <?php if ($tplData->getBtnDeleteEnable()): ?>
    <a
      class="text-danger q-box-button"
      data-button-type="delete"
      data-form="<?= $tplData->getForm() ?>"
      data-table="<?= $tplData->getTable() ?>"
      data-pk="<?= htmlspecialchars($tplData->getPk()) ?>"
      data-redirect="<?= $tplData->getRedirect() ?>"
      ><strong><?= $tplData->getBtnDeleteCaption() ?></strong></a>
    <?php endif; ?>
  <?php else: ?>
    <?php if ($tplData->getBtnCancelEnable()): ?>
    <a
      class="<?= $tplData->getBtnCancelClass() ?>"
      href="<?= $tplData->getRedirect() ?>"
      ><?= $tplData->getBtnCancelCaption() ?></a>
  <?php endif; ?>
<?php endif; ?>