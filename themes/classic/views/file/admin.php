<h2><?php echo Yii::t('lan','Manage Files'); ?> <?php echo CHtml::link(Yii::t('lan','New File'), array('create')); ?></h2>

<table class="dataGrid">
    <tr>
        <th><?php echo Yii::t('lan','File'); ?></th>
        <th><?php echo $sort->link('name'); ?></th>
        <th><?php echo $sort->link('type'); ?></th>
        <th><?php echo $sort->link('createTime'); ?></th>
        <th><?php echo Yii::t('lan','Actions'); ?></th>
    </tr>
    <?php foreach($models as $n=>$model): ?>

        <?php echo $this->renderPartial('_admin', array(
            'model'=>$model,
            'n'=>$n,
        )); ?>

    <?php endforeach; ?>
</table>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
