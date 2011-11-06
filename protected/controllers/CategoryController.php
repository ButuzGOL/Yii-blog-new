<?php

class CategoryController extends CController
{
    const PAGE_SIZE=10;
    public $pageTitle="";
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
            array('allow',  // allow all users to perform 'list' and 'show' actions
                'actions'=>array('list','show'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('admin','create','update','delete'),
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
        
        $criteria=new CDbCriteria;
        
        $pages=new CPagination(User::model()->count($criteria));
        $pages->pageSize=Yii::app()->params['postsPerPage'];
        $pages->applyLimit($criteria);
        
        $model=$this->loadCategorySlug();
        $this->pageTitle=Yii::t('lan','Posts in category').' "'.$model->name.'"';
        $this->render('show',array(
            'model'=>$model,
            'models'=>$model->posts,
            'pages'=>$pages)
        );
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'admin' page.
     */
    public function actionCreate()
    {
        
        $model=new Category;
        if(isset($_POST['Category']))
        {
            $model->attributes=$_POST['Category'];
            if($model->save())
                $this->redirect(array('admin'));
        }
        $this->pageTitle=Yii::t('lan','New Category');
        $this->render('create',array('model'=>$model));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'admin' page.
     */
    public function actionUpdate()
    {
        $model=$this->loadCategory();
        if(isset($_POST['Category']))
        {
            $model->attributes=$_POST['Category'];
            if($model->save())
                $this->redirect(array('admin'));
        }
        $this->pageTitle=Yii::t('lan','Update Category');
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
            $this->loadCategory()->delete();
            $this->redirect(array('list'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all models.
     */
    public function actionList()
    {
        $criteria=new CDbCriteria;

        $models=Category::model()->findAll($criteria);
        
        $this->pageTitle=Yii::t('lan','Categories List');
        $this->render('list',array(
            'models'=>$models,
            'pages'=>$pages,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $this->processAdminCommand();

        $criteria=new CDbCriteria;

        $pages=new CPagination(Category::model()->count($criteria));
        $pages->pageSize=self::PAGE_SIZE;
        $pages->applyLimit($criteria);

        $sort=new CSort('Category');
        $sort->applyOrder($criteria);

        $models=Category::model()->findAll($criteria);

        $this->pageTitle=Yii::t('lan','Manage Categories');
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
    public function loadCategory($id=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['id']))
                $this->_model=Category::model()->findbyPk($id!==null ? $id : $_GET['id']);
            if($this->_model===null)
                throw new CHttpException(404,'The requested page does not exist.');
        }
        return $this->_model;
    }
    
    /**
     * Returns the data model based on the slug given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param string the slug value. Defaults to null, meaning using the 'slug' GET variable
     */
    public function loadCategorySlug($slug=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['slug']))
                $this->_model=Category::model()->find('slug=:slug',array('slug'=>$slug!==null ? $slug : $_GET['slug']));
            if($this->_model===null)
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
            $this->loadCategory($_POST['id'])->delete();
            // reload the current page to avoid duplicated delete actions
            $this->refresh();
        }
    }
}
