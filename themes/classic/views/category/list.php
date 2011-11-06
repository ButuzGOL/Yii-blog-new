<h2><?php echo Yii::t('lan','Categories List'); ?></h2>

<?php foreach($models as $n=>$model): ?>
    <?php echo (count($model->posts)) ? CHtml::link(CHtml::encode($model->name).' ('.count($model->posts).')',array('category/show','slug'=>$model->slug)):CHtml::encode($model->name).' ('.count($model->posts).')'; ?>
    <br/>
<?php endforeach; ?>
