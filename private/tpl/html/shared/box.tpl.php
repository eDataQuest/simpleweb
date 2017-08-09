<div class="panel panel-default attention">
  <div class="panel-body">

<?php
  $tbo = $this->getThisBlock($this_block)['self'];
  if ($tbo->tplData instanceof qHtmlControls) :
?>
    <span
      data-table="<?= $tbo->tplData->getTable() ?>"
      data-form="<?= $tbo->tplData->getForm() ?>"
<?php if ($tbo->tplData->recordExists()) : ?>
      data-pk="<?= htmlspecialchars($tbo->tplData->getPk()) ?>"
      data-saveonchange="true"
<?php else: ?>
      data-saveonchange="false"
<?php endif; ?>
    />
<?php
  endif;
?>
    <div class="row  q-box-title">
      <div class="col-xs-12 col-sm-10">
        <?php $this->includeGsTpl($this_block,'BoxTopLeft'); ?>
      </div>
      <div class="col-xs-12 col-sm-2">
        <div class="row">
          <div class="col-xs-12">
            <?php $this->includeGsTpl($this_block,'BoxTopRight'); ?>
          </div>
        </div>
      </div>
    </div>

    <hr style="margin-top: 2px;">

    <div class="row">
      <div class="col-xs-12">
        <?php $this->includeGsTpl($this_block,'BoxMiddle'); ?>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <?php $this->includeGsTpl($this_block,'BoxBottom'); ?>
      </div>
    </div>
  </div>

</div>

