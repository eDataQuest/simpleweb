<table  id="<?= $tplData->getId() ?>" class="table table-condensed table-striped table-bordered table-hover" style="cellspacing: 0; width: 100%;" >
  <thead>
    <tr>
      <?php foreach ($tplData->getColumns() as $column): ?>
        <?php if (isset($column['label'])): ?>
          <th class="text-center"><?= $column['label'] ?></th>
        <?php endif; ?>
      <?php endforeach; ?>
    </tr>
  </thead>
</table>

<script>
  $(document).ready(function ()
  {
    var opts = <?= json_encode($tplData->getOpts()) ?>;
    opts['initComplete'] = function () {
      this.api().buttons().container().appendTo('#<?= $tplData->getId() ?>' + '_wrapper');
    };
    $('#<?= $tplData->getId() ?>').dataTable(opts);
  });
</script>