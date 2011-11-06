<ul>
    <?php foreach ($this->findAllPostDate() as $month=>$val): ?>
        <li>
            <?php echo CHtml::link(Yii::t('lan',date('F',strtotime($month))).date(' Y',strtotime($month))." ($val)",array('post/PostedInMonth',
                                   'year'=>date('Y',strtotime($month)),
                                   'month'=>date('m',strtotime($month)))); ?>
        </li>
    <?php endforeach; ?>
</ul>
