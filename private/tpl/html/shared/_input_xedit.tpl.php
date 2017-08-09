<?php
      if ($row['data']['type'] != 'select')
      {
        $value = htmlspecialchars($tplData->getActiveRecord()->qGetValue($row['data']['col'], 'Disp'));
      }
    ?>
      <div class="<?= isset($row['data']['size']) ? $row['data']['size'] : 'col-xs-12' ?>">
      <?php if ($row['data']['type'] == 'checkbox') : ?>
        <a class="editable"
          style="display:none;"
          data-name="<?= htmlspecialchars($inputOpts['data-name']) ?>"
          data-form="<?= htmlspecialchars($inputOpts['data-form']) ?>"
          data-value="<?= htmlspecialchars($inputOpts['data-value']) ?>"
          data-type="<?= htmlspecialchars($inputOpts['data-type']) ?>"
          data-pk="<?= htmlspecialchars($inputOpts['data-pk']) ?>"
          data-params="<?= htmlspecialchars($inputOpts['data-params']) ?>"
        ></a>
        <input
          type="checkbox"
          class="editable-paired"
          data-paired-form="<?= htmlspecialchars($inputOpts['data-form']) ?>"
          data-paired-control="<?= htmlspecialchars($inputOpts['data-name']) ?>"
          <?php if ($inputOpts['data-value']) { print("checked\n"); } ?>
        />

        <?php if ($row['data']['type'] != 'hidden'): ?>
          <h3 style="display:inline;" class="xedit-margin"><small><?= $tplData->getActiveRecord()->qGetEditLabel($row['data']['col']) ?></small></h3>
        <?php endif; ?>
      <?php else : ?>
        <?php if ($row['data']['type'] != 'hidden'): ?>
          <h3 class="xedit-margin"><small><?= $tplData->getActiveRecord()->qGetEditLabel($row['data']['col']) ?></small></h3>
        <?php endif; ?>
        <a
        <?php foreach ($inputOpts as $key => $val): ?>
          <?php if ($val):
            if (is_array($val))
            {
              $outval = htmlspecialchars(json_encode($val));
            }
            else
            {
              $outval = htmlspecialchars($val);
            }
          ?>
            <?= $key ?>="<?= $outval ?>"
          <?php endif; ?>
        <?php endforeach; ?>
        ><?= $value ?></a>
      <?php endif; ?>
      </div>
