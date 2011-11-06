<h2 id="preview-page"><?php echo Yii::t('lan','Update Page'); ?> <?php echo CHtml::link('#'.$model->id, array('page/show','slug'=>$model->slug)); ?> <?php echo CHtml::link(Yii::t('lan','Manage Pages'), array('admin')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>true,
)); ?>
