<?php echo Yii::t('lan','Hi'); ?>, <?php echo $model->username;?><br />

<br />
<?php echo Yii::t('lan','Can\'t remember your password ?'); ?> <br />
<?php echo Yii::t('lan','Please click on the link below our copy and paste the URL into your browser:'); ?><br />

<?php echo CHtml::link($this->createAbsoluteUrl('user/lostpass',array('code'=>$code)),$this->createAbsoluteUrl('user/lostpass',array('code'=>$code))); ?>
<br /><br />

<?php echo Yii::t('lan','This will reset your password. You can then login and change it to something you\'ll remember.'); ?>
