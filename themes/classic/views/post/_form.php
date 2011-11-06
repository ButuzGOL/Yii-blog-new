<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/thickbox/thickbox.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerCSSFile(Yii::app()->request->baseUrl.'/js/thickbox/thickbox.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/ckeditor/ckeditor.js', CClientScript::POS_HEAD); ?>

<?php if(isset($_POST['previewPost']) && !$model->hasErrors()): ?>
<h3><?php echo Yii::t('lan','Preview'); ?></h3>
<div class="post">
    <div class="title"><?php echo CHtml::encode($model->title); ?></div>
    <div class="author"><?php echo Yii::t('lan','posted by'); ?> <?php echo Yii::app()->user->username.' '.Yii::t('lan','on').' '.Yii::t('lan',date('F',$model->createTime)).date(' j, Y',$model->createTime); ?></div>
    <div class="content" id="prevshort"><?php echo $model->contentshort; ?></div>
    <div class="content" id="prevbig" style="display:none;"><?php echo $model->contentbig; ?></div>
    <div class="nav">
        <?php if($model->categoryId): ?>
            <b><?php echo Yii::t('lan','Category'); ?>:</b> 
            <?php echo CHtml::encode(Category::model()->findByPK($model->categoryId)->name); ?>
        <?php endif; ?>
        <b><?php echo Yii::t('lan','Tags'); ?>:</b>
        <?php echo $model->tags; ?>
        <br/>
        <?php if($model->contentbig): ?>
            <?php echo CHtml::link(Yii::t('lan','Read more'),'#preview-post',array('id'=>'prevread')); ?> |
        <?php endif; ?>
        <?php echo Yii::t('lan','Last updated on');?> <?php echo Yii::t('lan',date('F',$model->updateTime)).date(' j, Y',$model->updateTime); ?>
    </div>
</div>
<?php endif; ?>

<div class="form">

<?php echo CHtml::beginForm('#preview-post'); ?>

<?php echo CHtml::errorSummary($model); ?>

<div class="row">
    <?php echo CHtml::activeLabel($model,'title'); ?>
    <?php echo CHtml::activeTextField($model,'title',array('size'=>60,'maxlength'=>128,'id'=>'title')); ?>
</div>
<div class="row">
    <?php echo CHtml::activeLabel($model,'titleLink'); ?>
    <?php echo CHtml::activeTextField($model,'titleLink',array('size'=>60,'maxlength'=>128)); ?>
</div>
<div class="row">
    <?php echo CHtml::activeLabel($model,'categoryId'); ?>
    <?php echo CHtml::activeDropDownList($model,'categoryId',CHtml::listData(Category::model()->findAll(array('select'=>'id, name')),'id','name'),array('prompt'=>'')); ?>
</div>
<div class="row">
    <?php echo CHtml::activeLabel($model,'content'); ?>
    <?php echo CHtml::activeTextArea($model,'content',array('rows'=>6, 'cols'=>70,'id'=>'go')); ?>
    
</div>
<div class="row">
    <?php echo CHtml::activeLabel($model,'tags'); ?>
    <?php echo CHtml::activeTextField($model,'tags',array('size'=>60)); ?>
    <p class="hint">
        <?php echo Yii::t('lan','Separate different tags with commas.'); ?>
    </p>
</div>

<?php if(Yii::app()->user->status==User::STATUS_ADMIN || Yii::app()->user->status==User::STATUS_WRITER): ?>
    <div class="row">
        <?php echo CHtml::activeLabel($model,'status'); ?>
        <?php echo CHtml::activeDropDownList($model,'status',Post::model()->statusOptions); ?>
    </div>
<?php endif; ?>

<div class="action">
    <?php echo CHtml::submitButton($update ? Yii::t('lan','Save') : Yii::t('lan','Create'),array('name'=>'submitPost')); ?>
    <?php echo CHtml::submitButton(Yii::t('lan','Preview'),array('name'=>'previewPost')); ?>
</div>

<?php echo CHtml::endForm(); ?>

</div>

<script type="text/javascript">
/*<![CDATA[*/
var bs=false;
$(document).ready(function(){
    $('#prevread').click(function() {
        if(bs==true) {
            $(this).html("<?php echo Yii::t('lan','Read more'); ?>"); 
            $('#prevbig').attr('style', 'display:none'); 
            $('#prevshort').attr('style', 'display:');
        }
        else {
            $(this).html("<?php echo Yii::t('lan','Read less'); ?>"); 
            $('#prevshort').attr('style', 'display:none'); 
            $('#prevbig').attr('style', 'display:');
        }
        bs=!bs;
    });
});
/*]]>*/
</script>
<script type="text/javascript">
/*<![CDATA[*/

CKEDITOR.config.resize_minWidth = 570;
CKEDITOR.config.language = '<?php echo Yii::t('lan','en'); ?>';
var insertimageorfile="<?php echo $this->createUrl('filem/admin', array('TB_iframe'=>true,'height'=>350)); ?>"
<?php if(Yii::app()->user->status==User::STATUS_ADMIN || Yii::app()->user->status==User::STATUS_WRITER): ?>
CKEDITOR.config.toolbar=
    [
        ['TagMore'],['Maximize'],['Source'],['Bold','Italic','Underline','Strike'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink'],
        '/',
        ['PasteText','PasteFromWord'],['Undo','Redo'],
        ['Format'],
        ['TextColor','BGColor'],
        ['Image','Flash','Table','SpecialChar'],
        ['InsertImageOrFile']
    ];
<?php else: ?>
CKEDITOR.config.toolbar=
    [
        ['TagMore'],['Maximize'],['Source'],['Bold','Italic','Underline','Strike'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink'],
        '/',
        ['PasteText','PasteFromWord'],['Undo','Redo'],
        ['Format'],
        ['TextColor','BGColor'],
        ['Image','Flash','Table','SpecialChar']
    ];
<?php endif; ?>
editor = CKEDITOR.replace('Post[content]');
/*]]>*/
</script>
