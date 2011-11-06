<h2><?php echo Yii::t('lan','New User'); ?> <?php echo CHtml::link(Yii::t('lan','Users List'), array('list')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>false,
)); ?>
