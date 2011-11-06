<div class="post">
    <div class="title">
        <?php echo CHtml::encode($model->title); ?>
    </div>
    <div class="author">
        <?php if(Yii::app()->user->status==User::STATUS_ADMIN || Yii::app()->user->status==User::STATUS_WRITER): ?>
            [<?php echo CHtml::ajaxLink($model->statusText,
                    $this->createUrl('page/ajaxStatus',array('id'=>$model->id)),
                    array('success'=>'function(msg){ pThis.html(msg); }'),
                    array('onclick'=>'var pThis=$(this);')); ?>]
        <?php endif; ?>
        <?php echo Yii::t('lan','posted by'); ?> <?php echo (($model->author->username) ? CHtml::link($model->author->username,array('user/show', 'id'=>$model->authorId)):$model->authorName).' '.Yii::t('lan','on').' '.Yii::t('lan',date('F',$model->createTime)).date(' j, Y',$model->createTime); ?>
    </div>
    <div class="content">
        <?php echo $model->content; ?>
    </div>
    <div class="nav">
        <?php if(Yii::app()->user->status==User::STATUS_ADMIN || Yii::app()->user->status==User::STATUS_WRITER): ?>
            <?php echo CHtml::link(Yii::t('lan','Update'),array('page/update','id'=>$model->id)); ?> |
            <?php echo CHtml::linkButton(Yii::t('lan','Delete'),array(
               'submit'=>array('page/delete','id'=>$model->id),
               'confirm'=>Yii::t('lan','Are you sure to delete this page ?'),
            )); ?> |
        <?php endif; ?>
        <?php echo Yii::t('lan','Last updated on'); ?> <?php echo Yii::t('lan',date('F',$model->updateTime)).date(' j, Y',$model->updateTime); ?>
    </div>
</div>
