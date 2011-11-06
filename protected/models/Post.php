<?php

class Post extends CActiveRecord
{

    const STATUS_DRAFT=0;
    const STATUS_PUBLISHED=1;
    const STATUS_ARCHIVED=2;
    const STATUS_PENDING=3;

    public $content;

    /**
     * The followings are the available columns in table 'Post':
     * @var integer $id
     * @var string $title
     * @var string $titleLink
     * @var string $slug
     * @var string $contentshort
     * @var string $contentbig
     * @var string $tags
     * @var integer $status
     * @var integer $createTime
     * @var integer $updateTime
     * @var integer $commentCount
     * @var integer $categoryId
     * @var integer $authorId
     * @var integer $authorName
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
        return 'Post';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('title, tags, content', 'required'),
            array('title','length','max'=>128),
            array('titleLink','length','max'=>128),
            array('titleLink','url'),
            array('status','in','range'=>array(0,1,2,3)),
            array('tags','match','pattern'=>'/^[А-Яа-я\s\w,-]+$/u','message'=>Yii::t('lan','Tags can only contain word characters.')),
        );
    }

    /**
     * @return array attributes that can be massively assigned
     */
    public function safeAttributes()
    {
        return array('title','titleLink','content','status','tags','categoryId');
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'category'=>array(self::BELONGS_TO,'Category','categoryId'),
            'author'=>array(self::BELONGS_TO,'User','authorId'),
            'comments'=>array(self::HAS_MANY,'Comment','postId','order'=>'??.createTime'),
            'bookmarks'=>array(self::HAS_MANY,'Bookmark','postId',
                'condition'=>'??.userId='.Yii::app()->user->id),
            'tagFilter'=>array(self::MANY_MANY,'Tag','PostTag(postId, tagId)',
                'together'=>true,
                'joinType'=>'INNER JOIN',
                'condition'=>'??.name=:tag'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'title'=>Yii::t('lan','Title'),
            'titleLink'=>Yii::t('lan','Title Link'),
            'content'=>Yii::t('lan','Content'),
            'tags'=>Yii::t('lan','Tags'),
            'status'=>Yii::t('lan','Status'),
            'createTime'=>Yii::t('lan','Create Time'),
            'updateTime'=>Yii::t('lan','Update Time'),
            'commentCount'=>Yii::t('lan','Comment Count'),
            'authorName'=>Yii::t('lan','Author Name'),
            'author'=>Yii::t('lan','Author Name'),
            'categoryId'=>Yii::t('lan','Category'),
        );
    }

