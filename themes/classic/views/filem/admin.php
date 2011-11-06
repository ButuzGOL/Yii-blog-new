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

<script type="text/javascript">
/* <![CDATA[ */
var win = window.dialogArguments || opener || parent || top;
function insert_html(url, alt, type){
    
    if(type=='image')
        s = '<img alt="'+alt+'" src="'+url+'" />';
    else
        s = '<a href="'+url+'" title="'+alt+'">'+((alt)?alt:url)+'</a>';
    win.editor.insertHtml(s);
    win.tb_remove();
    }
/* ]]> */
</script>
