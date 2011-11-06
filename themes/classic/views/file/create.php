<h2><?php echo Yii::t('lan','New File'); ?> <?php echo CHtml::link(Yii::t('lan','Manage Files'), array('admin')); ?></h2>

<?php Yii::app()->clientScript->registerCSSFile(Yii::app()->baseUrl.'/js/swfupload/default.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/swfupload/swfupload.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/swfupload/swfupload.queue.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/swfupload/fileprogress.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/swfupload/handlers.js', CClientScript::POS_HEAD); ?>

<script type="text/javascript">
/*<![CDATA[*/
var swfu;
window.onload = function() {
    var settings = {
        flash_url : "<?php echo Yii::app()->baseUrl.'/js/swfupload/swfupload.swf'; ?>",
        upload_url: "<?php echo $this->createUrl('file/upload'); ?>",
        post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},
        file_size_limit : "2 MB",
        file_types : "*.*",
        file_types_description : "All Files",
        file_upload_limit : 100,
        file_queue_limit : 0,
        custom_settings : {
            progressTarget : "fsUploadProgress",
            cancelButtonId : "btnCancel"
        },
        debug: false,

        button_image_url: "<?php echo Yii::app()->theme->baseUrl.'/images/upload.png'; ?>",
        button_width: "100",
        button_height: "29",
        button_placeholder_id: "spanButtonPlaceHolder",
        button_text: '<span class="theFont"><?php echo Yii::t('lan','Upload'); ?></span>',
        button_text_style: ".theFont { font-size: 16px; font-weight:bold; text-align:center; }",
        button_text_left_padding: 2,
        button_text_top_padding: 3,
        button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
        
        file_queued_handler : fileQueued,
        file_queue_error_handler : fileQueueError,
        file_dialog_complete_handler : fileDialogComplete,
        upload_start_handler : uploadStart,
        upload_progress_handler : uploadProgress,
        upload_error_handler : uploadError,
        upload_success_handler : uploadSuccess,
        upload_complete_handler : uploadComplete,
        queue_complete_handler : queueComplete
    };
    swfu = new SWFUpload(settings);
 };
/*]]>*/
</script>
<div class="form">
    <?php echo CHtml::beginForm('','post',array('enctype'=>'multipart/form-data')); ?>
        <div id="fsUploadProgress"></div>
        <div>
            <span id="spanButtonPlaceHolder"></span>
            <input id="btnCancel" type="button" value="<?php echo Yii::t('lan','Cancel All Uploads'); ?>" onclick="swfu.cancelQueue();" disabled="disabled" />
        </div>
    <?php echo CHtml::endForm(); ?>
</div>
<br />
<table class="dataGrid" style="display:none;">
    <tr>
        <th><?php echo Yii::t('lan','File'); ?></th>
        <th><?php echo Yii::t('lan','Name'); ?></th>
        <th><?php echo Yii::t('lan','Type'); ?></th>
        <th><?php echo Yii::t('lan','Actions'); ?></th>
    </tr>
</table>

