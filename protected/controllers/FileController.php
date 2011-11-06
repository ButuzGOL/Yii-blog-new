<?php

class FileController extends CController
{
    const PAGE_SIZE=50;

    /**
     * @var string specifies the default action to be 'list'.
     */
    public $defaultAction='admin';
    
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
            array('allow',
                'expression'=>'Yii::app()->user->status=='.User::STATUS_ADMIN.
                    '||Yii::app()->user->status=='.User::STATUS_WRITER,
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }
    
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'show' page.
     */
    public function actionCreate()
    {
        $this->pageTitle=Yii::t('lan','New File');
        $this->render('create',array('model'=>$model));
    }
    
    /**
     * Uploads a new models.
     */
    public function actionUpload()
    {

        // Get the session Id passed from SWFUpload. We have to do this to work-around the Flash Player Cookie Bug
        if(isset($_POST['PHPSESSID']))
            session_id($_POST['PHPSESSID']);
        
        // Check the upload
        if(!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) || $_FILES['Filedata']['error']!=0)            exit(0);
        
        if(file_exists(Yii::app()->params['filePath'].$_FILES['Filedata']['name']))
            $_FILES['Filedata']['name']=File::getNonExistName(Yii::app()->params['filePath'].$_FILES['Filedata']['name']);
            
        if(@!move_uploaded_file($_FILES['Filedata']['tmp_name'],Yii::app()->params['filePath'].$_FILES['Filedata']['name']))
            exit(0);
        
        $model=new File;
        $model->name=$_FILES['Filedata']['name'];
        $f=escapeshellarg(Yii::app()->params['filePath'].$_FILES['Filedata']['name']);
        $model->type=trim(`file -bi $f`);
        $model->createTime=time();
        $model->save();
        
        echo $this->renderPartial('_create',array('model'=>$model));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'show' page.
     */
    public function actionUpdate()
    {
        $model=$this->loadFile();
        $file=File::model()->findbyPk($model->id)->name;
        if(isset($_POST['File']))
        {
            $model->attributes=$_POST['File'];
            if($model->save())
            {
                if($file!=$model->name)
                    rename(Yii::app()->params['filePath'].$file,Yii::app()->params['filePath'].$model->name);
                $this->redirect(array('admin'));
            }
        }
        
        $this->pageTitle=Yii::t('lan','Update File');
        $this->render('update',array('model'=>$model,'file'=>$file));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $this->processAdminCommand();

        $criteria=new CDbCriteria;
        
        $pages=new CPagination(File::model()->count($criteria));
        $pages->pageSize=self::PAGE_SIZE;
        $pages->applyLimit($criteria);

        $sort=new CSort('File');
        $sort->applyOrder($criteria);

        $models=File::model()->findAll($criteria);
        
        $this->pageTitle=Yii::t('lan','Manage Files');
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
    public function loadFile($id=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['id']))
                $this->_model=File::model()->findbyPk($id!==null ? $id : $_GET['id']);
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
            @unlink(Yii::app()->params['filePath'].File::model()->findbyPk($_POST['id'])->name);
            $this->loadFile($_POST['id'])->delete();
            // reload the current page to avoid duplicated delete actions
            $this->refresh();
        }
    }
}
