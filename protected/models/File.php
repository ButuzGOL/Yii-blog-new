<?php

class File extends CActiveRecord
{
    /**
     * The followings are the available columns in table 'File':
     * @var integer $id
     * @var string $name
     * @var string $type
     * @var integer $createTime
     * @var string $alt
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
        return 'File';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('name','length','max'=>64),
            array('type','length','max'=>32),
            array('alt','length','max'=>32),
            array('name', 'required'),
            array('createTime', 'numerical', 'integerOnly'=>true),
        );
    }
    
    /**
     * @return array attributes that can be massively assigned
     */
    public function safeAttributes()
    {
        return array('name','alt');
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'name'=>Yii::t('lan','Name'),
            'type'=>Yii::t('lan','Type'),
            'name'=>Yii::t('lan','Name'),
            'createTime'=>Yii::t('lan','Create Time'),
            'alt'=>Yii::t('lan','About file'),
        );
    }
    
    /**
     * Prepares attributes before performing validation.
     */
    protected function beforeValidate($on)
    {
        if(!$this->isNewRecord && File::model()->findbyPk($this->id)->name!=$this->name && $this->name!='' && file_exists(Yii::app()->params['filePath'].$this->name))
        {
            $this->addError('name',Yii::t('lan','File exists.'));
            return false;
        }
        return true;
    }
    
    /**
     * @return image height or weight.
     */
    public function getHOW($image)
    {
    
        $size=@getimagesize($image);
        $bb=Yii::app()->params['imageThumbnailBoundingbox'];
        if($size[0]>$bb && $size[1]<=$bb)
          $whtext='width';
        else if($size[0]<=$bb && $size[1]>$bb)
          $whtext='height';
        else if($size[0]>$bb && $size[1]>$bb)
          if(1.0<=$size[1]/$size[0])
            $whtext='height';
          else
            $whtext='width';
        
        return $whtext;
    }
    
    /**
     * @return non exist name.
     */
    function getNonExistName($filename)
    {
        $pathinfo=pathinfo($filename);
        $name=$pathinfo['dirname'].'/'.$pathinfo['filename'];
        $extension=$pathinfo['extension'];
        
        $k=1;
        while(file_exists($filename))
        {
            $filename=$name.'('.$k.').'.$extension; 
            $k++;
        }
    
        return $pathinfo['filename'].'('.($k-1).').'.$extension;
    }
}
