<?php

class CommentController extends BaseController
{
    const PAGE_SIZE=10;

    /**
     * @var string specifies the default action to be 'list'.
     */
    public $defaultAction='list';

    /**
     * @var CActiveRecord the currently loaded data model instance.
     */
    private $_model;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow admin and writer
                'expression'=>'Yii::app()->user->status=='.User::STATUS_ADMIN.
                    '||Yii::app()->user->status=='.User::STATUS_WRITER,
            ),
            array('deny',  // deny guest users
                'users'=>array('?'),
            ),
        );
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'show' page.
     */
    public function actionUpdate()
    {
        $model=$this->loadComment();

        if(isset($_POST['Comment']))
        {
            $model->attributes=$_POST['Comment'];
            
            if(isset($_POST['previewComment']))
                $model->validate();
            else if(isset($_POST['submitComment']) && $model->save())
                $this->redirect(array('post/show','slug'=>$model->post->slug,'#'=>'c'.$model->id));
        }
        
        $this->pageTitle=Yii::t('lan','Update Comment');
        $this->render('update',array('model'=>$model));
    }

    /**
     * Deletes a particular model with AJAX.
     */
    public function actionAjaxDelete()
    {
        $model=$this->loadComment();
        $model->delete();
    }

    /**
     * Approves a particular comment with AJAX.
     */
    public function actionAjaxApprove()
    {
        $model=$this->loadComment();
        $model->approve();
    }

    /**
     * Lists all pending comments.
     */
    public function actionList()
    {
        $criteria=new CDbCriteria;
        $criteria->condition='Comment.status='.Comment::STATUS_PENDING;
        
        $pages=new CPagination(Comment::model()->count($criteria));
        $pages->pageSize=self::PAGE_SIZE;
        $pages->applyLimit($criteria);

        $models=Comment::model()->with('post')->findAll($criteria);

        $this->pageTitle=Yii::t('lan','Comments Pending Approval');
        $this->render('list',array(
            'models'=>$models,
            'pages'=>$pages,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
     */
    public function loadComment($id=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['id']))
                $this->_model=Comment::model()->findbyPk($id!==null ? $id : $_GET['id']);
            if($this->_model===null)
                throw new CHttpException(404,'The requested comment does not exist.');
        }
        return $this->_model;
    }
}
