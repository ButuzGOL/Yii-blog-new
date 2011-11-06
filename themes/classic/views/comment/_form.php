<?php if(Yii::app()->user->hasFlash('commentSubmittedMessage')): ?>
    <div class="form">
        <?php echo Yii::app()->user->getFlash('commentSubmittedMessage'); ?>
    </div><br />
<?php endif; ?>

<?php if(isset($_POST['previewComment']) && !$model->hasErrors()): ?>
    <h3><?php echo Yii::t('lan','Preview'); ?></h3>
    <div class="comment">
        <div class="avatar">
            <img src="<?php echo Yii::app()->baseUrl.'/uploads/avatar/'.((User::model()->findByPK($model->authorId)->avatar)?User::model()->findByPK($model->authorId)->avatar:Yii::app()->params['noAvatar']); ?>" alt="<?php echo ((User::model()->findByPK($model->authorId)->username)?User::model()->findByPK($model->authorId)->username:$model->authorName); ?>" title="<?php echo ((User::model()->findByPK($model->authorId)->username)?User::model()->findByPK($model->authorId)->username:$model->authorName); ?>" />
        </div>
        <div class="info">
            <div class="author"><?php echo ((User::model()->findByPK($model->authorId)->username)?User::model()->findByPK($model->authorId)->username:$model->authorName); ?></div>
            <div class="time">
                <?php echo Yii::t('lan',date('F',$model->createTime)).date(' j, Y H:i ',$model->createTime); ?> |
                <?php echo ((User::model()->findByPK($model->authorId)->email)?User::model()->findByPK($model->authorId)->email:$model->email); ?>
            </div>
            <div class="content"><?php echo $model->contentDisplay; ?></div>
        </div>
    </div>
<?php endif; ?>

<div class="form">

<?php echo CHtml::beginForm('#form-comment'); ?>

<?php echo CHtml::errorSummary($model); ?>

<?php if(Yii::app()->user->isGuest): ?>
    <div class="row">
        <?php echo CHtml::activeLabel($model,'authorName'); ?>
        <?php echo CHtml::activeTextField($model,'authorName',array('size'=>50,'maxlength'=>50)); ?>
    </div>
    <div class="row">
        <?php echo CHtml::activeLabel($model,'email'); ?>
        <?php echo CHtml::activeTextField($model,'email',array('size'=>60,'maxlength'=>64)); ?>
    </div>
<?php endif; ?>

<div class="row">
    <?php echo CHtml::activeLabel($model,'content'); ?>
    <?php echo CHtml::activeTextArea($model,'content',array('rows'=>6, 'cols'=>50)); ?>
    <p class="hint">
        <?php echo Yii::t('lan','You may use'); ?> <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">Markdown syntax</a>.
    </p>
</div>

<?php if(Yii::app()->user->isGuest && extension_loaded('gd')): ?>
    <div class="row">
        <?php echo CHtml::activeLabel($model,'verifyCode'); ?>
        <div>
            <?php $this->widget('CCaptcha'); ?>
            <?php echo CHtml::activeTextField($model,'verifyCode'); ?>
        </div>
    </div>
<?php endif; ?>

<div class="action">
    <?php echo CHtml::submitButton($update ? Yii::t('lan','Save') : Yii::t('lan','Create'),array('name'=>'submitComment')); ?>
    <?php echo CHtml::submitButton(Yii::t('lan','Preview'),array('name'=>'previewComment')); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div>
