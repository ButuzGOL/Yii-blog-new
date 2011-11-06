<?php echo CHtml::beginForm(); ?>
<div class="row">
<?php echo CHtml::activeLabel($form,'username'); ?>
<br/>
<?php echo CHtml::activeTextField($form,'username') ?>
<?php echo CHtml::error($form,'username'); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($form,'password'); ?>
<br/>
<?php echo CHtml::activePasswordField($form,'password') ?>
<?php echo CHtml::error($form,'password'); ?>
</div>
<div class="row">
<?php echo CHtml::activeCheckBox($form,'rememberMe'); ?>
<?php echo CHtml::activeLabel($form,'rememberMe'); ?>
</div>
<div class="row">
<?php echo CHtml::submitButton(Yii::t('lan','Login'),array('name'=>'loginWidget')); ?>
</div>
<?php echo CHtml::endForm(); ?>

<?php echo CHtml::link(Yii::t('lan','Registration'), array('user/registration')); ?><br />
<?php echo CHtml::link(Yii::t('lan','Lost password ?'), array('user/lostpass')); ?>
