<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
    public $username;
    public $password;
    public $rememberMe;
    
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
            // username and password are required
            array('username, password', 'required'),
            // password needs to be authenticated
            array('password', 'authenticate'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'rememberMe'=>Yii::t('lan','Remember me next time'),
            'username'=>Yii::t('lan','Username'),
            'password'=>Yii::t('lan','Password'),
        );
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute,$params)
    {
        if(!$this->hasErrors())  // we only want to authenticate when no input errors
        {
            $identity=new UserIdentity($this->username,$this->password);
            $identity->authenticate();
            switch($identity->errorCode)
            {
                case UserIdentity::ERROR_NONE:
                    $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
                    Yii::app()->user->login($identity,$duration);
                    break;
                case UserIdentity::ERROR_USERNAME_INVALID:
                    $this->addError('username',Yii::t('lan','Username is incorrect.'));
                    break;
                case UserIdentity::ERROR_BANNED:
                    $this->addError('username',Yii::t('lan','User is banned.'));
                    break;
                case UserIdentity::ERROR_CONFIRMREGISTRATION:
                    $this->addError('username',Yii::t('lan','Confirm user email.'));
                    break;
                default:
                    $this->addError('password',Yii::t('lan','Password is incorrect.'));
                    break;
            }
        }
    }
}
