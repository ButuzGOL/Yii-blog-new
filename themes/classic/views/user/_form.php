<div class='form'>

<?php echo CHtml::beginForm('','post',array('enctype'=>'multipart/form-data')); ?>

<?php echo CHtml::errorSummary($model); ?>

<?php if(Yii::app()->user->status==User::STATUS_ADMIN): ?>
    <div class='row'>
        <?php echo CHtml::activeLabel($model,'username'); ?>
        <?php echo CHtml::activeTextField($model,'username',array('size'=>50,'maxlength'=>50)); ?>
    </div>
<?php endif; ?>
<div class='row'>
    <?php echo CHtml::activeLabel($model,'password',array(($update)?'':'class'=>'required')); ?>
    <?php echo CHtml::activeTextField($model,'password',array('size'=>32,'maxlength'=>32, 'value'=>($update && !$_POST) ? '' : $model->password)); ?>
</div>
<div class='row'>
    <?php echo CHtml::activeLabel($model,'email'); ?>
    <?php echo CHtml::activeTextField($model,'email',array('size'=>60,'maxlength'=>64)); ?>
</div>
<div class='row'>
    <?php echo CHtml::activeLabel($model,'url'); ?>
    <?php echo CHtml::activeTextField($model,'url',array('size'=>60,'maxlength'=>64)); ?>
</div>

<?php if(Yii::app()->user->status==User::STATUS_ADMIN): ?>
    <div class='row'>
        <?php echo CHtml::activeLabel($model,'status'); ?>
        <?php echo CHtml::activeDropDownList($model,'status',User::model()->statusOptions); ?>
    </div>

    <div class='row'>
        <?php echo CHtml::activeLabel($model,'banned'); ?>
        <?php $result = CHtml::activeRadioButtonList($model,'banned',User::model()->bannedOptions,array('separator'=>'')); ?>
        <?php echo str_replace('label','span',$result); ?>
    </div>
<?php endif; ?>

<div class='row'>
    <?php echo CHtml::activeLabel($model,'avatar'); ?>
    <?php echo CHtml::activeFileField($model,'avatar'); ?>
    <?php if($update) echo CHtml::checkBox('davatar').Yii::t('lan','Check to delete avatar.'); ?>
</div>
<div class='row'>
    <?php echo CHtml::activeLabel($model,'about'); ?>
    <?php echo CHtml::activeTextArea($model,'about',array('rows'=>6, 'cols'=>50)); ?>
</div>

<div class='action'>
    <?php echo CHtml::submitButton($update ? Yii::t('lan','Save') : Yii::t('lan','Create')); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div>
