<?php

class PopularPosts extends Portlet
{
    public $title='Popular Posts';

    public function getPopularPosts()
    {
        return Post::model()->findPopularPosts();
    }

    protected function renderContent()
    {
        $this->render('popularPosts');
    }
}
