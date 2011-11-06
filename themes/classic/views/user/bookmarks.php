<?php foreach($models as $model): ?>
    <?php if($model->post) $this->renderPartial('../post/_post',array(
        'model'=>$model->post,
    )); ?>
<?php endforeach; ?>

<br/>
<?php $this->widget('CLinkPager',array('pages'=>$pages)); ?>
