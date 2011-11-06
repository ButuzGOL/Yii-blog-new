<?php
/**
 * CExceptionEvent class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CExceptionEvent represents the parameter for the {@link CApplication::onException onException} event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CExceptionEvent.php 870 2009-03-22 15:06:23Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
class CExceptionEvent extends CEvent
{
	/**
	 * @var CException the exception that this event is about.
	 */
	public $exception;

	/**
	 * Constructor.
	 * @param mixed sender of the event
	 * @param CException the exception
	 */
	public function __construct($sender,$exception)
	{
		$this->exception=$exception;
		parent::__construct($sender);
	}
}