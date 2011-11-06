<h2><?php echo Yii::t('lan','Login'); ?></h2>

<div class='form'>

<?php echo CHtml::beginForm('','post'); ?>

<?php echo CHtml::errorSummary($model); ?>

<div class='row'>
    <?php echo CHtml::activeLabel($model,'username'); ?>
    <?php echo CHtml::activeTextField($model,'username',array('size'=>50,'maxlength'=>50)); ?>
</div>
<div class='row'>
    <?php echo CHtml::activeLabel($model,'password'); ?>
    <?php echo CHtml::activePasswordField($model,'password',array('size'=>32,'maxlength'=>32)); ?>
</div>
<div style="font-weight:bold;">
<?php echo CHtml::activeCheckBox($model,'rememberMe'); ?>
<?php echo CHtml::activeLabel($model,'rememberMe'); ?>
</div>
<div class='action'>
    <?php echo CHtml::submitButton(Yii::t('lan','Login'),array('name'=>'loginController')); ?>
</div>

<?php echo CHtml::endForm(); ?>

<br />
<?php echo CHtml::link(Yii::t('lan','Registration'), array('user/registration')); ?><br />
<?php echo CHtml::link(Yii::t('lan','Lost password ?'), array('user/lostpass')); ?>
</div>
