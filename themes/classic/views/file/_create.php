<?php
$type=explode('/',$model->type);
if($type[0]=='image')
{
    $whtext=File::getHOW(Yii::app()->params['filePath'].$model->name);
    $url=Yii::app()->baseUrl.'/uploads/file/'.$model->name;
}
?>

<tr class="odd">
    <td align="center"><?php echo ($type[0]=='image') ? CHtml::image($url, $model->alt,($whtext)?array($whtext=>Yii::app()->params['imageThumbnailBoundingbox']):'') : CHtml::image(Yii::app()->theme->baseUrl.'/images/file.png'); ?></td>
    <td><?php echo $model->name; ?></td>
    <td><?php echo $model->type; ?></td>
    <td>
        <?php echo CHtml::link(Yii::t('lan','Update'),array('update','id'=>$model->id)); ?>
    </td>
</tr>
