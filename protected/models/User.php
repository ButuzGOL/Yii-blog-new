<?php

class User extends CActiveRecord
{

    const STATUS_ADMIN=0;
    const STATUS_WRITER=1;
    const STATUS_VISITOR=2;
    const STATUS_GUEST=3;
    
    const BANNED_NO=0;
    const BANNED_YES=1;
    
    /**
     * @var string this property is used to collect user verification code input
     */
    public $verifyCode;
    
    public $password_repeat;
    public $usernameoremail;
    
    /**
     * The followings are the available columns in table 'User':
     * @var integer $id
     * @var string $username
     * @var string $password
     * @var string $email
     * @var string $url
     * @var integer $status
     * @var integer $banned
     * @var string $avatar
     * @var string $passwordLost
     * @var string $confirmRegistration
     * @var string $about
     */

    /**
     * Returns the static model of the specified AR class.
     * @return CActiveRecord the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'User';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('username','length','max'=>50),
            array('password','length','max'=>32),
            array('email','length','max'=>64),
            array('url','length','max'=>64),
            array('avatar','file','types'=>'gif, png, jpg, jpeg','maxSize'=>5242880, 'allowEmpty'=>true),
            array('passwordLost','length','max'=>32),
            array('username, email, status, banned','required','on'=>'insert, update, registration'),
            array('password','required','on'=>'insert, registration'),
            array('password','compare','on'=>'registration'),
            array('username','unique','on'=>'insert, registration, update'),
            array('email','unique','on'=>'insert, registration, update'),
            array('email','email'),
            array('url','url'),
            array('status','in','range'=>array(0,1,2)),
            array('banned','in','range'=>array(0,1)),
            array('username','match','pattern'=>'/^[\w\s.-]{3,50}$/','message'=>Yii::t('lan','Wrong or small username.')),
            array('password','match','pattern'=>'/^[\w\s]{3,32}$/','message'=>Yii::t('lan','Wrong or small password.')),
            array('verifyCode','captcha','on'=>'registration, lostpass','allowEmpty'=>!extension_loaded('gd')),
            array('usernameoremail','required','on'=>'lostpass'),
        );
    }

    /**
     * @return array attributes that can be massively assigned
     */
    public function safeAttributes()
    {
        return array('username','password','status', 'banned','about','email',
            'url','password_repeat','verifyCode','usernameoremail');
    }


    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'posts'=>array(self::HAS_MANY,'Post','authorId','order'=>'??.createTime'),
            'bookmarks'=>array(self::HAS_MANY,'Bookmark','userId'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'username'=>Yii::t('lan','Username'),
            'password'=>Yii::t('lan','Password'),
            'email'=>'Email',
            'url'=>'Url',
            'status'=>Yii::t('lan','Status'),
            'banned'=>Yii::t('lan','Banned'),
            'avatar'=>Yii::t('lan','Avatar'),
            'about'=>Yii::t('lan','About'),
            'password_repeat'=>Yii::t('lan','Password again'),
            'usernameoremail'=>Yii::t('lan','Username or Email'),
            'verifyCode'=>Yii::t('lan','Verification Code'),
        );
    }
    
    /**
     * @return array user status names indexed by status IDs
     */
    public function getStatusOptions()
    {
        return array(
            self::STATUS_ADMIN=>Yii::t('lan','Admin'),
            self::STATUS_WRITER=>Yii::t('lan','Writer'),
            self::STATUS_VISITOR=>Yii::t('lan','Visitor'),
        );
    }
    
    /**
     * @return string the status display for the current user
     */
    public function getStatusText()
    {
        $options=$this->statusOptions;
        return $options[$this->status];
    }
    
    /**
     * @return array user status names indexed by status IDs
     */
    public function getBannedOptions()
    {
        return array(
            self::BANNED_NO=>Yii::t('lan','No'),
            self::BANNED_YES=>Yii::t('lan','Yes'),
        );
    }
    
    /**
     * @return string the status display for the current user
     */
    public function getBannedText()
    {
        $options=$this->bannedOptions;
        return $options[$this->banned];
    }
    
    /**
     * Userprocessing after the record is saved
     */
    protected function afterSave()
    {
        if(!$this->isNewRecord && $this->id==Yii::app()->user->id)
        {
            Yii::app()->user->username=$this->username;
            if($this->password)
                Yii::app()->user->password=$this->password;
        }
    }
    
    /**
     * Prepares attributes before performing validation.
     * Used in form lost password
     */
    public function afterValidate()
    {
         if($this->usernameoremail)
         {
             $conditions='username=:usernameoremail OR email=:usernameoremail';
             $params=array('usernameoremail'=>$this->usernameoremail);
             $user=$this->find($conditions,$params);
             if($user===null)
             {
                $this->addError('usernameoremail',Yii::t('lan','Username or Email is incorrect.'));
                return false;
             }
             else if($user->banned==User::BANNED_YES)
             {
                $this->addError('usernameoremail',Yii::t('lan','User is banned.'));
                return false;
             }
             else if($user->confirmRegistration)
             {
                $this->addError('usernameoremail',Yii::t('lan','Confirm user email.'));
                return false;
             }
        }
        return true;
    }
    
    /**
     * Random name for avatar.
     */
    public function getRIN()
    {
        $chars='aeuybdghjlmnpqrstvwxz123456789';
        for($i=0,$pass='';$i<10;$i++)
            $rand.=$chars{mt_rand(0,29)};
        return $rand;
    }
}
