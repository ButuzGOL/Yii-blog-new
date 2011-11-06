<div class="form">

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($model); ?>

<div class="row">
    <?php echo CHtml::activeLabel($model,'name'); ?>
    <?php echo CHtml::activeTextField($model,'name',array('size'=>60,'maxlength'=>64)); ?>
</div>

<div class="action">
    <?php echo CHtml::submitButton($update ? Yii::t('lan','Save') : Yii::t('lan','Create')); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div>
