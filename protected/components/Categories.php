<?php

class Categories extends Portlet
{
    public $title='Categories';

    public function getCategories()
    {
        return Category::model()->findAll();
    }

    protected function renderContent()
    {
        $this->title=Yii::t('lan',$this->title);
        $this->render('categories');
    }
}
