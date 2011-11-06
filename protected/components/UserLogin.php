<?php

class UserLogin extends Portlet
{
    public $title='Login';
    
    protected function renderContent()
    {
        $form=new LoginForm;
        if(isset($_POST['LoginForm']) && isset($_POST['loginWidget']))
        {
            $form->attributes=$_POST['LoginForm'];
            if($form->validate())
                $this->controller->refresh();
        }
        $this->render('userLogin',array('form'=>$form));
    }
}
