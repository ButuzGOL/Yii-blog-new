<?php

class Portlet extends CWidget
{
    public $title;
    public $cssClass='portlet';
    public $headerCssClass='header';
    public $contentCssClass='content';
    public $visible=true;
    
    public function getViewPath()
    {
        $themeManager=Yii::app()->themeManager;
        return $themeManager->basePath.DIRECTORY_SEPARATOR.Yii::app()->theme->name.DIRECTORY_SEPARATOR.'views/portlets';
    }

    public function init()
    {
        $this->title=Yii::t('lan',$this->title);
        
        if(!$this->visible)
            return;
        echo "<div class=\"{$this->cssClass}\">\n";
        if($this->title!==null)
            echo "<div class=\"{$this->headerCssClass}\">{$this->title}</div>\n";
        echo "<div class=\"{$this->contentCssClass}\">\n";
    }

    public function run()
    {
        if(!$this->visible)
            return;
        $this->renderContent();
        echo "</div>";
        echo "</div>";
    }

    protected function renderContent()
    {
    }
}
