<ul>
    <?php foreach($this->getCategories() as $category): ?>
        <?php if(count($category->posts)): ?>
            <li>
                <?php echo CHtml::link(CHtml::encode($category->name).' ('.count($category->posts).')',array('category/show','slug'=>$category->slug)); ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
