<h2><?php echo Yii::t('lan','Update File'); ?> <?php echo CHtml::link(Yii::t('lan','Manage Files'), array('admin')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>true,
    'file'=>$file,
)); ?>