    /**
     * @return array post status names indexed by status IDs
     */
    public function getStatusOptions()
    {
        return array(
            self::STATUS_DRAFT=>Yii::t('lan','Draft'),
            self::STATUS_PUBLISHED=>Yii::t('lan','Published'),
            self::STATUS_ARCHIVED=>Yii::t('lan','Archived'),
            self::STATUS_PENDING=>Yii::t('lan','Pending'),
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

    /**
     * Prepares attributes before performing validation.
     */
    protected function beforeValidate($on)
    {
        $this->slug=$this->getSlug('Post',$this->title,($this->isNewRecord)?null:$this->id);
        $content=$this->contentShortBig($this->content);
        $this->contentshort=$content[0];
        $this->contentbig=$content[1];
        if($this->isNewRecord)
        {
            if(Yii::app()->user->status==User::STATUS_VISITOR)
                $this->status=Post::STATUS_PENDING;
            $this->createTime=$this->updateTime=time();
            $this->authorId=Yii::app()->user->id;
            $this->authorName=Yii::app()->user->username;
        }
        else
            $this->updateTime=time();
        return true;
    }

    /**
     * Postprocessing after the record is saved
     */
    protected function afterSave()
    {
        if(!$this->isNewRecord)
            $this->dbConnection->createCommand('DELETE FROM PostTag WHERE postId='.$this->id)->execute();

        if($this->status==self::STATUS_PUBLISHED)
        {
            foreach($this->getTagArray() as $name)
            {
                if(($tag=Tag::model()->findByAttributes(array('name'=>$name)))===null)
                {
                    $tag=new Tag(array('name'=>$name));
                    $tag->save();
                }
                $this->dbConnection->createCommand("INSERT INTO PostTag (postId, tagId) VALUES ({$this->id},{$tag->id})")->execute();
            }
        }
    }

    /**
     * Postprocessing after the record is deleted
     */
    protected function afterDelete()
    {
        // The following two deletions are mainly for SQLite database.
        // In other DBMS, the related row deletion is enforced by FK constraints
        Comment::model()->deleteAll('postId='.$this->id);
        $this->dbConnection->createCommand('DELETE FROM PostTag WHERE postId='.$this->id)->execute();
    }

    /**
     * @return array tags
     */
    public function getTagArray()
    {
        return array_unique(preg_split('/\s*,\s*/',trim($this->tags),-1,PREG_SPLIT_NO_EMPTY));
    }

    /**
     * @param integer the maximum number of comments that should be returned
     * @return array the most recently added comments
     */
    public function findRecentPosts($limit=10)
    {
        $criteria=array(
            'condition'=>'Post.status='.self::STATUS_PUBLISHED,
            'order'=>'Post.createTime DESC',
            'limit'=>$limit,
        );
        return $this->findAll($criteria);
    }

    /**
     * @param integer the maximum number of comments that should be returned
     * @return array the most recently added comments
     */
    public function findPopularPosts($limit=10)
    {
        $criteria=array(
            'condition'=>'Post.commentCount<>0 AND Post.status='.self::STATUS_PUBLISHED,
            'order'=>'Post.createTime DESC',
            'order'=>'Post.commentCount DESC',
            'limit'=>$limit,
        );
        return $this->findAll($criteria);
    }

    /**
     * Generates the hyperlinks for post tags.
     * This is mainly used by the view that displays a post.
     * @param Post the post instance
     * @return string the hyperlinks for the post tags
     */
    public function getTagLinks($model)
    {
        $links=array();
        foreach($model->getTagArray() as $tag)
            $links[]=CHtml::link($tag,array('list','tag'=>$tag));
        return implode(', ',$links);
    }

    /**
     * @param class name, slug title or name, id if update
     * @return slug
     */
    public function getSlug($class,$slug,$id)
    {
        $slug=trim(preg_replace('/[^a-z0-9a-я-]/','-',mb_strtolower($slug,'UTF-8')));
        if(preg_match('/[а-я]/',$slug))
        {
            $trans=array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh',
                'з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p',
                'р'=>'r','с'=>'s','т'=>'t','у'=>'t','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh',
                'щ'=>'w','ъ'=>'','ы'=>'i','ь'=>'','э'=>'je','ю'=>'yu','я'=>'ya');
            $slug=strtr($slug,$trans);
        }

        if(strlen($slug)>31)
            $slug=substr($slug,0,30);
        $slug=preg_replace('/-{2,}/','-',$slug);
        if($slug[strlen($slug)-1]=='-')
            $slug=substr($slug,0,strlen($slug)-1);
        if($slug[0]=='-')
            $slug=substr($slug,1,strlen($slug));
        if(!$slug)
            $slug='1';

        $k=1;
        $h=$slug;
        while(count(eval('return '.$class.'::model()->findAll(array(\'condition\'=>\'slug="'.$h.'"'.(($id)?' AND id<>'.$id:'').'\'));')))
        {
            $h=$slug.$k;
            $k++;
        }
        $slug=$h;

        return $slug;
    }

    public function contentShortBig($content)
    {
        $seperator="/<div(.*) id=\"post-more\"(.*)>\r\n\t<span style=\"display: none;\">(.*)<\/span><\/div>/";

        $sbcontent=preg_split($seperator,$content,2,PREG_SPLIT_NO_EMPTY);
        $sbcontent[1]=preg_replace("/\r|\n|\t|\s/",'',$sbcontent[1]);

        if($sbcontent[1]!='')
            $sbcontent[1]=$content;

        return $sbcontent;
    }
}

