<?php

class Links extends Portlet
{
    public $title='Links';

    protected function renderContent()
    {
        $this->render('links');
    }
}
