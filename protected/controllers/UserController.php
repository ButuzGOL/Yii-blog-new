<?php

class UserController extends CController
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
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',  // allow all users to perform 'list' and 'show' actions
                'actions'=>array('show','list','registration','captcha','lostpass','login'),
                'users'=>array('*'),
            ),
            array('allow',
                'expression'=>'Yii::app()->user->status=='.User::STATUS_ADMIN,
            ),
            array('allow',
                'actions'=>array('update'),
                'expression'=>'Yii::app()->user->id=='.$_GET['id'],
            ),
            array('allow',
                'actions'=>array('bookmarks'),
                'users'=>array('@'),
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
        $model=$this->loadUser();
        $this->pageTitle=Yii::t('lan','View User').' '.$model->username;
        $this->render('show',
            array('model'=>$model));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'show' page.
     */
    public function actionCreate()
    {
        $model=new User;
        if(isset($_POST['User']))
        {
            $model->attributes=$_POST['User'];
            if($model->password && $model->validate())
                $model->password=md5($model->password);
           
            $model->avatar=CUploadedFile::getInstance($model,'avatar');
            if($model->avatar && $model->validate()) 
            {
                $imagename=User::getRIN().'.'.$model->avatar->getExtensionName();
                $image=Yii::app()->image->load($model->avatar->getTempName());
                $image->resize(Yii::app()->params['avatarWidth'],Yii::app()->params['avatarHeight']);
                $image->save(Yii::app()->params['avatarPath'].$imagename);
                $model->avatar=$imagename;
            }
            if($model->save())
                $this->redirect(array('show','id'=>$model->id));
        }
        $this->pageTitle=Yii::t('lan','New User');
        $this->render('create',array('model'=>$model));
    }
    
    /**
     * Registration a new user.
     * If creation is successful, the browser will be redirected to the 'home' page.
     */
    public function actionRegistration()
    {
        if(isset($_GET['code']))
        {
            $user=User::model()->find('confirmRegistration=:confirmRegistration',
                array(':confirmRegistration'=>$_GET['code']));
            if($user===null)
                throw new CHttpException(404,'The requested page does not exist.');
            $user->confirmRegistration='';
            $user->save();
            Yii::app()->user->setFlash('message',Yii::t('lan','Thank you for confirm your email. You can login with your username and password.'));
            $this->redirect(Yii::app()->homeUrl);
        }
        
        $model=new User;
        if(isset($_POST['User']))
        {
            $model->attributes=$_POST['User'];
            $model->status=User::STATUS_VISITOR;
            $model->banned=User::BANNED_NO;
            
            if($model->validate('registration'))
            {
                if(Yii::app()->params['confirmRegistration'])
                {
                    $model->confirmRegistration=$code=User::getRIN();
                    $email=Yii::app()->email;
                    $email->to=$model->email;
                    $email->from=$email->replyTo='=?koi8-r?B?'.base64_encode(iconv('UTF-8','koi8-r',Yii::app()->params['emailFrom'])).'?= <'.Yii::app()->params['adminEmail'].'>';
                    $email->message=$this->renderPartial('../email/lostpass',
                                        array('model'=>$user,'code'=>$code),true);
                    $email->subject=Yii::t('lan','Confirm registration').' - '.Yii::app()->params['emailFrom'];
                    $email->message=$this->renderPartial('../email/confirmregistration',
                                        array('model'=>$model,'code'=>$code),true);
                    $email->send();
                    
                    Yii::app()->user->setFlash('message',Yii::t('lan','Thank you for registration but you have to confirm your email. You\'ll receive an email with instructions on the next step.'));
                }
                else Yii::app()->user->setFlash('message',Yii::t('lan','Thank you for registration. You can login with your username and password.'));
                $model->password=md5($model->password);
                $model->save();
                $this->redirect(Yii::app()->homeUrl);
            }
        }
        $this->pageTitle=Yii::t('lan','Registration');
        $this->render('registration',array('model'=>$model));
    }
    
    /**
     * Lost password.
     */
    public function actionLostpass()
    {
        if(isset($_GET['code']))
        {
            $user=User::model()->find('passwordLost=:passwordLost',array(':passwordLost'=>$_GET['code']));
            if($user===null)
                throw new CHttpException(404,'The requested page does not exist.');
            $user->passwordLost='';
            $password=User::getRIN();
            $user->password=md5($password);
            $user->save();
            Yii::app()->user->setFlash('message',Yii::t('lan','Your password is update. Your username: {username} Your new password: {password}',array('{username}'=>$user->username,'{password}'=>$password)));
            $this->redirect(Yii::app()->homeUrl);
        }
        
        $model=new User;
        if(isset($_POST['User']))
        {
            $model->attributes=$_POST['User'];
            if($model->validate('lostpass'))
            {
                $user=User::model()->find('username=:username',array(':username'=>$model->usernameoremail));
                if($user===null)
                    $user=User::model()->find('email=:email',array(':email'=>$model->usernameoremail));
                $user->passwordLost=$code=User::getRIN();
                $user->save();
                
                $email=Yii::app()->email;
                $email->to=$user->email;
                $email->from=$email->replyTo='=?koi8-r?B?'.base64_encode(iconv('UTF-8','koi8-r',Yii::app()->params['emailFrom'])).'?= <'.Yii::app()->params['adminEmail'].'>';
                $email->subject=Yii::t('lan','Lost password ?').' - '.Yii::app()->params['emailFrom'];
                $email->message=$this->renderPartial('../email/lostpass',
                                        array('model'=>$user,'code'=>$code),true);
                $email->send();

                Yii::app()->user->setFlash('message',Yii::t('lan','You\'ll receive an email with instructions on the next step.'));
                $this->redirect(Yii::app()->homeUrl);
            }
        }
        $this->pageTitle=Yii::t('lan','Lost password ?');
        $this->render('lostpass',array('model'=>$model));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'show' page.
     */
    public function actionUpdate()
    {
        $model=$this->loadUser();
        if(isset($_POST['User']))
        {
            $model->attributes=$_POST['User'];
            if($model->password && $model->validate())
                $model->password=md5($model->password);
            elseif(!$model->password)
                unset($model->password);
            
            if($model->id==1)
                $model->banned=User::BANNED_NO;
            
            $model->avatar=CUploadedFile::getInstance($model,'avatar');
            if($model->avatar && $model->validate())
            {
                $imagename=User::getRIN().'.'.$model->avatar->getExtensionName();
                $image=Yii::app()->image->load($model->avatar->getTempName());
                $image->resize(Yii::app()->params['avatarWidth'],Yii::app()->params['avatarHeight']);
                $image->save(Yii::app()->params['avatarPath'].$imagename);
                $model->avatar=$imagename;
                @unlink(Yii::app()->params['avatarPath'].User::model()->findbyPk($model->id)->avatar);
            }
            else unset($model->avatar);
            
            if($_POST['davatar'])
            {
                @unlink(Yii::app()->params['avatarPath'].User::model()->findbyPk($model->id)->avatar);
                $model->avatar='';
            }
            
            if($model->save())
                $this->redirect(array('show','id'=>$model->id));
        }
        if($model->id==Yii::app()->user->id) $this->pageTitle=Yii::t('lan','My profile');
        else $this->pageTitle=Yii::t('lan','Update User');
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
            if($_GET['id']!=1)
            {
                @unlink(Yii::app()->params['avatarPath'].User::model()->findbyPk($_GET['id'])->avatar);
                $this->loadUser()->delete();
            }
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

        $pages=new CPagination(User::model()->count($criteria));
        $pages->pageSize=self::PAGE_SIZE;
        $pages->applyLimit($criteria);

        $models=User::model()->findAll($criteria);

        $this->pageTitle=Yii::t('lan','Users List');
        $this->render('list',array(
            'models'=>$models,
            'pages'=>$pages,
        ));
    }
    
    /**
     * Lists all bookmarks.
     */
    public function actionBookmarks()
    {
        $criteria=new CDbCriteria;

        $pages=new CPagination(User::model()->count($criteria));
        $pages->pageSize=Yii::app()->params['postsPerPage'];
        $pages->applyLimit($criteria);
        
        $this->render('bookmarks',array(
            'models'=>$this->loadUser(Yii::app()->user->id)->bookmarks,
            'pages'=>$pages,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
     */
    public function loadUser($id=null)
    {
        if($this->_model===null)
        {
            if($id!==null || isset($_GET['id']))
                $this->_model=User::model()->findbyPk($id!==null ? $id : $_GET['id']);
            if($this->_model===null)
                throw new CHttpException(404,'The requested page does not exist.');
        }
        return $this->_model;
    }
    
    /**
     * Change banned with AJAX.
     */
    public function actionAjaxBanned()
    {
        $model=$this->loadUser();
        $options=User::getBannedOptions();
        $model->banned=(count($options)==($model->banned+1))?0:($model->banned+1);
        if($model->id==1)
            $model->banned=User::BANNED_NO;
        $model->save();
        echo $model->bannedText;
    }
    
    /**
     * User Login.
     * If login is successful, the browser will be redirected to the 'home' page.
     */
    public function actionLogin()
    {
        $form=new LoginForm;
        if(isset($_POST['LoginForm']) && isset($_POST['loginController']))
        {
            $form->attributes=$_POST['LoginForm'];
            if($form->validate())
                $this->redirect(Yii::app()->homeUrl);
        }
        $this->pageTitle=Yii::t('lan','Login');
        $this->render('login',array('model'=>$form));
    }
}
