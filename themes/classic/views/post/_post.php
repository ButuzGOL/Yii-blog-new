<div class="post">
    <?php if(!$model->titleLink): ?>
        <div class="title">
            <?php echo CHtml::link(CHtml::encode($model->title),array('post/show','slug'=>$model->slug)); ?>
        </div>
    <?php else: ?>
        <div class="titlelink">
            <?php echo CHtml::link(CHtml::encode($model->title),$model->titleLink); ?>
        </div>
    <?php endif; ?>
    <div class="author">
        <?php echo Yii::t('lan','posted by'); ?> <?php echo (($model->author->username) ? $model->author->username:$model->authorName).' '.Yii::t('lan','on').' '.Yii::t('lan',date('F',$model->createTime)).date(' j, Y',$model->createTime); ?>
    </div>
    <div class="content">
        <?php echo $model->contentshort; ?>
    </div>
    <div class="nav">
        <?php if($model->category): ?>
            <b><?php echo Yii::t('lan','Category'); ?>:</b> 
            <?php echo CHtml::link(CHtml::encode($model->category->name),array('category/show','slug'=>$model->category->slug)); ?>
        <?php endif; ?>
        <b><?php echo Yii::t('lan','Tags'); ?>:</b>
        <?php echo Post::getTagLinks($model); ?>
        <br/>
        <?php if($model->contentbig): ?>
            <?php echo CHtml::link(Yii::t('lan','Read more'),array('post/show','slug'=>$model->slug,'#'=>'post-more')); ?> |
        <?php endif; ?>
        <?php echo CHtml::link(Yii::t('lan','Comments')." ({$model->commentCount})",array('post/show','slug'=>$model->slug,'#'=>'comments')); ?> | 
        <?php if(!Yii::app()->user->isGuest): ?>
            <?php echo CHtml::ajaxLink((($model->bookmarks)?Yii::t('lan','Delete'):Yii::t('lan','Add')).' '.Yii::t('lan','Bookmark'),
                    $this->createUrl('post/ajaxBookmark',array('id'=>$model->id)),
                    array('success'=>'function(msg){ pThis.html(msg+" '.Yii::t('lan','Bookmark').'") }'),
                    array('onclick'=>'var pThis=$(this);')); ?> |
        <?php endif; ?>
        <?php if(Yii::app()->user->status==User::STATUS_ADMIN || Yii::app()->user->status==User::STATUS_WRITER): ?>
            <?php echo CHtml::link(Yii::t('lan','Update'),array('post/update','id'=>$model->id)); ?> |
            <?php echo CHtml::linkButton(Yii::t('lan','Delete'),array(
                'submit'=>array('post/delete','id'=>$model->id),
                'confirm'=>Yii::t('lan','Are you sure to delete this post ?'),
            )); ?> |
        <?php endif; ?>
        <?php echo Yii::t('lan','Last updated on'); ?> <?php echo Yii::t('lan',date('F',$model->updateTime)).date(' j, Y',$model->updateTime); ?>
        <?php echo CHtml::link(Yii::t('lan','In Twitter'),'http://twitter.com/home/?status='.$this->createUrl('post/show',array('slug'=>$model->slug)).' '.$model->title.' %23pamparam'); ?>
    </div>
</div>
