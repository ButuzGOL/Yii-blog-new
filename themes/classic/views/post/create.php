<h2 id="preview-post"><?php echo Yii::t('lan','New Post'); ?> <?php if(Yii::app()->user->status==User::STATUS_ADMIN || Yii::app()->user->status==User::STATUS_WRITER) echo CHtml::link(Yii::t('lan','Manage Posts'),array('admin')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>false,
)); ?>
