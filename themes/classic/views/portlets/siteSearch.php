<?php echo CHtml::beginForm($this->getController()->createUrl('post/search')); ?>
<div class="row">
    <?php echo CHtml::activeTextField($form,'keyword',array('onclick'=>"this.value=''",'onblur'=>"if(this.value=='') this.value='Search...';")); ?>
    <?php echo CHtml::SubmitButton(Yii::t('lan','Start Search')); ?>
</div>
<?php echo CHtml::endForm(); ?>
