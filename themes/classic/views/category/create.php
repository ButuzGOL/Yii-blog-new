<h2><?php echo Yii::t('lan','New Category'); ?> <?php echo CHtml::link(Yii::t('lan','Manage Categories'), array('admin')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>false,
)); ?>
