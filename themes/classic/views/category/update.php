<h2><?php echo Yii::t('lan','Update Category'); ?> <?php echo CHtml::link('#'.$model->id, array('category/show','slug'=>$model->slug)); ?> <?php echo CHtml::link(Yii::t('lan','Manage Categories'), array('admin')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>true,
)); ?>
