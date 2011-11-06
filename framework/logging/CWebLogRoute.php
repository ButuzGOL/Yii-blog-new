<?php
/**
 * CWebLogRoute class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebLogRoute shows the log content in Web page.
 *
 * The log content can appear either at the end of the current Web page
 * or in FireBug console window (if {@link showInFireBug} is set true).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CWebLogRoute.php 639 2009-02-08 16:16:26Z qiang.xue $
 * @package system.logging
 * @since 1.0
 */
class CWebLogRoute extends CLogRoute
{
	/**
	 * @var boolean whether the log should be displayed in FireBug instead of browser window. Defaults to false.
	 */
	public $showInFireBug=false;

	/**
	 * Displays the log messages.
	 * @param array list of log messages
	 */
	public function processLogs($logs)
	{
		$this->render('log',$logs);
	}

	/**
	 * Renders the view.
	 * @param string the view name (file name without extension). The file is assumed to be located under framework/data/views.
	 * @param array data to be passed to the view
	 */
	protected function render($view,$data)
	{
		if($this->showInFireBug)
			$view.='-firebug';
		else
		{
			$app=Yii::app();
			if(!($app instanceof CWebApplication) || $app->getRequest()->getIsAjaxRequest())
				return;
		}
		$viewFile=YII_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.'.php';
		include(Yii::app()->findLocalizedFile($viewFile,'en'));
	}
}

