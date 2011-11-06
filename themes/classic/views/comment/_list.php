<?php foreach($models as $model): ?>

<?php if(Yii::app()->user->status!=User::STATUS_ADMIN && Yii::app()->user->status!=User::STATUS_WRITER && $model->status==Comment::STATUS_PENDING) continue; ?>

<div class="comment" id="c<?php echo $model->id; ?>">
    <?php echo CHtml::link("#{$model->id}",array('post/show','slug'=>$model->post->slug,'#'=>'c'.$model->id),array('class'=>'cid')); ?>
    
    <div class="avatar">
    
        <img src="<?php echo Yii::app()->baseUrl.'/uploads/avatar/'.(($model->author->avatar)?$model->author->avatar:Yii::app()->params['noAvatar']); ?>" alt="<?php echo ($model->author->username)?$model->author->username:$model->authorName; ?>" title="<?php echo ($model->author->username)?$model->author->username:$model->authorName; ?>" />
    
    </div>
    <div class="info">
        <div class="author"><?php echo (($model->author->username)?CHtml::link($model->author->username,array('user/show','id'=>$model->author->id)):$model->authorName); ?></div>
        <div class="time">
        
            <?php if(Yii::app()->user->status==User::STATUS_ADMIN || Yii::app()->user->status==User::STATUS_WRITER): ?>
                <?php if($model->status==Comment::STATUS_PENDING): ?>
                    <?php echo CHtml::ajaxLink(Yii::t('lan','Pending approval'),
                    $this->createUrl('comment/ajaxApprove',array('id'=>$model->id)),
                    array('success'=>'function(msg){ pThis.hide(); }'),
                    array('onclick'=>'var pThis=$(this);','class'=>'pending')); ?> 
                <?php endif; ?>
                <?php echo CHtml::link(Yii::t('lan','Update'),array('comment/update','id'=>$model->id)); ?> |
                <?php echo CHtml::ajaxLink(Yii::t('lan','Delete'),
                    $this->createUrl('comment/ajaxDelete',array('id'=>$model->id)),
                    array('success'=>'function(msg){ $("#c'.$model->id.'").animate({ opacity: "hide" }, "slow"); }')); ?> |
            <?php endif; ?>
        
            <?php echo Yii::t('lan',date('F',$model->createTime)).date(' j, Y H:i ',$model->createTime); ?> |
            <?php echo (($model->author->email)?$model->author->email:$model->email); ?>
            
        </div>
        <div class="content"><?php echo $model->contentDisplay; ?></div>
    </div>
</div>

<?php endforeach; ?>
