<?php foreach($this->TagWeights as $tag=>$weight): ?>
    <span class="tag" style="font-size:<?php echo $weight; ?>pt">
        <?php echo CHtml::link($tag,array('post/list','tag'=>$tag)); ?>
    </span>
<?php endforeach; ?>
