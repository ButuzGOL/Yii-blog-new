<ul>
    <?php foreach($this->getRecentPosts() as $post): ?>
        <li>
            <?php echo CHtml::link(CHtml::encode($post->title),array('post/show','slug'=>$post->slug)); ?>
            &rarr;
            <?php echo CHTml::link(Yii::t('lan',date('F', $post->createTime)).date(' j',$post->createTime),array('post/PostedOnDate', 
                                        'year'=>date('Y',$post->createTime),
                                        'month'=>date('m',$post->createTime),
                                        'day'=>date('j',$post->createTime))); ?>
        </li>
    <?php endforeach; ?>
</ul>
