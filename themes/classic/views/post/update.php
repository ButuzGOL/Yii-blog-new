<h2 id="preview-post"><?php echo Yii::t('lan','Update Post'); ?> <?php echo CHtml::link('#'.$model->id, array('post/show','slug'=>$model->slug)); ?> <?php echo CHtml::link(Yii::t('lan','Manage Posts'), array('admin')); ?></h2>

<?php $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>true,
)); ?>
