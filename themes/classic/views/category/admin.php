<h2><?php echo Yii::t('lan','Manage Categories'); ?> <?php echo CHtml::link(Yii::t('lan','New Category'), array('create')); ?></h2>

<table class="dataGrid">
    <tr>
        <th><?php echo $sort->link('name'); ?></th>
        <th><?php echo Yii::t('lan','Actions'); ?></th>
    </tr>
<?php foreach($models as $n=>$model): ?>
    <tr class="<?php echo $n%2?'even':'odd';?>">
        <td><?php echo CHtml::link(CHtml::encode($model->name),array('show','slug'=>$model->slug)); ?></td>
        <td>
          <?php echo CHtml::link(Yii::t('lan','Update'),array('update','id'=>$model->id)); ?>
          <?php echo CHtml::linkButton(Yii::t('lan','Delete'),array(
              'submit'=>'',
              'params'=>array('command'=>'delete','id'=>$model->id),
              'confirm'=>Yii::t('lan','Are you sure to delete')." {$model->name} ?")); ?>
        </td>
  </tr>
<?php endforeach; ?>
</table>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
