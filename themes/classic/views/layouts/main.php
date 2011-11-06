<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo (($this->pageTitle)?$this->pageTitle.' - ':''); ?> <?php echo Yii::app()->params['title']; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="<?php echo CHtml::encode(Yii::app()->params['description']); ?>" />
<meta name="keywords" content="<?php echo CHtml::encode(Yii::app()->params['keywords']); ?>" />
<?php echo CHtml::cssFile(Yii::app()->theme->baseUrl.'/css/style.css'); ?>
<?php echo CHtml::cssFile(Yii::app()->baseUrl.'/js/highslide/highslide.css'); ?>
<?php Yii::app()->clientScript->registerLinkTag('alternate','application/rss+xml',$this->createUrl('site/postFeed')); ?>
<?php Yii::app()->clientScript->registerLinkTag('alternate','application/rss+xml',$this->createUrl('site/commentFeed')); ?>


<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/highslide/highslide.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/highslide/highslide_eh.js', CClientScript::POS_HEAD); ?>
</head>

<body class="page">

<div id="container">
    <div id="header">
        <h1><?php echo CHtml::link(Yii::app()->params['title'],Yii::app()->homeUrl); ?></h1>
        <h3><?php echo CHtml::encode(Yii::app()->params['description']); ?></h3>
    </div>

    <div id="sidebar">

        <?php $this->widget('UserLogin',array('visible'=>Yii::app()->user->isGuest)); ?>
        <?php $this->widget('UserMenu',array('visible'=>!Yii::app()->user->isGuest)); ?>
        <?php $this->widget('SiteSearch'); ?>
        <?php $this->widget('TagCloud'); ?>
        <?php $this->widget('Links'); ?>
        <?php $this->widget('RecentComments'); ?>
        <?php $this->widget('MonthlyArchives'); ?>
        <?php $this->widget('RecentPosts'); ?>
        <?php $this->widget('PopularPosts'); ?>
        <?php $this->widget('Categories'); ?>

    </div>

    <div id="content">
        <?php if(Yii::app()->user->hasFlash('message')): ?>
            <br />
            <div class="form">
                <?php echo Yii::app()->user->getFlash('message'); ?>
            </div>
        <?php endif; ?>
        <?php echo $content; ?>
    </div>

    <br class="clearfloat" />

    <div id="footer">
        <p><?php echo Yii::app()->params['copyrightInfo']; ?><br/>
        All Rights Reserved.<br/>
        <?php echo Yii::powered(); ?></p>
    </div>
</div>
<script type="text/javascript">
/* <![CDATA[ */
    hs.graphicsDir = '<?php echo Yii::app()->request->baseUrl; ?>/js/highslide/graphics/';
    hs.outlineType = 'rounded-white';
    hs.showCredits = false;
    hs.captionEval = 'this.thumb.alt';
    hs.wrapperClassName = 'draggable-header';
    addHighSlideAttribute();
/* ]]> */
</script>
</body>
</html>

