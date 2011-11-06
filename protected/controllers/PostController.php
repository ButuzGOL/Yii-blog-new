<?php

class PostController extends BaseController
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
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image
            // this is used by the contact page
            'captcha'=>array(
                'class'=>'CCaptchaAction',
            ),
        );
    }

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
                'actions'=>array('list','show','captcha','PostedInMonth','PostedOnDate', 'search'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array('ajaxBookmark','create'),
                'users'=>array('@'),
            ),
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
     * Shows a particular post.
     */
    public function actionShow()
    {
        $model=$this->loadPostSlug();
        $newComment=$this->newComment($model);
        
        $this->pageTitle=$model->title.(($model->category)?' / '.$model->category->name:'');
        $this->render('show',array(
            'model'=>$model,
            'comments'=>$model->comments,
            'newComment'=>$newComment,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'show' page.
     */
    public function actionCreate()
    {
        $model=new Post;
        if(isset($_POST['Post']))
        {
            $model->attributes=$_POST['Post'];
            if(isset($_POST['previewPost']))
                $model->validate();
            else if(isset($_POST['submitPost']) && $model->save())
            {
                if(Yii::app()->user->status==User::STATUS_VISITOR)
                {
                    Yii::app()->user->setFlash('message','Thank you for your post. Your post will be posted once it is approved.');
                    $this->redirect(Yii::app()->homeUrl);
                }
                $this->redirect(array('show','slug'=>$model->slug));
            }
        }
        $this->pageTitle=Yii::t('lan','New Post');
        $this->render('create',array('model'=>$model));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'show' page.
     */
    public function actionUpdate()
    {
        $model=$this->loadPost();
        $model->content=($model->contentbig)?$model->contentbig:$model->contentshort;
        if(isset($_POST['Post']))
        {
            $model->attributes=$_POST['Post'];
            if(isset($_POST['previewPost']))
                $model->validate();
            else if(isset($_POST['submitPost']) && $model->save())
                $this->redirect(array('show','slug'=>$model->slug));
        }
        $this->pageTitle=Yii::t('lan','Update Post');
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
            $this->loadPost()->delete();
            $this->redirect(array('list'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all posts.
     */
    public function actionList()
    {
        $criteria=new CDbCriteria;
        $criteria->condition='status='.Post::STATUS_PUBLISHED;
        $criteria->order='createTime DESC';

        $withOption=null;
        if(!empty($_GET['tag']))
        {
            $this->pageTitle=Yii::t('lan','Posts Tagged with').' "'.CHtml::encode($_GET['tag']).'"';
            $withOption['tagFilter']['params'][':tag']=$_GET['tag'];
            $modelsCount=Post::model()->with($withOption)->count($criteria);
        }
        else
        {
            $this->pageTitle='';
            $modelsCount=Post::model()->count($criteria);
        }
        $pages=new CPagination($modelsCount);
        $pages->pageSize=Yii::app()->params['postsPerPage'];
        $pages->applyLimit($criteria);

        $models=Post::model()->with($withOption)->findAll($criteria);
        
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

        $pages=new CPagination(Post::model()->count($criteria));
        $pages->applyLimit($criteria);

        $sort=new CSort('Post');
        $sort->defaultOrder='status ASC, createTime DESC';
        $sort->applyOrder($criteria);

        $models=Post::model()->findAll($criteria);
        
        $this->pageTitle=Yii::t('lan','Manage Posts');
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
    public function loadPost($id=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['id']))
                $this->_model=Post::model()->findbyPk($id!==null ? $id : $_GET['id']);
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
    public function loadPostSlug($slug=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['slug']))
                $this->_model=Post::model()->find('slug=:slug',array('slug'=>$slug!==null ? $slug : $_GET['slug']));
            if($this->_model===null)
                throw new CHttpException(404,'The requested page does not exist.');
        }
        return $this->_model;
    }
    
    /**
     * Creates a new comment.
     * This method attempts to create a new comment based on the user input.
     * If the comment is successfully created, the browser will be redirected
     * to show the created comment.
     * @param Post the post that the new comment belongs to
     * @return Comment the comment instance
     */
    protected function newComment($model)
    {
        $comment=new Comment;
        if(isset($_POST['Comment']))
        {
            $comment->attributes=$_POST['Comment'];
            if(!Yii::app()->user->isGuest)
            {
                $comment->authorName=Yii::app()->user->username;
                $comment->email=Yii::app()->user->email;
                $comment->authorId=Yii::app()->user->id;
            }
            
            if(Yii::app()->user->isGuest && Yii::app()->params['commentNeedApproval'])
                $comment->status=Comment::STATUS_PENDING;
            else
                $comment->status=Comment::STATUS_APPROVED;
            
            $comment->postId=$model->id;
            
            if(isset($_POST['previewComment']))
                $comment->validate();
            else
                if(isset($_POST['submitComment']) && $comment->save())
                {
                    if($comment->status==Comment::STATUS_PENDING)
                    {
                        Yii::app()->user->setFlash('commentSubmittedMessage',Yii::t('lan','Thank you for your comment. Your comment will be posted once it is approved.'));
                        $this->refresh();
                    }
                    else
                        $this->redirect(array('show','slug'=>$model->slug,'#'=>'c'.$comment->id));
                }
        }
        return $comment;
    }
    
    /**
     * Executes any command triggered on the admin page.
     */
    protected function processAdminCommand()
    {
        if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
        {
            $this->loadPost($_POST['id'])->delete();
            // reload the current page to avoid duplicated delete actions
            $this->refresh();
        }
    }
    
    /**
     * Add/Delete Bookmark with AJAX.
     */
    public function actionAjaxBookmark()
    {
        $model=$this->loadPost();
        echo (Bookmark::addDel($model->id))?Yii::t('lan','Delete'):Yii::t('lan','Add');
     }
    
    /**
     * Collect posts issued in specific month
     */
    public function actionPostedInMonth()
    {
        $criteria=new CDbCriteria;
        $criteria->condition='status='.Post::STATUS_PUBLISHED;
        $criteria->order='createTime DESC';

        $criteria->condition.=' AND createTime > :time1 AND createTime < :time2';
        $year=intval($_GET['year']);
        $month=intval($_GET['month']);
        $criteria->params[':time1']=$firstDay=mktime(0,0,0,$month,1,$year);
        $criteria->params[':time2']=mktime(0,0,0,$month+1,1,$year);

        $pages=new CPagination(Post::model()->count($criteria));
        $pages->pageSize=Yii::app()->params['postsPerPage'];
        $pages->applyLimit($criteria);

        $models=Post::model()->findAll($criteria);
        
        $this->pageTitle=Yii::t('lan','Posts Issued on').' "'.Yii::t('lan',date('F',$firstDay)).date(', Y',$firstDay).'"';
        $this->render('month',array(
            'models'=>$models,
            'pages'=>$pages,
            'firstDay'=> $firstDay,
        ));
    }
    
    /**
     * Collect posts issued in specific date
     */
    public function actionPostedOnDate()
    {
        $criteria=new CDbCriteria;
        $criteria->condition='status='.Post::STATUS_PUBLISHED;
        $criteria->order='createTime DESC';

        $criteria->condition.=' AND createTime > :time1 AND createTime < :time2';
        $year=intval($_GET['year']);
        $month=intval($_GET['month']);
        $day=intval($_GET['day']);
        $criteria->params[':time1']=$theDay = mktime(0,0,0,$month,$day,$year);
        $criteria->params[':time2']=mktime(0,0,0,$month,$day+1,$year);

        $pages=new CPagination(Post::model()->count($criteria));
        $pages->pageSize=Yii::app()->params['postsPerPage'];
        $pages->applyLimit($criteria);

        $models=Post::model()->findAll($criteria);
        
        $this->pageTitle=Yii::t('lan','Posts Issued on').' "'.Yii::t('lan',date('F',$theDay)).date(' j, Y',$theDay).'"';
        $this->render('date',array(
            'models'=>$models,
            'pages'=>$pages,
            'theDay'=> $theDay,
        ));
    }
    
    /**
     * Sitewide search.
     * Shows a particular post searched.
     */
    public function actionSearch()
    {
        $search=new SiteSearchForm;
        
        if(isset($_POST['SiteSearchForm']))
        {
            $search->attributes=$_POST['SiteSearchForm'];
            $_GET['searchString']=$search->keyword;
        }
        else
            $search->keyword=$_GET['searchString'];
         
        if($search->validate())
        {
        
            $criteria=new CDbCriteria;
            $criteria->condition='status='.Post::STATUS_PUBLISHED;
            $criteria->order='createTime DESC';
            
            $criteria->condition.=' AND contentshort LIKE :keyword';
            $criteria->params=array(':keyword'=>'%'.CHtml::encode($search->keyword).'%');

            $postCount=Post::model()->count($criteria);
            $pages=new CPagination($postCount);
            $pages->pageSize=Yii::app()->params['postsPerPage'];
            $pages->applyLimit($criteria);

            $models=Post::model()->findAll($criteria);
        }
    
        $this->pageTitle=Yii::t('lan','Search Results').' "'.CHtml::encode($_GET['searchString']).'"';
        $this->render('search',array(
            'models'=>($models)?$models:array(),
            'pages'=>$pages,
            'search'=>$search,
        ));
    }
    
    /**
     * Change status with AJAX.
     */
    public function actionAjaxStatus()
    {
        $model=$this->loadPost();
        $options=Post::getStatusOptions();
        $model->status=(count($options)==($model->status+1))?0:($model->status+1);
        $model->save(false);
        echo $model->statusText;
    }
}
