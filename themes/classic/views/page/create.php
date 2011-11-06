<h2 id="preview-page"><?php echo Yii::t('lan','New Page'); ?> <?php echo CHtml::link(Yii::t('lan','Manage Pages'), array('admin')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>false,
)); ?>
