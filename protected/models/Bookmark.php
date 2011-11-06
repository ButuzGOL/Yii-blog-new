<?php

class Bookmark extends CActiveRecord
{
    /**
     * The followings are the available columns in table 'Bookmark':
     * @var integer $id
     * @var integer $postId
     * @var integer $userId
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
        return 'Bookmark';
    }
    
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'post'=>array(self::BELONGS_TO,'Post','postId',
                'condition'=>'??.status='.Post::STATUS_PUBLISHED.' OR '.Yii::app()->user->status.'='.User::STATUS_ADMIN),
        );
    }

    /**
     * Add or Delete Bookmark.
     */
    public function addDel($postId)
    {
        $row=Bookmark::model()->find('postId=:postId and userId=:userId',array(':postId'=>$postId,':userId'=>Yii::app()->user->id));
        if(empty($row))
        {
            $row=new Bookmark;
            $row->postId=$postId;
            $row->userId=Yii::app()->user->id;
            $row->save();
            return true;
        }
        else
        {
            $row->delete();
            return false;
        }
    }
}
