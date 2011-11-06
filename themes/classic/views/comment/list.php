<h2><?php echo Yii::t('lan','Comments Pending Approval'); ?></h2>

<?php if(Comment::model()->pendingCommentCount>=1): ?>
        <h3>
          <?php echo Comment::model()->pendingCommentCount>1 ? Comment::model()->pendingCommentCount.' '.Yii::t('lan','comments') : Yii::t('lan','One comment'); ?>
        </h3>
    <?php endif; ?>

<?php $this->renderPartial('_list',array(
    'models'=>$models,
)); ?>

<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
