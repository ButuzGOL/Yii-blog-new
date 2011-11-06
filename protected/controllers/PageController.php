<?php

class PageController extends CController
{
    const PAGE_SIZE=10;

    /**
     * @var string specifies the default action to be 'show'.
     */
    public $defaultAction='show';

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
            array('allow',  // allow all users to perform 'show' actions
                'actions'=>array('show'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'expression'=>'Yii::app()->user->status=='.User::STATUS_ADMIN.
                    '||Yii::app()->user->status=='.User::STATUS_WRITER,
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Shows a particular model.
     */
    public function actionShow()
    {
        $model=$this->loadPageSlug();
        $this->pageTitle=$model->title;
        $this->render('show',array('model'=>$model));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'show' page.
     */
    public function actionCreate()
    {
        $model=new Page;
        if(isset($_POST['Page']))
        {
            $model->attributes=$_POST['Page'];
            if(isset($_POST['previewPage']))
                $model->validate();
            else if(isset($_POST['submitPage']) && $model->save())
                $this->redirect(array('show','slug'=>$model->slug));
        }
        $this->pageTitle=Yii::t('lan','New Page');
        $this->render('create',array('model'=>$model));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'show' page.
     */
    public function actionUpdate()
    {
        $model=$this->loadPage();
        if(isset($_POST['Page']))
        {
            $model->attributes=$_POST['Page'];
            if(isset($_POST['previewPage']))
                $model->validate();
            else if(isset($_POST['submitPage']) && $model->save())
                $this->redirect(array('show','slug'=>$model->slug));
        }
        $this->pageTitle=Yii::t('lan','Update Page');
        $this->render('update',array('model'=>$model));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     */
    public function actionDelete()
    {
        if(Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $this->loadPage()->delete();
            $this->redirect(array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $this->processAdminCommand();

        $criteria=new CDbCriteria;

        $pages=new CPagination(Page::model()->count($criteria));
        $pages->pageSize=self::PAGE_SIZE;
        $pages->applyLimit($criteria);

        $sort=new CSort('Page');
        $sort->applyOrder($criteria);

        $models=Page::model()->findAll($criteria);
        
        $this->pageTitle=Yii::t('lan','Manage Pages');
        $this->render('admin',array(
            'models'=>$models,
            'pages'=>$pages,
            'sort'=>$sort,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
     */
    public function loadPage($id=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['id']))
                $this->_model=Page::model()->findbyPk($id!==null ? $id : $_GET['id']);
            if($this->_model===null || Yii::app()->user->status!=User::STATUS_ADMIN && Yii::app()->user->status!=User::STATUS_WRITER && $this->_model->status!=Post::STATUS_PUBLISHED)
                throw new CHttpException(404,'The requested page does not exist.');
        }
        return $this->_model;
    }
    
    /**
     * Returns the data model based on the slug given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param string the slug value. Defaults to null, meaning using the 'slug' GET variable
     */
    public function loadPageSlug($slug=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['slug']))
                $this->_model=Page::model()->find('slug=:slug',array('slug'=>$slug!==null ? $slug : $_GET['slug']));
            if($this->_model===null || Yii::app()->user->status!=User::STATUS_ADMIN && Yii::app()->user->status!=User::STATUS_WRITER && $this->_model->status!=Post::STATUS_PUBLISHED)
                throw new CHttpException(404,'The requested page does not exist.');
        }
        return $this->_model;
    }

    /**
     * Executes any command triggered on the admin page.
     */
    protected function processAdminCommand()
    {
        if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
        {
            $this->loadPage($_POST['id'])->delete();
            // reload the current page to avoid duplicated delete actions
            $this->refresh();
        }
    }
    
    /**
     * Change status with AJAX.
     */
    public function actionAjaxStatus()
    {
        $model=$this->loadPage();
        $options=Page::getStatusOptions();
        $model->status=(count($options)==($model->status+1))?0:($model->status+1);
        $model->save();
        echo $model->statusText;
    }
}
