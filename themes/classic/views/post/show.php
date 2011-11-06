<?php $this->renderPartial('_postbig',array(
    'model'=>$model,
)); ?>

<div id="comments">

    <?php if($model->commentCount>=1): ?>
        <h3>
          <?php echo $model->commentCount>1 ? $model->commentCount.' '.Yii::t('lan','comments') : Yii::t('lan','One comment'); ?>
        </h3>
    <?php endif; ?>

    <?php $this->renderPartial('/comment/_list',array(
        'models'=>$comments,
        'post'=>$model,
    )); ?>

    <h3 id="form-comment"><?php echo Yii::t('lan','Leave a Comment'); ?></h3>

    <?php $this->renderPartial('/comment/_form',array(
        'model'=>$newComment,
        'update'=>false,
    )); ?>

</div>
