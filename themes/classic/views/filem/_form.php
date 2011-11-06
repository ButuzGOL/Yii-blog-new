<div class="form">

<?php echo CHtml::beginForm(); ?>

<?php
$type=explode('/',$model->type);
if($type[0]=='image')
{
    $whtext=File::getHOW(Yii::app()->params['filePath'].$file);
    $url=Yii::app()->baseUrl.'/uploads/file/'.$file;
}
?>

<?php echo CHtml::errorSummary($model); ?>

<?php echo ($type[0]=='image') ? (($whtext)?CHtml::link(CHtml::image($url, $model->alt, array($whtext=>Yii::app()->params['imageThumbnailBoundingbox'])), $url, array('class'=>'highslide')):CHtml::image($url, $model->alt)) : CHtml::image(Yii::app()->baseUrl.'/images/file.png'); ?>
<div class="row">
    <?php echo CHtml::activeLabel($model,'name'); ?>
    <?php echo CHtml::activeTextField($model,'name',array('size'=>60,'maxlength'=>64)); ?>
</div>
<div class="row">
    <?php echo CHtml::activeLabel($model,'alt'); ?>
    <?php echo CHtml::activeTextField($model,'alt',array('size'=>32,'maxlength'=>32)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton($update ? Yii::t('lan','Save') : Yii::t('lan','Create')); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div>
