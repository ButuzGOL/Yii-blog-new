<ul>
    <?php foreach($this->getPopularPosts() as $post): ?>
        <li>
            <?php echo CHtml::link(CHtml::encode($post->title).' ('.$post->commentCount.')',array('post/show','slug'=>$post->slug)); ?>
        </li>
    <?php endforeach; ?>
</ul>
