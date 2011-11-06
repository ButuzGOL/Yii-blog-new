<h3><?php echo Yii::t('lan','Posts Issued on'); ?> "<?php echo Yii::t('lan',date('F',$theDay)).date(' j, Y',$theDay); ?>"</h3>

<?php foreach($models as $model): ?>
    <?php $this->renderPartial('_post',array(
        'model'=>$model,
    )); ?>
<?php endforeach; ?>

<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
