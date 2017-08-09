<?php foreach ($tplData->getInputs() as $group): ?>
  <div class="row">
<?php
    foreach ($group as $row)
    {
      $value = '';
      $inputOpts = $tplData->getInputOptions($row['data']);

      require ('_input_'.$row['type'].'.tpl.php');
    }
?>
  </div>
<?php endforeach; ?>
<!-- end -->
<h4 class="text-danger" id="form_message_<?= $tplData->getForm() ?>">&nbsp;</h4>

