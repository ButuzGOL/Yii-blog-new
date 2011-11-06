<?php

class UserMenu extends Portlet
{
    public function init()
    {
        if(isset($_POST['command']) && $_POST['command']==='logout')
        {
            Yii::app()->user->logout();
            $this->controller->redirect(Yii::app()->homeUrl);
        }
        if(!Yii::app()->user->isGuest) $this->title=CHtml::encode(Yii::app()->user->username);
        parent::init();
    }

    protected function renderContent()
    {
        $this->render('userMenu');
    }
}
