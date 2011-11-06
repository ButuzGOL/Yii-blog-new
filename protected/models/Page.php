<?php

class Page extends CActiveRecord
{

    const STATUS_DRAFT=0;
    const STATUS_PUBLISHED=1;
    
    /**
     * The followings are the available columns in table 'Page':
     * @var integer $id
     * @var string $title
     * @var string $slug
     * @var string $content
     * @var integer $status
     * @var integer $createTime
     * @var integer $updateTime
     * @var integer $authorId
     * @var string $authorName
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
        return 'Page';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('title','length','max'=>128),
            array('slug','length','max'=>32),
            array('authorName','length','max'=>50),
            array('status','in','range'=>array(0,1)),
            array('title, content, status', 'required'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'author'=>array(self::BELONGS_TO,'User','authorId'),
        );
    }
    
    /**
     * @return array attributes that can be massively assigned
     */
    public function safeAttributes()
    {
        return array('title','content','status');
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'title'=>Yii::t('lan','Title'),
            'content'=>Yii::t('lan','Content'),
            'status'=>Yii::t('lan','Status'),
            'createTime'=>Yii::t('lan','Create Time'),
            'authorName'=>Yii::t('lan','Author Name'),
            'author'=>Yii::t('lan','Author Name'),
        );
    }
    
    /**
     * Prepares attributes before performing validation.
     */
    protected function beforeValidate($on)
    {
        $this->slug=Post::getSlug('Page',$this->title,($this->isNewRecord)?null:$this->id);
        if($this->isNewRecord)
        {
            $this->createTime=$this->updateTime=time();
            $this->authorId=Yii::app()->user->id;
            $this->authorName=Yii::app()->user->username;
        }
        else
            $this->updateTime=time();
        return true;
    }

    
    /**
     * @return array post status names indexed by status IDs
     */
    public function getStatusOptions()
    {
        return array(
            self::STATUS_DRAFT=>Yii::t('lan','Draft'),
            self::STATUS_PUBLISHED=>Yii::t('lan','Published'),
        );
    }
    
    /**
     * @return string the status display for the current post
     */
    public function getStatusText()
    {
        $options=$this->statusOptions;
        return $options[$this->status];
    }
}
