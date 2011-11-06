<ul>
<?php foreach($this->getRecentComments() as $comment): ?>
    <li><?php echo $comment->authorLink; ?> &rarr;
        <?php echo CHtml::link(CHtml::encode($comment->post->title),array('post/show','slug'=>$comment->post->slug,'#'=>'c'.$comment->id)); ?>
    </li>
<?php endforeach; ?>
</ul>
