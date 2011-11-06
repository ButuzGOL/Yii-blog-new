<?php echo Yii::t('lan','Hi'); ?>, <?php echo $model->username;?><br />

<br />

<?php echo Yii::t('lan','You were successfully registered on the {baseurl} with:',array('{baseurl}'=>Yii::app()->getBaseUrl('true'))); ?><br />

<?php echo Yii::t('lan','Username'); ?>: <?php echo $model->username;?><br />

<?php echo Yii::t('lan','Password'); ?>: <?php echo $model->password;?><br />

<br />

<?php echo Yii::t('lan','Please click on the link below our copy and paste the URL into your browser:'); ?><br />

<?php echo CHtml::link($this->createAbsoluteUrl('user/registration',array('code'=>$code)),$this->createAbsoluteUrl('user/registration',array('code'=>$code))); ?>
<br /><br />

<?php echo Yii::t('lan','This will confirm you email.'); ?>
