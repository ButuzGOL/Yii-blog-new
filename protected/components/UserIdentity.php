<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    
    const ERROR_BANNED=3;
    const ERROR_CONFIRMREGISTRATION=4;
    
    private $_id;
    /**
     * Authenticates a user.
     * The example implementation makes sure if the username and password
     * are both 'demo'.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     * @return boolean whether authentication succeeds.
     */
    public function authenticate($md5=true)
    {
        $user=User::model()->find('LOWER(username)=?',array(strtolower($this->username)));
        if($user===null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else if((($md5)?md5($this->password):$this->password)!==$user->password)
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else if($user->banned==User::BANNED_YES)
            $this->errorCode=self::ERROR_BANNED;
        else if($user->confirmRegistration)
            $this->errorCode=self::ERROR_CONFIRMREGISTRATION;
        else
        {
            $this->_id=$user->id;
            $this->setState('username', $user->username);
            $this->setState('password', $user->password);
            $this->setState('email', $user->email);
            $this->setState('status', $user->status);
            $this->errorCode=self::ERROR_NONE;
        }
        return !$this->errorCode;
    }

    /**
     * @return integer the ID of the user record
     */
    public function getId()
    {
        return $this->_id;
    }
}
