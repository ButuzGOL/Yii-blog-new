<?php

class SiteController extends CController
{
    /**
     * Generate Post feed.
     */
    public function actionPostFeed()
    {
        Yii::import('application.vendors.*');
        require_once('Zend/Feed/Rss.php');
        
        // retrieve the latest 20 models
        $models=Post::model()->findAll(array(
            'order'=>'createTime DESC',
            'condition'=>'status='.Post::STATUS_PUBLISHED,
            'limit'=>20,
        ));
        
        // convert to the format needed by Zend_Feed
        $entries=array();
        foreach($models as $model)
        {
            $entries[]=array(
                'title'=>$model->title,
                'link'=>CHtml::encode($this->createAbsoluteUrl('post/show',array('slug'=>$model->slug))),
                'description'=>$model->contentshort,
                'lastUpdate'=>$model->createTime,
            );
        }
        
        // generate and render RSS feed
        $feed=Zend_Feed::importArray(array(
            'title'=>Yii::t('lan','Post Feed for ').Yii::app()->params['title'],
            'description'=>Yii::app()->params['description'],
            'link'=>$this->createAbsoluteUrl(''),
            'charset'=>'UTF-8',
            'entries'=>$entries,
        ), 'rss');
        $feed->send();
    }
    
    /**
     * Generate Comment feed.
     */
    public function actionCommentFeed()
    {
        Yii::import('application.vendors.*');
        require_once('Zend/Feed/Rss.php');
        
        // retrieve the latest 20 models
        $models=Comment::model()->findRecentComments(20);
        
        // convert to the format needed by Zend_Feed
        $entries=array();
        foreach($models as $model)
        {
            $entries[]=array(
                'title'=>(($model->author)?$model->author->username:$model->authorName).' '.Yii::t('lan','on').' '.CHtml::encode($model->post->title),
                'link'=>CHtml::encode($this->createAbsoluteUrl('post/show',array('slug'=>$model->post->slug,'#'=>'c'.$model->id))),
                'description'=>$model->contentDisplay,
                'lastUpdate'=>$model->createTime,
            );
        }
        
        // generate and render RSS feed
        $feed=Zend_Feed::importArray(array(
            'title'=>Yii::t('lan','Comment Feed for ').Yii::app()->params['title'],
            'description'=>Yii::app()->params['description'],
            'link'=>$this->createAbsoluteUrl(''),
            'charset'=>'UTF-8',
            'entries'=>$entries,
        ), 'rss');
        $feed->send();
    }
    
    /**
     * Generate sitemap.
     */
    public function actionSitemapxml()
    {
        
        $posts=Post::model()->findAll(array(
            'order'=>'createTime DESC',
            'condition'=>'status='.Post::STATUS_PUBLISHED,
        ));
        
        $pages=Page::model()->findAll(array(
            'order'=>'createTime DESC',
            'condition'=>'status='.Page::STATUS_PUBLISHED,
        ));
        
        header('Content-Type: application/xml');
        $this->renderPartial('../site/sitemapxml',array('posts'=>$posts,'pages'=>$pages));
    }
}
