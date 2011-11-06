<h2><?php echo Yii::t('lan','Posts in category'); ?> "<?php echo $model->name; ?>"</h2>

<?php foreach($models as $model): ?>
    <?php $this->renderPartial('../post/_post',array(
        'model'=>$model,
    )); ?>
<?php endforeach; ?>

<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
