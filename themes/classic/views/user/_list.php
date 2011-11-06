<?php foreach($models as $model): ?>

<?php if(Yii::app()->user->status!=User::STATUS_ADMIN && $model->confirmRegistration) continue; ?>

<div class="profile" id="c<?php echo $model->id; ?>">
    <?php echo CHtml::link("#{$model->id}",array('user/list','#'=>'c'.$model->id),array('class'=>'cid')); ?>
    <div class="avatar">
        <img src="<?php echo Yii::app()->baseUrl.'/uploads/avatar/'.(($model->avatar)?$model->avatar:Yii::app()->params['noAvatar']); ?>" alt="<?php echo $model->username; ?>" title="<?php echo $model->username; ?>" />
    </div>
    <div class="info">
        <div><?php echo Yii::t('lan','Username'); ?>: <?php echo CHtml::link($model->username, array('user/show','id'=>$model->id)); ?></div>
        <div>Email: <?php echo $model->email; ?></div>
        <?php if($model->url): ?>
            <div>Url: <?php echo CHtml::link($model->url, $model->url); ?></div>
        <?php endif; ?>
        <div><?php echo Yii::t('lan','Status'); ?>: <?php echo $model->statusText; ?></div>
        <div><?php echo Yii::t('lan','Banned'); ?>: <?php if($model->id!=1 && Yii::app()->user->status==User::STATUS_ADMIN): ?>
            <?php echo CHtml::ajaxLink($model->bannedText,
                    $this->createUrl('user/ajaxBanned',array('id'=>$model->id)),
                    array('success'=>'function(msg){ pThis.html(msg); }'),
                    array('onclick'=>'var pThis=$(this);')); ?>
            <?php else: echo $model->bannedText; endif; ?>
        </div>
        <?php if($model->about): ?>
            <div><?php echo Yii::t('lan','About'); ?>: <?php echo CHtml::encode($model->about); ?></div>
        <?php endif; ?>
        <?php if($model->confirmRegistration): ?>
            <div class="pending"><?php echo Yii::t('lan','Need to confirm email.'); ?></div>
        <?php endif; ?>
        <?php if(Yii::app()->user->status==User::STATUS_ADMIN): ?>
              <?php echo CHtml::link(Yii::t('lan','Update'),array('user/update','id'=>$model->id)); ?> |
              <?php echo CHtml::linkButton(Yii::t('lan','Delete'), array('submit'=>array('user/delete','id'=>$model->id))); ?>
        <?php endif; ?>
    </div>
</div>

<?php endforeach; ?>
