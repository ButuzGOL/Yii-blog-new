<h2 id="form-comment"><?php echo Yii::t('lan','Update Comment'); ?> <?php echo CHtml::link('#'.$model->post->id,array('post/show','slug'=>$model->post->slug,'#'=>'c'.$model->id)); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>true,
)); ?>
