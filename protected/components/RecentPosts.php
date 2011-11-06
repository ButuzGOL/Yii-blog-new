<?php

class RecentPosts extends Portlet
{
    public $title='Recent Posts';

    public function getRecentPosts()
    {
        return Post::model()->findRecentPosts();
    }

    protected function renderContent()
    {
        $this->render('recentPosts');
    }
}
