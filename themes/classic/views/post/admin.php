<h2><?php echo Yii::t('lan','Manage Posts'); ?> <?php echo CHtml::link(Yii::t('lan','New Post'), array('create')); ?></h2>

<table class="dataGrid">
    <tr>
        <th><?php echo $sort->link('status'); ?></th>
        <th><?php echo $sort->link('categoryId'); ?></th>
        <th><?php echo $sort->link('title'); ?></th>
        <th><?php echo $sort->link('author'); ?></th>
        <th><?php echo $sort->link('createTime'); ?></th>
        <th><?php echo $sort->link('updateTime'); ?></th>
        <th><?php echo $sort->link('commentCount'); ?></th>
        <th><?php echo Yii::t('lan','Actions'); ?></th>
    </tr>
    <?php foreach($models as $n=>$model): ?>
        <tr class="<?php echo $n%2?'even':'odd';?>">
            <td>
                <?php echo CHtml::ajaxLink($model->statusText,
                    $this->createUrl('post/ajaxStatus',array('id'=>$model->id)),
                    array('success'=>'function(msg){ pThis.html(msg); }'),
                    array('onclick'=>'var pThis=$(this);')); ?>
            </td>
            <td><?php echo CHtml::link(CHtml::encode($model->category->name),array('category/show','slug'=>$model->category->slug)); ?></td>
            <td><?php echo CHtml::link(CHtml::encode($model->title),array('show','slug'=>$model->slug)); ?></td>
            <td><?php echo (($model->author->username) ? CHtml::link($model->author->username,array('user/show', 'id'=>$model->authorId)):$model->authorName); ?></td>
            <td><?php echo Yii::t('lan',date('F',$model->createTime)).date(' j, Y',$model->createTime); ?></td>
            <td><?php echo Yii::t('lan',date('F',$model->updateTime)).date(' j, Y',$model->updateTime); ?></td>
            <td><?php echo $model->commentCount; ?></td>
            <td>
                <?php echo CHtml::link(Yii::t('lan','Update'),array('update','id'=>$model->id)); ?>
                <?php echo CHtml::linkButton(Yii::t('lan','Delete'),array(
                    'submit'=>'',
                    'params'=>array('command'=>'delete','id'=>$model->id),
                    'confirm'=>Yii::t('lan','Are you sure to delete')." {$model->title} ?")); ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
