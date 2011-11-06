<?php

class SiteSearchForm extends CFormModel
{
    public $keyword;

    public function rules()
    {
        return array(
            array('keyword', 'required'),
            array('keyword','length','max'=>128),
            array('keyword','length','min'=>3));
    }

    public function safeAttributes()
    {
        return array('keyword',);
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'keyword'=>Yii::t('lan','Keyword'),
        );
    }
}
