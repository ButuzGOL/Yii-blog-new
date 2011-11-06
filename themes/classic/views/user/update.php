<h2><?php if ($model->id==Yii::app()->user->id) echo Yii::t('lan','My profile'); else echo Yii::t('lan','Update User'); ?> <?php echo CHtml::link('#'.$model->id, array('user/show','id'=>$model->id)); ?> <?php echo CHtml::link(Yii::t('lan','Users List'),array('list')); ?></h2>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model,
    'update'=>true,
)); ?>
